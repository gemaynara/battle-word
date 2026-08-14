<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class ProductionSeeder extends Seeder
{
    /**
     * Seed production data (no Faker dependency).
     */
    public function run(): void
    {
        $this->call([
            AchievementSeeder::class,
            DictionarySeeder::class,
            CategorizedDictionarySeeder::class,
        ]);
    }
}
