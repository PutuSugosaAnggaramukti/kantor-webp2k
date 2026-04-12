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
        Schema::table('kunjungans', function (Blueprint $row) {
            // Menambahkan kolom alamat_nasabah setelah nama_nasabah
            // Gunakan text karena alamat biasanya panjang
            $row->text('alamat_nasabah')->nullable()->after('nama_nasabah');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kunjungans', function (Blueprint $row) {
            // Menghapus kolom jika migration di-rollback
            $row->dropColumn('alamat_nasabah');
        });
    }
};
