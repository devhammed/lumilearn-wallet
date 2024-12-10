<?php

use App\Http\Controllers\UserController;
use App\Models\User;
use function Pest\Laravel\withoutToken;
use function Pest\Laravel\withToken;

it('cannot retrieve user information without authentication', function () {
    $response = withoutToken()->getJson(route('user'));

    $response->assertUnauthorized();

    $response->assertJsonStructure([
        'message',
    ]);
})->coversClass(UserController::class);

it('retrieves user information', function () {
    $user = User::factory()->create([
        'name' => 'John Doe',
        'email' => 'john@example.com',
    ]);

    $user->wallet()->create();

    $token = $user->createToken('auth_token')->plainTextToken;

    $response = withToken($token)->getJson(route('user'));

    $response->assertOk();

    $response->assertJson([
        'data' => [
            'id' => $user->getKey(),
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'wallet' => [
                'balance' => 0,
                'currency' => config('app.currency')->value,
            ],
        ],
    ]);
})->coversClass(UserController::class);
