<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pengiriman', function (Blueprint $table) {
            $table->string('receiver_name')->nullable()->after('telpon_kurir');
            $table->string('receiver_phone', 20)->nullable()->after('receiver_name');
            $table->text('receiver_address')->nullable()->after('receiver_phone');
            $table->unsignedBigInteger('origin_destination_id')->nullable()->after('receiver_address');
            $table->string('origin_label')->nullable()->after('origin_destination_id');
            $table->unsignedBigInteger('destination_destination_id')->nullable()->after('origin_label');
            $table->string('destination_label')->nullable()->after('destination_destination_id');
            $table->unsignedInteger('package_weight')->nullable()->after('destination_label');
            $table->string('courier_code', 30)->nullable()->after('package_weight');
            $table->string('courier_service', 60)->nullable()->after('courier_code');
            $table->unsignedInteger('shipping_cost')->nullable()->after('courier_service');
            $table->string('shipping_etd', 80)->nullable()->after('shipping_cost');
            $table->string('awb_number', 80)->nullable()->after('shipping_etd');
            $table->string('tracking_status', 120)->nullable()->after('awb_number');
            $table->boolean('tracking_delivered')->default(false)->after('tracking_status');
            $table->timestamp('tracking_last_checked_at')->nullable()->after('tracking_delivered');
            $table->json('tracking_payload')->nullable()->after('tracking_last_checked_at');

            $table->index('awb_number');
            $table->index('courier_code');
        });
    }

    public function down(): void
    {
        Schema::table('pengiriman', function (Blueprint $table) {
            $table->dropIndex(['awb_number']);
            $table->dropIndex(['courier_code']);
            $table->dropColumn([
                'receiver_name',
                'receiver_phone',
                'receiver_address',
                'origin_destination_id',
                'origin_label',
                'destination_destination_id',
                'destination_label',
                'package_weight',
                'courier_code',
                'courier_service',
                'shipping_cost',
                'shipping_etd',
                'awb_number',
                'tracking_status',
                'tracking_delivered',
                'tracking_last_checked_at',
                'tracking_payload',
            ]);
        });
    }
};
