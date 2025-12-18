<?php

// database/seeders/UserSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // bikin 100 user role siswa
        for ($i = 1; $i <= 100; $i++) {
            User::create([
                'name' => 'Anggota ' . $i,
                'no_hp' => '0812345678' . $i . '@mail.com',
                'password' => Hash::make('password'), // default password
                'role' => 'anggota',
            ]);
        }
    }
}
