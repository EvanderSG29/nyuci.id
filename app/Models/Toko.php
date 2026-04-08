<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Toko extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'nama_toko',
        'alamat',
        'no_hp',
        'background_mode',
        'background_color',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function laundries()
    {
        return $this->hasMany(Laundry::class);
    }

    public function jasas()
    {
        return $this->hasMany(Jasa::class);
    }

    public function kliens()
    {
        return $this->hasMany(Klien::class);
    }
}
