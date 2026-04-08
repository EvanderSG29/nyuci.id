<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Jasa extends Model
{
    use HasFactory;

    protected $fillable = [
        'toko_id',
        'nama_jasa',
        'satuan',
        'harga',
    ];

    protected function casts(): array
    {
        return [
            'harga' => 'integer',
        ];
    }

    public function toko()
    {
        return $this->belongsTo(Toko::class);
    }

    public function laundries()
    {
        return $this->hasMany(Laundry::class);
    }

    public function getLabelAttribute(): string
    {
        return trim($this->nama_jasa.' / '.$this->satuan);
    }
}
