<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Kelompok;

class KelompokSeeder extends Seeder
{
    public function run(): void
    {
        Kelompok::create([
            'nama_kelompok' => 'Kelompok A',
        ]);
    }
}
