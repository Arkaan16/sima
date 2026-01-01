<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Loop sebanyak 30 kali
        foreach (range(1, 30) as $index) {
            Category::create([
                // Membuat nama kategori acak (2 kata) & unik
                // ucwords agar huruf depan kapital (contoh: "Electronic Gadget")
                'name' => ucwords(fake()->unique()->words(2, true)), 
            ]);
        }
    }
}