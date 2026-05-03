<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_reminder_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('kredit_id')->nullable()->constrained('kredit')->nullOnDelete();
            $table->foreignId('angsuran_id')->nullable()->constrained('angsuran')->nullOnDelete();
            $table->string('reminder_type'); // H-7, H-3, H-1, H0, H+1, H+3, H+7
            $table->date('due_date');
            $table->datetime('sent_at')->nullable();
            $table->string('status')->default('pending'); // pending, sent, failed, skipped
            $table->timestamps();

            $table->index(['angsuran_id', 'reminder_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_reminder_logs');
    }
};
