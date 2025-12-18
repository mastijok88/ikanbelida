<?php

namespace App\Models;

use App\Models\User;
use App\Models\Tugas;
use Illuminate\Database\Eloquent\Model;

class Kelompok extends Model
{
    protected $table = 'kelompok';
    protected $fillable = ['nama_kelompok'];

    // public function users()
    // {
    //     return $this->hasMany(User::class, 'kelompok_id');
    // }

    // public function tugas()
    // {
    //     return $this->hasMany(Tugas::class, 'kelompok_id');
    // }

    // Kelompok.php
    public function tugas()
    {
        return $this->hasMany(Tugas::class, 'kelompok_id');
    }



    public function users()
    {
        return $this->hasManyThrough(User::class, Tugas::class, 'kelompok_id', 'id', 'id', 'user_id');
    }
}
