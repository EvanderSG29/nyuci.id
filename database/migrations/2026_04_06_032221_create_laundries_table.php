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
        Schema::create('laundries', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('no_hp');
            $table->float('berat');
            $table->date('tanggal');
            $table->enum('layanan', ['cuci', 'setrika', 'keduanya']);
            $table->date('estimasi_selesai');
            $table->boolean('is_taken')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('laundries');
    }
};
