<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   public function up()
    {
        Schema::table('ijin_kunjungans', function (Blueprint $table) {
            // Kita tambah kolom status setelah kolom 'alasan'
            $table->enum('status', ['pending', 'disetujui', 'ditolak'])->default('pending')->after('alasan');
        });
    }

    public function down()
    {
        Schema::table('ijin_kunjungans', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
