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
        Schema::create('impersonate_requests', function (Blueprint $table) {
            $table->id();
            $table->string('admin_nrp');
            $table->string('target_nrp');
            $table->enum('status', ['pending', 'approved', 'rejected', 'expired'])->default('pending');
            $table->timestamp('expires_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('impersonate_requests');
    }
};
