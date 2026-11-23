<?php

use App\Models\CalendarDay;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

test('authenticated users can visit the advent calendar', function () {
    $user = User::factory()->create();
    CalendarDay::factory()->create();

    $response = $this->actingAs($user)->get(route('calendar.index'));

    $response->assertOk();
});

test('users can solve an available calendar day', function () {
    $user = User::factory()->create();
    $day = CalendarDay::factory()->create([
        'available_at' => now()->startOfDay(),
        'challenge_data' => [
            'question' => 'Who delivers gifts?',
            'accepted_answers' => ['Santa'],
        ],
    ]);

    $response = $this->from(route('calendar.index'))
        ->actingAs($user)
        ->post(route('calendar.days.solve', $day), ['answer' => 'santa']);

    $response->assertRedirect(route('calendar.index'));
    $this->assertDatabaseHas('calendar_day_user', [
        'calendar_day_id' => $day->id,
        'user_id' => $user->id,
    ]);
});

test('locked days cannot be solved before their available date', function () {
    $user = User::factory()->create();
    $day = CalendarDay::factory()->locked()->create([
        'challenge_data' => [
            'question' => 'Who delivers gifts?',
            'accepted_answers' => ['Santa'],
        ],
    ]);

    $response = $this->from(route('calendar.index'))
        ->actingAs($user)
        ->post(route('calendar.days.solve', $day), ['answer' => 'santa']);

    $response->assertSessionHasErrors('challenge');
    $this->assertDatabaseMissing('calendar_day_user', [
        'calendar_day_id' => $day->id,
        'user_id' => $user->id,
    ]);
});

test('wrong riddle answers do not unlock the gift', function () {
    $user = User::factory()->create();
    $day = CalendarDay::factory()->availableOn(Carbon::yesterday())->create([
        'challenge_data' => [
            'question' => 'Je suis blanc et je tombe du ciel. Suis-je ?',
            'accepted_answers' => ['neige', 'flocon'],
        ],
        'gift_title' => 'Un cadeau secret',
    ]);

    $response = $this->from(route('calendar.index'))
        ->actingAs($user)
        ->post(route('calendar.days.solve', $day), ['answer' => 'sable']);

    $response->assertSessionHasErrors('challenge');
    $this->assertDatabaseMissing('calendar_day_user', [
        'calendar_day_id' => $day->id,
        'user_id' => $user->id,
    ]);
});

test('answers are compared without accents, case or punctuation', function () {
    $user = User::factory()->create();
    $day = CalendarDay::factory()->availableOn(Carbon::yesterday())->create([
        'challenge_data' => [
            'question' => 'Suis-je ?',
            'accepted_answers' => ["bonhomme pain d'epice"],
        ],
    ]);

    $response = $this->from(route('calendar.index'))
        ->actingAs($user)
        ->post(route('calendar.days.solve', $day), ['answer' => '  Bonhomme Pain D\'Épice ! ']);

    $response->assertRedirect(route('calendar.index'));
    $this->assertDatabaseHas('calendar_day_user', [
        'calendar_day_id' => $day->id,
        'user_id' => $user->id,
    ]);
});

test('qcm answers are only accepted when the correct option is chosen', function () {
    $user = User::factory()->create();
    $day = CalendarDay::factory()->qcm()->availableOn(Carbon::yesterday())->create([
        'challenge_data' => [
            'question' => 'What color?',
            'options' => ['Rouge', 'Bleu', 'Vert'],
            'correct_index' => 1,
        ],
    ]);

    $this->from(route('calendar.index'))
        ->actingAs($user)
        ->post(route('calendar.days.solve', $day), ['option' => 0])
        ->assertSessionHasErrors('challenge');

    $this->assertDatabaseMissing('calendar_day_user', ['calendar_day_id' => $day->id]);

    $this->from(route('calendar.index'))
        ->actingAs($user)
        ->post(route('calendar.days.solve', $day), ['option' => 1])
        ->assertRedirect(route('calendar.index'));

    $this->assertDatabaseHas('calendar_day_user', [
        'calendar_day_id' => $day->id,
        'user_id' => $user->id,
    ]);
});

test('game challenges are unlocked once the mini-game is completed', function () {
    $user = User::factory()->create();
    $day = CalendarDay::factory()->game()->availableOn(Carbon::yesterday())->create();

    $this->from(route('calendar.index'))
        ->actingAs($user)
        ->post(route('calendar.days.solve', $day), ['completed' => 1])
        ->assertRedirect(route('calendar.index'));

    $this->assertDatabaseHas('calendar_day_user', [
        'calendar_day_id' => $day->id,
        'user_id' => $user->id,
    ]);
});

test('gift content stays hidden in the calendar until the challenge is solved', function () {
    $user = User::factory()->create();
    $day = CalendarDay::factory()->availableOn(Carbon::yesterday())->create([
        'challenge_data' => [
            'question' => 'Suis-je ?',
            'accepted_answers' => ['neige'],
        ],
        'gift_title' => 'Super cadeau',
        'gift_message' => 'Message secret',
        'gift_link_url' => 'https://example.com/cadeau',
    ]);

    $this->actingAs($user)
        ->get(route('calendar.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('calendar/Index')
            ->where('days.0.status', 'available')
            ->where('days.0.gift', null));

    $this->from(route('calendar.index'))
        ->actingAs($user)
        ->post(route('calendar.days.solve', $day), ['answer' => 'neige']);

    $this->actingAs($user)
        ->get(route('calendar.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('calendar/Index')
            ->where('days.0.status', 'solved')
            ->where('days.0.gift.title', 'Super cadeau'));
});

test('users can edit a calendar day challenge and gift', function () {
    Storage::fake('public');
    $user = User::factory()->create();
    $day = CalendarDay::factory()->riddle()->create();

    $response = $this->actingAs($user)->patch(route('calendar.days.update', $day), [
        'challenge_type' => 'qcm',
        'available_at' => Carbon::createFromDate(null, 12, 5)->toDateString(),
        'qcm_question' => 'Quelle est la bonne couleur ?',
        'qcm_options' => ['Bleu', 'Rouge', 'Vert'],
        'qcm_correct_index' => 2,
        'gift_title' => 'Nouveau cadeau',
        'gift_message' => 'Nouveau message',
    ]);

    $response->assertRedirect(route('calendar.edit'));

    $day->refresh();
    expect($day->challenge_type)->toBe('qcm');
    expect($day->challenge_data)->toMatchArray([
        'question' => 'Quelle est la bonne couleur ?',
        'options' => ['Bleu', 'Rouge', 'Vert'],
        'correct_index' => 2,
    ]);
    expect($day->gift_title)->toBe('Nouveau cadeau');
});

test('calendar day updates require fields matching the challenge type', function () {
    $user = User::factory()->create();
    $day = CalendarDay::factory()->riddle()->create();

    $response = $this->actingAs($user)
        ->patch(route('calendar.days.update', $day), [
            'challenge_type' => 'qcm',
            'available_at' => Carbon::tomorrow()->toDateString(),
            // Options manquantes : le qcm est incomplet.
            'qcm_question' => 'Question sans options',
        ]);

    $response->assertSessionHasErrors(['qcm_options', 'qcm_correct_index']);
});

test('guests cannot access the calendar or edit pages', function () {
    $day = CalendarDay::factory()->create();

    $this->get(route('calendar.index'))->assertRedirect(route('login'));
    $this->get(route('calendar.edit'))->assertRedirect(route('login'));
    $this->get(route('calendar.days.edit', $day))->assertRedirect(route('login'));
    $this->post(route('calendar.days.solve', $day))->assertRedirect(route('login'));
    $this->patch(route('calendar.days.update', $day))->assertRedirect(route('login'));
});
