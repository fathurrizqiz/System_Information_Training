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
        Schema::create('presensi_detail_internal', function (Blueprint $table) {
            $table->id();
            $table->foreignId('detail_program_id')->constrained('detail_internal')->cascadeOnDelete();
            $table->date('tanggal')->nullable();
            $table->string('nama_karyawan', 100)->nullable();
            $table->string('nrp', 20)->nullable();
            $table->integer('jam_diklat')->nullable();
            $table->decimal('prescore', 10, 2)->nullable();
            $table->decimal('postscore', 10, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('presensi_detail_internal');
    }
};
