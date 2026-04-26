<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ChallengeCategory;

class ChallengeCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            "Pendapatan Terlalu Kecil",
            "Belanja Terlalu Banyak",
            "Manajemen Sumber Daya yang Buruk",
            "Perencanaan yang Buruk untuk Masa Depan",
            "Akses Buruk ke Pembiayaan Jangka Panjang",
            "Merencanakan Infrastruktur Prioritas",
            "Pendanaan Pembangunan Infrastruktur Prioritas",

        ];

        foreach ($categories as $category) {
            ChallengeCategory::create([
                'name' => $category
            ]);
        }
    }
}
