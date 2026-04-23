<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('data_kunjungan_adms', function (Blueprint $table) {
            // Kita letakkan setelah no_angsuran agar rapi di database
            $table->string('kode_nasabah')->nullable()->after('no_angsuran');
        });
    }

    public function down(): void
    {
        Schema::table('data_kunjungan_adms', function (Blueprint $table) {
            $table->dropColumn('kode_nasabah');
        });
    }
};