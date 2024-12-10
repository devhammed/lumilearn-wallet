<?php

use App\Models\User;
use App\Models\Wallet;
use App\Http\Controllers\UserController;
use function Pest\Laravel\withToken;
use function Pest\Laravel\withoutToken;

it('requires authentication', function () {
    $response = withoutToken()->getJson(route('user'));

    $response->assertUnauthorized();

    $response->assertJsonStructure(['message']);
})->coversClass(UserController::class);

it('retrieves user information', function () {
    $zeroMoney = money(0);

    $user = User::factory()->create([
        'name' => 'John Doe',
        'email' => 'john@example.com',
    ]);

    $wallet = Wallet::factory()->for($user)->create([
        'balance' => $zeroMoney,
    ]);

    $token = $user->createToken('auth_token')->plainTextToken;

    $response = withToken($token)->getJson(route('user'));

    $response->assertOk();

    $response->assertJson([
        'data' => [
            'id' => $user->getKey(),
            'name' => $user->name,
            'email' => $user->email,
            'wallet' => [
                'id' => $wallet->getKey(),
                'user_id' => $user->getKey(),
                'balance' => $zeroMoney->getArray(),
                'created_at' => $wallet->created_at?->toISOString(),
                'updated_at' => $wallet->updated_at?->toISOString(),
            ],
            'created_at' => $user->created_at?->toISOString(),
            'updated_at' => $user->updated_at?->toISOString(),
        ],
    ]);
})->coversClass(UserController::class);
