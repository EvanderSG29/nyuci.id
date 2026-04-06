<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pembayaran extends Model
{
    use HasFactory;

    protected $fillable = [
        'laundry_id',
        'total',
        'status',
    ];

    public function laundry()
    {
        return $this->belongsTo(Laundry::class);
    }
}
