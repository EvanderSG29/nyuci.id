<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DashboardChartUserOverride extends Model
{
    use HasFactory;

    protected $fillable = [
        'dashboard_chart_preset_id',
        'user_id',
        'use_custom',
        'title_override',
        'subtitle_override',
        'chart_type_override',
        'period_granularity_override',
        'period_length_override',
        'primary_metric_override',
        'secondary_metric_override',
        'accent_color_override',
        'show_points_override',
    ];

    protected function casts(): array
    {
        return [
            'use_custom' => 'boolean',
            'period_length_override' => 'integer',
            'show_points_override' => 'boolean',
        ];
    }

    public function preset()
    {
        return $this->belongsTo(DashboardChartPreset::class, 'dashboard_chart_preset_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
