<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jenis_cicilan', function (Blueprint $table) {
            $table->id();
            $table->integer('lama_cicilan'); // in months
            $table->decimal('margin_kredit', 8, 2); // percentage
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jenis_cicilan');
    }
};
