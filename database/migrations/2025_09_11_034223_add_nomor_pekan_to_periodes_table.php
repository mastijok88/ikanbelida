<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('periode', function (Blueprint $table) {
            $table->integer('nomor_pekan')->after('id')->default(1);
        });
    }

    public function down()
    {
        Schema::table('periode', function (Blueprint $table) {
            $table->dropColumn('nomor_pekan');
        });
    }
};
