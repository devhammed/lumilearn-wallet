<?php

use App\Models\User;
use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;
use function Pest\Laravel\withToken;

// User Registration

it('cannot register a new user with missing data', function () {
    $response = postJson('/user', []);

    $response->assertUnprocessable()
             ->assertJsonValidationErrors([
                 'name',
                 'email',
             ]);
});


it('cannot register a new user with invalid data', function () {
    $response = postJson('/user', [
        'name' => 'John Doe',
        'email' => 'invalid-email',
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

    $response = postJson('/user', [
        'name' => 'John Doe',
        'email' => 'john@example.com',
    ]);

    $response->assertUnprocessable()
             ->assertJsonValidationErrors([
                 'email',
             ]);
});

it('registers a new user', function () {
    $response = postJson('/user', [
        'name' => 'John Doe',
        'email' => 'john@example.com',
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
        'user_id' => $response->json('user.id'),
        'currency' => config('app.currency'),
        'balance' => 0,
    ]);
});

// User Login

it('cannot login a user with missing data', function () {
    $response = postJson('/login', []);

    $response->assertUnprocessable()
             ->assertJsonValidationErrors([
                 'email',
                 'password',
             ]);
});

it('cannot login a user with invalid data', function () {
    $response = postJson('/login', [
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

    $response = postJson('/login', [
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

    $response = postJson('/login', [
        'email' => 'john@example.com',
        'password' => 'password',
    ]);

    $response->assertOk()
             ->assertJsonStructure([
                 'data' => [
                     'id',
                     'token',
                     'expires_at',
                 ],
             ]);
});

// User Information

it('cannot retrieve user information without authentication', function () {
    $response = getJson('/user');

    $response->assertUnauthorized();

    $response->assertJsonStructure([
        'message',
    ]);
});

it('retrieves user information', function () {
    $user = User::factory()->create([
        'name' => 'John Doe',
        'email' => 'john@example.com',
    ]);

    $token = $user->createToken('auth_token')->plainTextToken;

    $response = withToken($token)->getJson('/user');

    $response->assertOk()
             ->assertJson([
                 'id' => $user->getKey(),
                 'name' => 'John Doe',
                 'email' => 'john@example.com',
                 'wallet' => [
                     'balance' => 0,
                     'currency' => config('app.currency'),
                 ],
             ]);
});
