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
        Schema::table('evaluasi_detail_internal', function (Blueprint $table) {
            $table->string('sentimen_materi', 50)->nullable()->after('evaluasipengajar');
            $table->string('sentimen_pengajar', 50)->nullable()->after('sentimen_materi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('evaluasi_detail_internal', function (Blueprint $table) {
            $table->dropColumn(['sentimen_materi', 'sentimen_pengajar']);
        });
    }
};
