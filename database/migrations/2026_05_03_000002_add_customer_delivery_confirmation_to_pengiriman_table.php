<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pengiriman', function (Blueprint $table) {
            $table->string('delivery_verification_status', 40)->default('Belum Dikonfirmasi')->after('tracking_payload');
            $table->timestamp('customer_received_at')->nullable()->after('delivery_verification_status');
            $table->string('customer_received_photo')->nullable()->after('customer_received_at');
            $table->text('customer_received_note')->nullable()->after('customer_received_photo');
            $table->timestamp('delivery_verified_at')->nullable()->after('customer_received_note');
            $table->foreignId('delivery_verified_by')->nullable()->after('delivery_verified_at')->constrained('users')->nullOnDelete();
            $table->text('delivery_verification_note')->nullable()->after('delivery_verified_by');
        });
    }

    public function down(): void
    {
        Schema::table('pengiriman', function (Blueprint $table) {
            $table->dropConstrainedForeignId('delivery_verified_by');
            $table->dropColumn([
                'delivery_verification_status',
                'customer_received_at',
                'customer_received_photo',
                'customer_received_note',
                'delivery_verified_at',
                'delivery_verification_note',
            ]);
        });
    }
};
