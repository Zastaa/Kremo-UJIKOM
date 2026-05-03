<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pengajuan_kredit', function (Blueprint $table) {
            $table->string('dp_payment_status', 30)->default('Belum Bayar')->after('catatan_survey');
            $table->string('dp_payment_order_id')->nullable()->after('dp_payment_status');
            $table->string('dp_snap_token')->nullable()->after('dp_payment_order_id');
            $table->unsignedInteger('dp_paid_amount')->default(0)->after('dp_snap_token');
            $table->timestamp('dp_paid_at')->nullable()->after('dp_paid_amount');
            $table->unsignedBigInteger('shipping_origin_destination_id')->nullable()->after('dp_paid_at');
            $table->string('shipping_origin_label')->nullable()->after('shipping_origin_destination_id');
            $table->unsignedBigInteger('shipping_destination_destination_id')->nullable()->after('shipping_origin_label');
            $table->string('shipping_destination_label')->nullable()->after('shipping_destination_destination_id');
            $table->unsignedInteger('shipping_package_weight')->nullable()->after('shipping_destination_label');
            $table->string('shipping_courier_code', 30)->nullable()->after('shipping_package_weight');
            $table->string('shipping_courier_service', 60)->nullable()->after('shipping_courier_code');
            $table->unsignedInteger('shipping_cost')->nullable()->after('shipping_courier_service');
            $table->string('shipping_etd', 80)->nullable()->after('shipping_cost');
        });
    }

    public function down(): void
    {
        Schema::table('pengajuan_kredit', function (Blueprint $table) {
            $table->dropColumn([
                'dp_payment_status',
                'dp_payment_order_id',
                'dp_snap_token',
                'dp_paid_amount',
                'dp_paid_at',
                'shipping_origin_destination_id',
                'shipping_origin_label',
                'shipping_destination_destination_id',
                'shipping_destination_label',
                'shipping_package_weight',
                'shipping_courier_code',
                'shipping_courier_service',
                'shipping_cost',
                'shipping_etd',
            ]);
        });
    }
};
