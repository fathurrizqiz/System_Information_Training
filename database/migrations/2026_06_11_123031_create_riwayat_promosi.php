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
        Schema::create('riwayat_promosi', function (Blueprint $table) {
            $table->id();

            $table->string('nrp', 20);
            $table->string('kategori', 50);

            $table->decimal('target_jam', 10, 2);
            $table->decimal('jam_tercapai', 10, 2);

            $table->date('periode_mulai');
            $table->date('periode_selesai')->nullable();

            $table->enum('status', [
                'pending',
                'tercapai'
            ])->default('pending');

            $table->timestamp('tanggal_promosi')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('riwayat_promosi');
    }
};
