<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            AdminUserSeeder::class,
            SettingsSeeder::class,
            GenZContentSeeder::class,
            NewsDepartmentSeeder::class,
            AuthorsSeeder::class,
            NewToolsBatch1Seeder::class,
            NewBlogPostsBatch1Seeder::class,
            NewNewsBatch1Seeder::class,
            NewToolsBatch2Seeder::class,
            NewBlogPostsBatch2Seeder::class,
            NewNewsBatch2Seeder::class,
            NewToolsBatch3Seeder::class,
            NewBlogPostsBatch3Seeder::class,
            NewNewsBatch3Seeder::class,
        ]);
    }
}
