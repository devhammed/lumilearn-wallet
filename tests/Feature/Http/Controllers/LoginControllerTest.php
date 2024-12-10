<?php


use App\Models\User;
use App\Http\Controllers\LoginController;
use function Pest\Laravel\postJson;

it('cannot login a user with missing data', function () {
    $response = postJson(route('login'), []);

    $response->assertUnprocessable();

    $response->assertJsonValidationErrors([
        'email',
        'password',
    ]);
})->coversClass(LoginController::class);

it('cannot login a user with invalid data', function () {
    $response = postJson(route('login'), [
        'email' => 'invalid-email',
        'password' => 'password',
    ]);

    $response->assertUnprocessable();

    $response->assertJsonValidationErrors([
        'email',
    ]);
})->coversClass(LoginController::class);

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
})->coversClass(LoginController::class);

it('logs in a user', function () {
    $user = User::factory()->create([
        'email' => 'john@example.com',
    ]);

    $user->wallet()->create();

    $response = postJson(route('login'), [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response->assertOk();

    $response->assertJsonStructure([
        'data' => [
            'id',
            'user_id',
            'name',
            'token',
            'abilities',
            'expires_at',
        ],
    ]);

    $response->assertJson([
        'data' => [
            'user_id' => $user->id,
        ],
    ]);
})->coversClass(LoginController::class);
