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
        Schema::create('diklat_hlc_kehadiran', function (Blueprint $table) {
            $table->id();

            $table->foreignId('diklat_hlc_id')
                ->constrained('diklat_hlc')
                ->onDelete('cascade');

            $table->date('tanggal');

            $table->enum('status', [
                'belum_absen',
                'hadir',
                'tidak_hadir'
            ])->default('belum_absen');

            $table->timestamps();

            $table->unique([
                'diklat_hlc_id',
                'tanggal'
            ]);
        });
    
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('diklat_hlc_kehadiran');
    }
};
