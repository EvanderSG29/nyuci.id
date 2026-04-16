<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('laundries', function (Blueprint $table) {
            $table->string('jenis_jasa')->nullable()->after('layanan');
            $table->string('satuan')->nullable()->after('berat');
            $table->date('tgl_selesai')->nullable()->after('estimasi_selesai');
        });

        DB::table('laundries')
            ->select(['id', 'layanan', 'berat'])
            ->orderBy('id')
            ->chunkById(100, function ($laundries): void {
                foreach ($laundries as $laundry) {
                    $berat = (float) $laundry->berat;
                    $formattedWeight = rtrim(rtrim(number_format($berat, 2, '.', ''), '0'), '.');

                    DB::table('laundries')
                        ->where('id', $laundry->id)
                        ->update([
                            'jenis_jasa' => $laundry->layanan,
                            'satuan' => ($formattedWeight !== '' ? $formattedWeight : '0').' kg',
                        ]);
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('laundries', function (Blueprint $table) {
            $table->dropColumn(['jenis_jasa', 'satuan', 'tgl_selesai']);
        });
    }
};
