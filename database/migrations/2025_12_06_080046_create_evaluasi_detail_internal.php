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
        Schema::create('evaluasi_detail_internal', function (Blueprint $table) {
            $table->id();
            $table->foreignId('detail_id')->constrained('detail_internal')->cascadeOnDelete();
            $table->text('evaluasimateri')->nullable();
            $table->text('evaluasipengajar')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evaluasi_detail_internal');
    }
};
