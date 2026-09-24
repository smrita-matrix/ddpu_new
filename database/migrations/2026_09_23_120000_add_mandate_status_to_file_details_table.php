<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * file_details.status is the COLLECTION RESULT for one transaction
 * (processing / paid / failed), written by the `sync:fastpay-status` command
 * from the remittance report.
 *
 * The customer's MANDATE state in the FastPay portal (Live / Cancelled /
 * Expired / Suspended) is a different thing entirely, so it gets its own
 * column instead of fighting over `status`.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('file_details', function (Blueprint $table) {
            if (!Schema::hasColumn('file_details', 'mandate_status')) {
                $table->string('mandate_status', 20)->nullable()->after('status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('file_details', function (Blueprint $table) {
            if (Schema::hasColumn('file_details', 'mandate_status')) {
                $table->dropColumn('mandate_status');
            }
        });
    }
};
