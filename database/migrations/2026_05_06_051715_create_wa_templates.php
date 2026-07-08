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
        Schema::create('wa_templates', function (Blueprint $table) {
            $table->id();
            $table->string('nama_template', 50); // Contoh: Notif Jadwal Baru, Pengingat Absensi
            $table->string('slug', 100)->unique(); // Contoh: notif-jadwal
            $table->text('pesan');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wa_templates');
    }
};
