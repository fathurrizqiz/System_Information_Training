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
        Schema::create('user_answers_post_pree_detail', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained('question_test_detail_internal')->cascadeOnDelete();
            $table->foreignId('choice_id')->nullable()->constrained('question_choices')->cascadeOnDelete();
            $table->string('nrp', 20)->nullable();
            $table->text('essay_answer')->nullable();
            $table->boolean('is_correct')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_answers_post_pree_detail');
    }
};
