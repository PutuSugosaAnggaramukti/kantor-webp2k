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
        Schema::table('data_kunjungan_adms', function (Blueprint $table) {
            $table->bigInteger('nominal')->default(0)->after('alamat_nasabah');
            $table->bigInteger('sisa_pokok')->default(0)->after('nominal');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('data_kunjungan_adms', function (Blueprint $table) {
            //
        });
    }
};
