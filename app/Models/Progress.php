<?php

namespace App\Models;

use App\Models\Tugas;
use Illuminate\Database\Eloquent\Model;

class Progress extends Model
{
    protected $table = 'progresses';
    protected $fillable = ['user_id', 'tugas_id', 'nama_surat', 'ayat_dari', 'ayat_sampai', 'status'];

    // Relasi ke Tugas
    public function tugas()
    {
        return $this->belongsTo(Tugas::class, 'tugas_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    
    //public function tugas()
    //{
        //return $this->belongsTo(Tugas::class, 'tugas_id');
    //}
}
