<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dashboard_chart_user_overrides', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('dashboard_chart_preset_id')->constrained('dashboard_chart_presets')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->boolean('use_custom')->default(false);
            $table->string('title_override')->nullable();
            $table->string('subtitle_override')->nullable();
            $table->string('chart_type_override', 32)->nullable();
            $table->string('period_granularity_override', 16)->nullable();
            $table->unsignedSmallInteger('period_length_override')->nullable();
            $table->string('primary_metric_override', 64)->nullable();
            $table->string('secondary_metric_override', 64)->nullable();
            $table->string('accent_color_override', 32)->nullable();
            $table->boolean('show_points_override')->nullable();
            $table->timestamps();

            $table->unique(['dashboard_chart_preset_id', 'user_id']);
            $table->index(['user_id', 'dashboard_chart_preset_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dashboard_chart_user_overrides');
    }
};
