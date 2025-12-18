<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Periode;

class PeriodeSeeder extends Seeder
{
    public function run(): void
    {
        Periode::create([
            'nama_periode'   => 'Pekan 1',
            'tanggal_mulai'  => now(),
            'tanggal_selesai' => now()->addDays(7),
        ]);
    }
}
