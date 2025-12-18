<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tugas;

class TugasSeeder extends Seeder
{
    public function run(): void
    {
        Tugas::create([
            'periode_id'   => 1,
            'kelompok_id'  => 1,
            'juz'          => 1,
            'user_id'      => 1, // pastikan user_id=1 ada di tabel users
        ]);
    }
}
