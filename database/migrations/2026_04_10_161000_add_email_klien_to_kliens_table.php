<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('kliens', function (Blueprint $table): void {
            $table->string('email_klien')->nullable()->after('nama_klien');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kliens', function (Blueprint $table): void {
            $table->dropColumn('email_klien');
        });
    }
};
