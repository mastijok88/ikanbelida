<?php

namespace App\Models;

use App\Models\User;
use App\Models\Periode;
use App\Models\Kelompok;
use App\Models\Progress;
use Illuminate\Database\Eloquent\Model;

class Tugas extends Model
{
    protected $table = 'tugas';
    protected $fillable = ['periode_id', 'kelompok_id', 'juz', 'diambil_oleh', 'user_id', 'is_additional'];

    // Relasi ke Periode
    public function periode()
    {
        return $this->belongsTo(Periode::class);
    }

    // // Relasi ke Kelompok
    // public function kelompok()
    // {
    //     return $this->belongsTo(Kelompok::class);
    // }

    // // Relasi ke User
    // public function user()
    // {
    //     return $this->belongsTo(User::class);
    // }

    // // Tugas punya banyak progress
    // public function progress()
    // {
    //     return $this->hasMany(Progress::class);
    // }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    
    public function tugasAsli()
    {
        // kalau ini tugas tambahan, cari tugas lama yg diambil
        return $this->belongsTo(Tugas::class, 'tugas_id');
    }
    
    public function tugasTambahan()
    {
        // kalau ini tugas lama, punya banyak tugas tambahan
        return $this->hasMany(Tugas::class, 'tugas_id');
    }

    public function progress()
    {
        return $this->hasMany(Progress::class, 'tugas_id');
    }

    public function kelompok()
    {
        return $this->belongsTo(Kelompok::class, 'kelompok_id');
    }

    public function pengambil()
    {
        return $this->belongsTo(User::class, 'diambil_oleh');
    }

    // public function diambilOleh()
    // {
    //     return $this->belongsTo(User::class, 'diambil_oleh');
    // }
}
