<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DashboardChartPreset extends Model
{
    use HasFactory;

    protected $fillable = [
        'toko_id',
        'slot_key',
        'title',
        'subtitle',
        'chart_type',
        'period_granularity',
        'period_length',
        'primary_metric',
        'secondary_metric',
        'accent_color',
        'show_points',
    ];

    protected function casts(): array
    {
        return [
            'period_length' => 'integer',
            'show_points' => 'boolean',
        ];
    }

    public function toko()
    {
        return $this->belongsTo(Toko::class);
    }

    public function overrides()
    {
        return $this->hasMany(DashboardChartUserOverride::class);
    }
}
