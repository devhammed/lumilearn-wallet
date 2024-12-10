<?php

use App\Models\User;
use function Pest\Laravel\withToken;
use function Pest\Laravel\withoutToken;

it('cannot retrieve user information without authentication', function () {
    $response = withoutToken()->getJson('/user');

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
});
