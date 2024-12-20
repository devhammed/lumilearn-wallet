<?php

use App\Models\User;
use App\Http\Controllers\RegisterController;
use function Pest\Laravel\postJson;

it('cannot register a new user with missing data', function () {
    $response = postJson(route('register'), []);

    $response->assertUnprocessable();

    $response->assertJsonValidationErrors([
        'name',
        'email',
    ]);
})->coversClass(RegisterController::class);

it('cannot register a new user with invalid data', function () {
    $response = postJson(route('register'), [
        'name' => 'John Doe',
        'email' => 'invalid-email',
        'password' => 'password',
    ]);

    $response->assertUnprocessable();

    $response->assertJsonValidationErrors([
        'email',
    ]);
})->coversClass(RegisterController::class);

it('cannot register a new user with an existing email', function () {
    User::factory()->create([
        'email' => 'john@example.com',
    ]);

    $response = postJson(route('register'), [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'password',
    ]);

    $response->assertUnprocessable();

    $response->assertJsonValidationErrors([
        'email',
    ]);
})->coversClass(RegisterController::class);

it('registers a new user', function () {
    $response = postJson(route('register'), [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'password',
    ]);

    $response->assertCreated();

    $response->assertJsonStructure([
        'data' => [
            'id',
            'name',
            'email',
            'wallet' => [
                'id',
                'user_id',
                'balance' => [
                    'amount',
                    'currency',
                    'formatted',
                ],
                'created_at',
                'updated_at',
            ],
            'created_at',
            'updated_at',
        ],
    ]);

    $this->assertDatabaseHas('users', [
        'email' => $response->json('data.email'),
    ]);

    $this->assertDatabaseHas('wallets', [
        'user_id' => $response->json('data.id'),
        'balance->amount' => 0,
        'balance->currency' => config('money.defaults.currency'),
    ]);
})->coversClass(RegisterController::class);
