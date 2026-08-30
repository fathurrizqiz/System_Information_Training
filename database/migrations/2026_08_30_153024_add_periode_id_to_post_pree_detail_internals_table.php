<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('postest_preetest_detail_internal', function (Blueprint $table) {
            $table->unsignedBigInteger('periode_id')->nullable()->after('detail_program_id');
            $table->foreign('periode_id')->references('id')->on('periode_detail_internal')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('postest_preetest_detail_internal', function (Blueprint $table) {
            $table->dropForeign(['periode_id']);
            $table->dropColumn('periode_id');
        });
    }
};
