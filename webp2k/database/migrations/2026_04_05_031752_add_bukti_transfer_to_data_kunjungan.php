<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
        public function up()
    {
        // Tambahkan huruf 's' di akhir nama tabel
        Schema::table('kunjungans', function (Blueprint $table) {
            $table->string('bukti_transfer')->nullable()->after('foto_kunjungan');
        });
    }

    public function down()
    {
        Schema::table('kunjungans', function (Blueprint $table) {
            $table->dropColumn('bukti_transfer');
        });
    }
};
