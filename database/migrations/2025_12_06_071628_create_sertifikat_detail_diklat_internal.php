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
        Schema::create('sertifikat_detail_diklat_internal', function (Blueprint $table) {
            $table->id();
            $table->foreignId('detail_program_id')->constrained('detail_internal')->cascadeOnDelete();
            $table->json('materi')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sertifikat_detail_diklat_internal');
    }
};
