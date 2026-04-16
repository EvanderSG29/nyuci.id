<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dashboard_chart_presets', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('toko_id')->constrained()->cascadeOnDelete();
            $table->string('slot_key', 32);
            $table->string('title');
            $table->string('subtitle')->nullable();
            $table->string('chart_type', 32);
            $table->string('period_granularity', 16);
            $table->unsignedSmallInteger('period_length');
            $table->string('primary_metric', 64);
            $table->string('secondary_metric', 64)->nullable();
            $table->string('accent_color', 32)->nullable();
            $table->boolean('show_points')->default(true);
            $table->timestamps();

            $table->unique(['toko_id', 'slot_key']);
            $table->index(['toko_id', 'slot_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dashboard_chart_presets');
    }
};
