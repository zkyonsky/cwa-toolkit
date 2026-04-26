<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Sectoral_gdp;

class SectoralGdpsYearSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $year = 2024;

        $sectoral_gdps = Sectoral_gdp::all();

        foreach ($sectoral_gdps as $sectoral_gdp) {
            $sectoral_gdp->year = $year;
            $sectoral_gdp->save();
        }
    }
}
