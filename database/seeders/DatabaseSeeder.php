<?php
// database/seeders/DatabaseSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            DangerCategorySeeder::class,
            // Ajouter ici d'autres seeders si nécessaire
            // UserSeeder::class,
            // PlaygroundSeeder::class,
        ]);
    }
}
