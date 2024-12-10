<?php


use App\Models\User;
use function Pest\Laravel\postJson;

it('cannot login a user with missing data', function () {
    $response = postJson(route('login'), []);

    $response->assertUnprocessable()
             ->assertJsonValidationErrors([
                 'email',
                 'password',
             ]);
});

it('cannot login a user with invalid data', function () {
    $response = postJson(route('login'), [
        'email' => 'invalid-email',
        'password' => 'password',
    ]);

    $response->assertUnprocessable()
             ->assertJsonValidationErrors([
                 'email',
             ]);
});

it('cannot login a user with incorrect credentials', function () {
    $user = User::factory()->create([
        'email' => 'john@example',
    ]);

    $response = postJson(route('login'), [
        'email' => $user->email,
        'password' => 'invalid-password',
    ]);

    $response->assertUnauthorized();

    $response->assertJsonStructure([
        'message',
    ]);
});

it('logs in a user', function () {
    $user = User::factory()->create([
        'email' => 'john@example.com',
    ]);

    $response = postJson(route('login'), [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response->assertOk()
             ->assertJsonStructure([
                 'data' => [
                     'id',
                     'name',
                     'token',
                     'abilities',
                     'expires_at',
                 ],
             ]);
});
