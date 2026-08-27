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
        Schema::table('diklat_karyawan', function (Blueprint $table) {
            $table->boolean('is_deleted')->default(false)->after('id');
            $table->timestamp('deleted_at')->nullable()->after('is_deleted');
            $table->string('deleted_by', 20)->nullable()->after('deleted_at');
        });

        Schema::table('program_diklat_eksternal', function (Blueprint $table) {
            $table->boolean('is_deleted')->default(false)->after('id');
            $table->timestamp('deleted_at')->nullable()->after('is_deleted');
            $table->string('deleted_by', 20)->nullable()->after('deleted_at');
        });

        Schema::table('diklat_eksternal', function (Blueprint $table) {
            $table->boolean('is_deleted')->default(false)->after('id');
            $table->timestamp('deleted_at')->nullable()->after('is_deleted');
            $table->string('deleted_by', 20)->nullable()->after('deleted_at');
        });

        Schema::table('no_hp_karyawan', function (Blueprint $table) {
            $table->boolean('is_deleted')->default(false)->after('id');
            $table->timestamp('deleted_at')->nullable()->after('is_deleted');
            $table->string('deleted_by', 20)->nullable()->after('deleted_at');
        });

        Schema::table('wa_templates', function (Blueprint $table) {
            $table->boolean('is_deleted')->default(false)->after('id');
            $table->timestamp('deleted_at')->nullable()->after('is_deleted');
            $table->string('deleted_by', 20)->nullable()->after('deleted_at');
        });

        Schema::table('karyawans', function (Blueprint $table) {
            $table->boolean('is_deleted')->default(false)->after('id');
            $table->timestamp('deleted_at')->nullable()->after('is_deleted');
            $table->string('deleted_by', 20)->nullable()->after('deleted_at');
        });

        Schema::table('detail_internal', function (Blueprint $table) {
            $table->boolean('is_deleted')->default(false)->after('id');
            $table->timestamp('deleted_at')->nullable()->after('is_deleted');
            $table->string('deleted_by', 20)->nullable()->after('deleted_at');
        });

        Schema::table('program_internal', function (Blueprint $table) {
            $table->boolean('is_deleted')->default(false)->after('id');
            $table->timestamp('deleted_at')->nullable()->after('is_deleted');
            $table->string('deleted_by', 20)->nullable()->after('deleted_at');
        });

        Schema::table('materi_library', function (Blueprint $table) {
            $table->boolean('is_deleted')->default(false)->after('id');
            $table->timestamp('deleted_at')->nullable()->after('is_deleted');
            $table->string('deleted_by', 20)->nullable()->after('deleted_at');
        });

        Schema::table('program_diklat_hlc', function (Blueprint $table) {
            $table->boolean('is_deleted')->default(false)->after('id');
            $table->timestamp('deleted_at')->nullable()->after('is_deleted');
            $table->string('deleted_by', 20)->nullable()->after('deleted_at');
        });

        Schema::table('diklat_hlc', function (Blueprint $table) {
            $table->boolean('is_deleted')->default(false)->after('id');
            $table->timestamp('deleted_at')->nullable()->after('is_deleted');
            $table->string('deleted_by', 20)->nullable()->after('deleted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('diklat_karyawan', function (Blueprint $table) {
            $table->dropColumn(['is_deleted', 'deleted_at', 'deleted_by']);
        });

        Schema::table('program_diklat_eksternal', function (Blueprint $table) {
            $table->dropColumn(['is_deleted', 'deleted_at', 'deleted_by']);
        });

        Schema::table('diklat_eksternal', function (Blueprint $table) {
            $table->dropColumn(['is_deleted', 'deleted_at', 'deleted_by']);
        });

        Schema::table('no_hp_karyawan', function (Blueprint $table) {
            $table->dropColumn(['is_deleted', 'deleted_at', 'deleted_by']);
        });

        Schema::table('wa_templates', function (Blueprint $table) {
            $table->dropColumn(['is_deleted', 'deleted_at', 'deleted_by']);
        });

        Schema::table('karyawans', function (Blueprint $table) {
            $table->dropColumn(['is_deleted', 'deleted_at', 'deleted_by']);
        });

        Schema::table('detail_internal', function (Blueprint $table) {
            $table->dropColumn(['is_deleted', 'deleted_at', 'deleted_by']);
        });

        Schema::table('program_internal', function (Blueprint $table) {
            $table->dropColumn(['is_deleted', 'deleted_at', 'deleted_by']);
        });

        Schema::table('materi_library', function (Blueprint $table) {
            $table->dropColumn(['is_deleted', 'deleted_at', 'deleted_by']);
        });

        Schema::table('program_diklat_hlc', function (Blueprint $table) {
            $table->dropColumn(['is_deleted', 'deleted_at', 'deleted_by']);
        });

        Schema::table('diklat_hlc', function (Blueprint $table) {
            $table->dropColumn(['is_deleted', 'deleted_at', 'deleted_by']);
        });
    }
};