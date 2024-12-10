<?php

use App\Models\User;
use function Pest\Laravel\postJson;

it('cannot register a new user with missing data', function () {
    $response = postJson(route('users.register'), []);

    $response->assertUnprocessable()
             ->assertJsonValidationErrors([
                 'name',
                 'email',
             ]);
});

it('cannot register a new user with invalid data', function () {
    $response = postJson(route('users.register'), [
        'name' => 'John Doe',
        'email' => 'invalid-email',
        'password' => 'password',
    ]);

    $response->assertUnprocessable()
             ->assertJsonValidationErrors([
                 'email',
             ]);
});

it('cannot register a new user with an existing email', function () {
    User::factory()->create([
        'email' => 'john@example.com',
    ]);

    $response = postJson(route('users.register'), [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'password',
    ]);

    $response->assertUnprocessable()
             ->assertJsonValidationErrors([
                 'email',
             ]);
});

it('registers a new user', function () {
    $response = postJson(route('users.register'), [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'password',
    ]);

    $response->assertCreated()
             ->assertJsonStructure([
                 'data' => [
                     'id',
                     'name',
                     'email',
                     'wallet' => [
                         'id',
                         'user_id',
                         'currency',
                         'balance',
                         'created_at',
                         'updated_at',
                     ],
                     'created_at',
                     'updated_at',
                 ],
             ]);

    $this->assertDatabaseHas('users', [
        'email' => 'john@example.com',
    ]);

    $this->assertDatabaseHas('wallets', [
        'user_id' => $response->json('data.id'),
        'currency' => config('app.currency'),
        'balance' => 0,
    ]);
});
