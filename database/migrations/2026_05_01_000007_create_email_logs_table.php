<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('to_email');
            $table->string('subject');
            $table->string('template');
            $table->string('status')->default('queued'); // queued, sent, failed
            $table->text('error_message')->nullable();
            $table->string('related_type')->nullable(); // morph
            $table->unsignedBigInteger('related_id')->nullable();
            $table->datetime('sent_at')->nullable();
            $table->timestamps();

            $table->index(['related_type', 'related_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_logs');
    }
};
