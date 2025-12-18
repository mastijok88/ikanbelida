<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Tugas;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    use HasApiTokens, Notifiable;

    protected $fillable = ['name', 'no_hp', 'password', 'role','status',];

    protected $hidden = ['password', 'remember_token'];

    public function kelompok()
    {
        return $this->belongsTo(Kelompok::class, 'kelompok_id');
    }

    public function tugas()
    {
        return $this->hasMany(Tugas::class, 'user_id');
    }

    public function progress()
    {
        return $this->hasMany(Progress::class, 'user_id');
    }
}
