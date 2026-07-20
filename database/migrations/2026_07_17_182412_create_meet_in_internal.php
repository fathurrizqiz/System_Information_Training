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
        Schema::create('internal_meetings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('periode_id')->unique()->constrained('periode_detail_internal')->cascadeOnDelete();
            $table->text('link_zoom')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meet_in_internal');
    }
};
