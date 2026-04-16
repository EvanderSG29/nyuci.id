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
        Schema::table('tokos', function (Blueprint $table) {
            if (! Schema::hasColumn('tokos', 'payment_gateway_qris_payload')) {
                $table->longText('payment_gateway_qris_payload')->nullable()->after('no_hp');
            }

            if (! Schema::hasColumn('tokos', 'payment_gateway_qris_merchant_name')) {
                $table->string('payment_gateway_qris_merchant_name')->nullable()->after('payment_gateway_qris_payload');
            }

            if (! Schema::hasColumn('tokos', 'payment_gateway_checkout_ttl_minutes')) {
                $table->unsignedSmallInteger('payment_gateway_checkout_ttl_minutes')->nullable()->after('payment_gateway_qris_merchant_name');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tokos', function (Blueprint $table) {
            $columnsToDrop = [];

            if (Schema::hasColumn('tokos', 'payment_gateway_checkout_ttl_minutes')) {
                $columnsToDrop[] = 'payment_gateway_checkout_ttl_minutes';
            }

            if (Schema::hasColumn('tokos', 'payment_gateway_qris_merchant_name')) {
                $columnsToDrop[] = 'payment_gateway_qris_merchant_name';
            }

            if (Schema::hasColumn('tokos', 'payment_gateway_qris_payload')) {
                $columnsToDrop[] = 'payment_gateway_qris_payload';
            }

            if ($columnsToDrop !== []) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};
