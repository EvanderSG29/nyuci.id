<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pembayarans', function (Blueprint $table): void {
            if (! Schema::hasColumn('pembayarans', 'gateway_status')) {
                $table->string('gateway_status')->nullable();
            }

            if (! Schema::hasColumn('pembayarans', 'gateway_paid_at')) {
                $table->dateTime('gateway_paid_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('pembayarans', function (Blueprint $table): void {
            $columnsToDrop = [];

            if (Schema::hasColumn('pembayarans', 'gateway_paid_at')) {
                $columnsToDrop[] = 'gateway_paid_at';
            }

            if (Schema::hasColumn('pembayarans', 'gateway_status')) {
                $columnsToDrop[] = 'gateway_status';
            }

            if ($columnsToDrop !== []) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};
