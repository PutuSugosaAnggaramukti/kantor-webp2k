<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('nasabahs', function (Blueprint $table) {
            // Hanya tambah 'kode' jika belum ada
            if (!Schema::hasColumn('nasabahs', 'kode')) {
                $table->string('kode')->nullable()->after('id');
            }

            // Hanya tambah 'kode_ao_nasabah' jika belum ada
            if (!Schema::hasColumn('nasabahs', 'kode_ao_nasabah')) {
                $table->string('kode_ao_nasabah')->nullable()->after('kode');
            }
        });
    }

    public function down()
    {
        Schema::table('nasabahs', function (Blueprint $table) {
            $table->dropColumn(['kode', 'kode_ao_nasabah']);
        });
    }
};
