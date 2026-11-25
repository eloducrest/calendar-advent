<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

test('guests and players cannot reach the players screen', function () {
    $player = User::factory()->create();

    $this->get(route('players.index'))->assertRedirect(route('login'));

    $this->actingAs($player)
        ->get(route('players.index'))
        ->assertRedirect(route('calendar.index'));

    $this->from(route('players.index'))
        ->actingAs($player)
        ->post(route('players.store'), [
            'name' => 'Temptation',
            'email' => 'temptation@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])
        ->assertRedirect(route('calendar.index'));

    $this->assertDatabaseMissing('users', ['email' => 'temptation@example.com']);
});

test('an admin can create a player account', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)
        ->post(route('players.store'), [
            'name' => 'Camille',
            'email' => 'camille@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

    $response->assertRedirect(route('players.index'));

    $player = User::query()->where('email', 'camille@example.com')->firstOrFail();
    expect($player->role)->toBe(User::ROLE_PLAYER);
    expect(Hash::check('password123', $player->password))->toBeTrue();
    expect($player->email_verified_at)->not->toBeNull();
});

test('an admin sees the players list', function () {
    $admin = User::factory()->admin()->create();
    $player = User::factory()->create(['name' => 'Joueur un']);

    $response = $this->actingAs($admin)->get(route('players.index'));

    $response->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('players/Index')
            ->where('players.0.name', $player->name));
});

test('creating a player requires a valid email and matching password', function () {
    $admin = User::factory()->admin()->create();
    $existing = User::factory()->create();

    $response = $this->actingAs($admin)
        ->post(route('players.store'), [
            'name' => 'Anonyme',
            'email' => $existing->email,
            'password' => 'secret',
            'password_confirmation' => 'different',
        ]);

    $response->assertSessionHasErrors(['email', 'password']);
});

test('the public registration route is disabled', function () {
    $this->get('/register')->assertNotFound();
    $this->post('/register', ['name' => 'x', 'email' => 'x@example.com', 'password' => 'secret123', 'password_confirmation' => 'secret123'])->assertNotFound();
});
