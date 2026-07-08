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
        Schema::create('diklat_karyawan', function (Blueprint $table) {
            $table->id();
            // $table->foreignId('diklat_hlc_id')->nullable()->constrained('diklat_hlc')->nullOnDelete();
            $table->string('nrp')->nullable();
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->string('nama_diklat', 50)->nullable();
            $table->string('pengajar', 30);
            $table->string('diklat', 10);
            $table->integer('jam_diklat');
            $table->string('penyelenggara', 50)->nullable();
            $table->string('file_path')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('alasan_penolakan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('diklat_karyawan');
    }
};
