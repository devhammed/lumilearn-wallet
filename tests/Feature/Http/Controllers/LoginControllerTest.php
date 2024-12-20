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
            'tokenable_id',
            'tokenable_type',
            'name',
            'token',
            'abilities',
            'expires_at',
        ],
    ]);

    $response->assertJson([
        'data' => [
            'tokenable_id' => $user->getKey(),
            'tokenable_type' => $user->getMorphClass(),
        ],
    ]);

    $this->assertDatabaseHas('personal_access_tokens', [
        'id' => $response->json('data.id'),
        'tokenable_id' => $user->getKey(),
        'tokenable_type' => $user->getMorphClass(),
        'name' => $response->json('data.name'),
    ]);
})->coversClass(LoginController::class);
