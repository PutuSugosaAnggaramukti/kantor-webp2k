<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up() {
        Schema::table('data_kunjungan_adms', function (Blueprint $table) {
            $table->string('no_angsuran')->nullable()->after('bulan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('data_kunjungans', function (Blueprint $table) {
            //
        });
    }
};
