<?php

use App\Models\User;

test('guests landing on the root are sent to the login screen', function () {
    $response = $this->get(route('home'));

    $response->assertRedirect(route('login'));
});

test('logged-in users landing on the root are sent to the calendar', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('home'));

    $response->assertRedirect(route('calendar.index'));
});
