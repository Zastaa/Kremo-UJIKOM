<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_status_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('angsuran_id')->constrained('angsuran')->cascadeOnDelete();
            $table->string('order_id'); // Midtrans order_id
            $table->string('previous_status')->nullable();
            $table->string('new_status');
            $table->json('midtrans_response')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_status_logs');
    }
};
