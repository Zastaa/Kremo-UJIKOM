<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipping_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pengiriman_id')->nullable()->constrained('pengiriman')->nullOnDelete();
            $table->string('courier');
            $table->string('service');
            $table->string('description')->nullable();
            $table->double('cost');
            $table->string('etd')->nullable(); // estimasi hari
            $table->json('raw_response')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipping_rates');
    }
};
