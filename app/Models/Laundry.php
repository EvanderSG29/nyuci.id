<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Laundry extends Model
{
    use HasFactory;

    protected $fillable = [
        'toko_id',
        'klien_id',
        'jasa_id',
        'qty',
        'status',
        'tanggal_dimulai',
        'ets_selesai',
        'nama',
        'no_hp',
        'berat',
        'tanggal',
        'layanan',
        'jenis_jasa',
        'satuan',
        'estimasi_selesai',
        'tgl_selesai',
        'is_taken',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'tanggal_dimulai' => 'date',
            'estimasi_selesai' => 'date',
            'ets_selesai' => 'date',
            'tgl_selesai' => 'date',
            'is_taken' => 'boolean',
            'berat' => 'float',
            'qty' => 'float',
        ];
    }

    public function getJenisJasaLabelAttribute(): string
    {
        $value = $this->jasa?->nama_jasa ?: $this->jenis_jasa ?: $this->layanan;

        return match ($value) {
            'cuci' => 'Cuci',
            'setrika' => 'Setrika',
            'keduanya' => 'Cuci + Setrika',
            default => str($value ?: '-')->replace('_', ' ')->title()->toString(),
        };
    }

    public function getSatuanLabelAttribute(): string
    {
        if ($this->qty !== null && $this->jasa?->satuan) {
            return $this->formatted_qty.' '.$this->jasa->satuan;
        }

        if ($this->satuan) {
            return $this->satuan;
        }

        if ($this->berat === null) {
            return '-';
        }

        $formattedWeight = rtrim(rtrim(number_format((float) $this->berat, 2, '.', ''), '0'), '.');

        return ($formattedWeight !== '' ? $formattedWeight : '0').' kg';
    }

    public function getFormattedQtyAttribute(): string
    {
        $quantity = $this->qty ?? $this->berat ?? 0;

        return rtrim(rtrim(number_format((float) $quantity, 2, '.', ''), '0'), '.');
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'selesai' => 'Selesai',
            'proses' => 'Proses',
            default => 'Belum Selesai',
        };
    }

    public function toko()
    {
        return $this->belongsTo(Toko::class);
    }

    public function klien()
    {
        return $this->belongsTo(Klien::class);
    }

    public function jasa()
    {
        return $this->belongsTo(Jasa::class);
    }

    public function pembayaran()
    {
        return $this->hasOne(Pembayaran::class);
    }
}
