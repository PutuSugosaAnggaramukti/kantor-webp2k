<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   public function up()
    {
        Schema::table('kunjungans', function (Blueprint $table) {
            $table->decimal('nominal_janji_bayar', 15, 2)->nullable()->after('tgl_janji_bayar');
        });
    }

    public function down()
    {
        Schema::table('kunjungans', function (Blueprint $table) {
            $table->dropColumn('nominal_janji_bayar');
        });
    }
};
