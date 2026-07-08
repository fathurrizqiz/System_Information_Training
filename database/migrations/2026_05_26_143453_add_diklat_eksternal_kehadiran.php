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
        Schema::create('diklat_eksternal_kehadiran', function (Blueprint $table) {
            $table->id();

            $table->foreignId('diklat_eksternal_id')
                ->constrained('diklat_eksternal')
                ->onDelete('cascade');

            $table->date('tanggal');

            $table->enum('status', [
                'belum_absen',
                'hadir',
                'tidak_hadir'
            ])->default('belum_absen');

            $table->timestamps();

            $table->unique([
                'diklat_eksternal_id',
                'tanggal'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
