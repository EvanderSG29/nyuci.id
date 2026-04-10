<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pembayarans', function (Blueprint $table): void {
            $table->string('gateway_status')->nullable();
            $table->dateTime('gateway_paid_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('pembayarans', function (Blueprint $table): void {
            $table->dropColumn(['gateway_paid_at', 'gateway_status']);
        });
    }
};
