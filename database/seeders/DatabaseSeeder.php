<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            // RolesTableSeeder::class,
            PermissionsTableSeeder::class,
            // UserTableSeeder::class,
            // GovsSeeder::class,
            // Budget_realSeeder::class,
            // Economy_indicatorSeeder::class,
            // Budget_planSeeder::class,
            // FinancingSeeder::class,
            // Sectoral_gdpSeeder::class,
            // SectoralGdpsYearSeeder::class,
            // ChallengeSeeder::class,
            // ChallengeCategorySeeder::class,
            // ChallengeActionSeeder::class,
        ]);
    }
}
