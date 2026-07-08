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
        Schema::create('rekap_jam_diklat', function (Blueprint $table) {
            $table->id();
            $table->string('nrp', 20);
            $table->integer('bulan');
            $table->integer('tahun');
            $table->integer('total_jam');
            $table->timestamps();

            $table->unique(['nrp', 'bulan']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rekap_jam_diklat');
    }
};
