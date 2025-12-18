<?php

namespace App\Imports;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class UsersImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        return new User([
            'name'     => $row['name'],
            'no_hp'    => $row['no_hp'],
            'role'     => $row['role'] ?? 'anggota', // default anggota
            'password' => Hash::make($row['password'] ?? 'password'), // auto hash
        ]);
    }
}
