<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class AccountFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \App\Models\Account::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'account_id' => (int) $this->faker->unique()->numberBetween(100000, 999999),
            'username' => $this->faker->unique()->userName(),
            'password' => Hash::make('password'),
            'role' => 'employee',
        ];
    }

    /**
     * Set role state.
     */
    public function role(string $role)
    {
        return $this->state(fn (array $attributes) => ['role' => $role]);
    }
}
