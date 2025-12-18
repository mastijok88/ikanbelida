<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Progress;

class ProgressSeeder extends Seeder
{
    public function run(): void
    {
        Progress::create([
            'tugas_id'   => 1,
            'ayat_dari'  => 1,
            'ayat_sampai' => 7,
            'status'     => 'proses',
        ]);
    }
}
