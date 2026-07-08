<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('diklat_hlc', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained('program_diklat_hlc')->cascadeOnDelete();
            $table->string('nama_diklat', 50)->nullable();
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->string('nrp', 20)->nullable();
            $table->string('pengajar', 30)->nullable();
            $table->string('penyelenggara', 50)->nullable();
            $table->string('diklat', 10)->default('HLC');
            $table->integer('jam_diklat')->nullable();
            $table->string('status', 20)->default('approve');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('diklat_hlc');
    }
};
