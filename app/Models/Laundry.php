<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Laundry extends Model
{
    use HasFactory;

    protected $fillable = [
        'toko_id',
        'nama',
        'no_hp',
        'berat',
        'tanggal',
        'layanan',
        'estimasi_selesai',
        'is_taken',
    ];

    public function toko()
    {
        return $this->belongsTo(Toko::class);
    }

    public function pembayaran()
    {
        return $this->hasOne(Pembayaran::class);
    }
}
