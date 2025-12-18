<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Kelompok;

class KelompokUserSeeder extends Seeder
{
    public function run(): void
    {
        // Buat beberapa kelompok
        $kelompok1 = Kelompok::create(['nama_kelompok' => 'Kelompok A']);
        $kelompok2 = Kelompok::create(['nama_kelompok' => 'Kelompok B']);

        // Tambahkan user ke dalam kelompok A
        User::create([
            'name' => 'Admin Utama',
            'no_hp' => '08333333333333',
            'password' => bcrypt('password'),
            'kelompok_id' => $kelompok1->id,
        ]);

        User::create([
            'name' => 'Anggota 1',
            'no_hp' => '0844444444444',
            'password' => bcrypt('password'),
            'kelompok_id' => $kelompok1->id,
        ]);

        // Tambahkan user ke dalam kelompok B
        User::create([
            'name' => 'Anggota 2',
            'no_hp' => '08555555555555',
            'password' => bcrypt('password'),
            'kelompok_id' => $kelompok2->id,
        ]);
    }
}
