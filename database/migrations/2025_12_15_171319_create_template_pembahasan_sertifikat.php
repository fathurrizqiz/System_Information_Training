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
        Schema::create('template_pembahasan_sertifikat', function (Blueprint $table) {
            $table->id();
            $table->foreignId('periode_id')->constrained('periode_detail_internal')->cascadeOnDelete();
            $table->string('materi',100)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('template_pembahasan_sertifikat');
    }
};
