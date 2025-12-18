<?php

namespace App\Models;

use App\Models\Tugas;
use Illuminate\Database\Eloquent\Model;

class Periode extends Model
{
    protected $table = 'periode';
    protected $fillable = ['nama_periode', 'tanggal_mulai', 'tanggal_selesai', 'nomor_pekan','status'];

    // Periode punya banyak tugas
    public function tugas()
    {
        return $this->hasMany(Tugas::class);
    }
    
    // tambahkan ini biar tanggal otomatis jadi Carbon
    protected $casts = [
        'tanggal_mulai' => 'datetime',
        'tanggal_selesai' => 'datetime',
    ];
}
