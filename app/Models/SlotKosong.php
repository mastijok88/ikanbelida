<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SlotKosong extends Model
{
    use HasFactory;

    protected $table = 'slot_kosong';
    protected $fillable = ['kelompok_id', 'urutan'];
}
