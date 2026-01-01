<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Category>
 */
class CategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            // Tambahkan baris ini agar setiap kali factory dipanggil,
            // dia otomatis membuatkan nama acak.
            'name' => fake()->unique()->word(), 
        ];
    }
}
