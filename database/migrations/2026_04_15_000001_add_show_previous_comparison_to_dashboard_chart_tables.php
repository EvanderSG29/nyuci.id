<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dashboard_chart_presets', function (Blueprint $table): void {
            $table->boolean('show_previous_comparison')->default(true);
        });

        Schema::table('dashboard_chart_user_overrides', function (Blueprint $table): void {
            $table->boolean('show_previous_comparison_override')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('dashboard_chart_user_overrides', function (Blueprint $table): void {
            $table->dropColumn('show_previous_comparison_override');
        });

        Schema::table('dashboard_chart_presets', function (Blueprint $table): void {
            $table->dropColumn('show_previous_comparison');
        });
    }
};
