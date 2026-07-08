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
        Schema::create('detail_internal', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained('program_internal')->cascadeOnDelete();
            $table->string('nama_diklat', 50)->nullable();
            $table->string('keterangan', 20)->nullable();
            $table->string('pengajar', 30)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_internal');
    }
};
