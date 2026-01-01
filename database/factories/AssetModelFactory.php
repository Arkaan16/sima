<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Manufacturer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AssetModel>
 */
class AssetModelFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(3, true),
            'model_number' => fake()->unique()->numerify('MOD-####'),
            'image' => null,
            // Otomatis buatkan Category & Manufacturer jika tidak disediakan
            'category_id' => Category::factory(), 
            'manufacturer_id' => Manufacturer::factory(),
        ];
    }
}
