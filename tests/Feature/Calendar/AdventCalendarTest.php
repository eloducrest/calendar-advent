<?php

use App\Models\CalendarDay;
use App\Models\User;

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
