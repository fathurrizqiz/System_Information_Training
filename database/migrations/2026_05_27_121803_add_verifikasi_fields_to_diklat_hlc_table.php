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
        Schema::table('diklat_hlc', function (Blueprint $table) {
            $table->string('bukti_hadir', 255)->nullable();
            $table->enum('status_verifikasi', [
                'belum_upload',
                'menunggu_verifikasi',
                'verified',
                'ditolak'
            ])->default('belum_upload');

            $table->timestamp('uploaded_at')->nullable();
            $table->text('catatan_verifikasi')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('diklat_hlc', function (Blueprint $table) {
            $table->dropColumn([
                'bukti_hadir',
                'status_verifikasi',
                'uploaded_at',
                'catatan_verifikasi'
            ]);
        });
    }
};
