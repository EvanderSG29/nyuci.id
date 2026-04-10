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
        Schema::table('pembayarans', function (Blueprint $table): void {
            if (! Schema::hasColumn('pembayarans', 'gateway_provider')) {
                $table->string('gateway_provider')->nullable()->after('catatan');
            }

            if (! Schema::hasColumn('pembayarans', 'gateway_reference')) {
                $table->string('gateway_reference')->nullable()->after('gateway_provider');
            }

            if (! Schema::hasColumn('pembayarans', 'gateway_invoice_id')) {
                $table->string('gateway_invoice_id')->nullable()->after('gateway_reference');
            }

            if (! Schema::hasColumn('pembayarans', 'gateway_token')) {
                $table->string('gateway_token', 128)->nullable()->after('gateway_invoice_id');
            }

            if (! Schema::hasColumn('pembayarans', 'gateway_payment_url')) {
                $table->text('gateway_payment_url')->nullable()->after('gateway_token');
            }

            if (! Schema::hasColumn('pembayarans', 'gateway_qr_image')) {
                $table->longText('gateway_qr_image')->nullable()->after('gateway_payment_url');
            }

            if (! Schema::hasColumn('pembayarans', 'gateway_request_date')) {
                $table->date('gateway_request_date')->nullable()->after('gateway_qr_image');
            }

            if (! Schema::hasColumn('pembayarans', 'gateway_expires_at')) {
                $table->dateTime('gateway_expires_at')->nullable()->after('gateway_request_date');
            }

            if (! Schema::hasColumn('pembayarans', 'gateway_status')) {
                $table->string('gateway_status')->nullable()->after('gateway_expires_at');
            }

            if (! Schema::hasColumn('pembayarans', 'gateway_customer_name')) {
                $table->string('gateway_customer_name')->nullable()->after('gateway_status');
            }

            if (! Schema::hasColumn('pembayarans', 'gateway_method_by')) {
                $table->string('gateway_method_by')->nullable()->after('gateway_customer_name');
            }

            if (! Schema::hasColumn('pembayarans', 'gateway_paid_at')) {
                $table->dateTime('gateway_paid_at')->nullable()->after('gateway_method_by');
            }

            if (! Schema::hasColumn('pembayarans', 'gateway_payload')) {
                $table->json('gateway_payload')->nullable()->after('gateway_paid_at');
            }

            $table->unique('gateway_token');
            $table->unique('gateway_reference');
            $table->unique('gateway_invoice_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pembayarans', function (Blueprint $table): void {
            $table->dropUnique(['gateway_token']);
            $table->dropUnique(['gateway_reference']);
            $table->dropUnique(['gateway_invoice_id']);
            $table->dropColumn([
                'gateway_provider',
                'gateway_reference',
                'gateway_invoice_id',
                'gateway_token',
                'gateway_payment_url',
                'gateway_qr_image',
                'gateway_request_date',
                'gateway_expires_at',
                'gateway_status',
                'gateway_customer_name',
                'gateway_method_by',
                'gateway_paid_at',
                'gateway_payload',
            ]);
        });
    }
};
