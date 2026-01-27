<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('kunjungans', function (Blueprint $table) {
            // Tambahkan kolom kode_ao sebagai string setelah kolom id
            $table->string('kode_ao')->nullable()->after('id');
        });
    }

    public function down()
    {
        Schema::table('kunjungans', function (Blueprint $table) {
            $table->dropColumn('kode_ao');
        });
    }
};
