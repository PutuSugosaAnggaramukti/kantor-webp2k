<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   public function up(): void
    {
        Schema::table('nasabahs', function (Blueprint $table) {
            // Tambahkan kolom kode_agunan setelah kolom kode_ao_nasabah
            $table->string('kode_agunan')->nullable()->after('kode_ao_nasabah');
        });
    }

    public function down(): void
    {
        Schema::table('nasabahs', function (Blueprint $table) {
            $table->dropColumn('kode_agunan');
        });
    }
};
