<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('data_kunjungan_adms', function (Blueprint $table) {
            // Cek dulu, kalau belum ada baru buat
            if (!Schema::hasColumn('data_kunjungan_adms', 'nominal')) {
                $table->decimal('nominal', 15, 2)->nullable()->after('alamat_nasabah');
            }
            if (!Schema::hasColumn('data_kunjungan_adms', 'sisa_pokok')) {
                $table->decimal('sisa_pokok', 15, 2)->nullable()->after('nominal');
            }
        });
    }

    public function down()
    {
        Schema::table('data_kunjungan_adms', function (Blueprint $table) {
            $table->dropColumn(['nominal', 'sisa_pokok']);
        });
    }
};
