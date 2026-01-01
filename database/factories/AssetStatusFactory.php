<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AssetStatus>
 */
class AssetStatusFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            // Gunakan unique() agar nama status yang digenerate tidak kembar
            'name' => fake()->unique()->randomElement(['Baik', 'Rusak', 'Hilang', 'Disewakan', 'Lelang']),
        ];
    }
}
