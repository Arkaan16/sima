<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Manufacturer>
 */
class ManufacturerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            // Gunakan fake()->company() agar namanya terlihat nyata
            'name' => fake()->unique()->company(),
            
            // Data dummy untuk URL & Kontak
            'url' => fake()->url(),
            'support_url' => fake()->url(),
            'support_email' => fake()->companyEmail(),
            'support_phone' => fake()->phoneNumber(),
            
            // Default image null dulu, nanti di test kita isi manual
            'image' => null, 
        ];
    }
}
