<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Wallet>
 */
class WalletFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'balance' => money($this->faker->numberBetween(100, 100_000_000)),
        ];
    }

    /**
     * Indicate that the wallet's balance is zero.
     */
    public function withZeroBalance(): WalletFactory
    {
        return $this->state(fn(array $attributes) => [
            'balance' => money(0),
        ]);
    }
}
