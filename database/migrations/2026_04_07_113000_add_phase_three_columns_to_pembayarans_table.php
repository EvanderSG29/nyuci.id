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
        Schema::table('pembayarans', function (Blueprint $table) {
            $table->string('metode_pembayaran')->nullable()->after('total');
            $table->date('tgl_pembayaran')->nullable()->after('status');
            $table->text('catatan')->nullable()->after('tgl_pembayaran');
        });

        DB::table('pembayarans')
            ->where('status', 'sudah_bayar')
            ->whereNull('tgl_pembayaran')
            ->update([
                'tgl_pembayaran' => DB::raw('date(created_at)'),
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pembayarans', function (Blueprint $table) {
            $table->dropColumn(['metode_pembayaran', 'tgl_pembayaran', 'catatan']);
        });
    }
};
