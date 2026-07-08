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
        Schema::create('aksi_detail_internal', function (Blueprint $table) {
            $table->id();
            $table->foreignId('periode_id')->constrained('periode_detail_internal')->cascadeOnDelete();
            $table->date('periode')->nullable();
            $table->integer('jam_diklat')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aksi_detail_internal');
    }
};
