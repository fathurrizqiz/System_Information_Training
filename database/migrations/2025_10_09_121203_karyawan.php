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
        Schema::create('karyawans', function (Blueprint $table) {
            $table->id();
            $table->string('nama_karyawan', 100);
            $table->date('tmt')->nullable();
            $table->string('nrp', 20)->unique()->nullable();
            $table->string('bagian', 50)->nullable();
            $table->string('unit_kerja', 50)->nullable();
            $table->string('posisi_jabatan', 50)->nullable();
            $table->string('klinis_non_klinis', 50)->nullable();
            $table->string('jenis_kelamin', 10)->nullable();
            // $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('karyawans');
    }
};
