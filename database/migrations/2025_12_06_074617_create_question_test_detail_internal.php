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
        Schema::create('question_test_detail_internal', function (Blueprint $table) {
            $table->id();
            $table->foreignId('test_id')->constrained('postest_preetest_detail_internal')->cascadeOnDelete();
            $table->text('pertanyaan')->nullable();
            $table->enum('tipe', ['multiple_choice', 'essay']);
            $table->decimal('bobot', 5, 2)->default(0.00);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('question_test_detail_internal');
    }
};
