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
        Schema::create('materi_library', function (Blueprint $table) {
            $table->id();
            $table->string('title', 50);
            $table->enum('type', ['folder', 'file']);
            $table->string('file_path', 255)->nullable();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->foreign('parent_id')->references('id')->on('materi_library')->onDelete('cascade');
            // $table->foreignId('uploaded_by')->constrained('users')->nullable();
            $table->enum('status', ['pending', 'verified', 'rejected'])->default('pending');
            $table->text('reject_reason')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('materi_library');
    }
};
