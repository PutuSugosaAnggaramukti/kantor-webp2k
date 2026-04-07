<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kunjungans', function (Blueprint $table) {
            // Kita pakai enum supaya pilihan statusnya terbatas dan aman
            $table->enum('status', ['Menunggu Pembayaran', 'Sudah Bayar', 'Gagal Bayar'])
                ->default('Menunggu Pembayaran');
        });
    }

    public function down(): void
    {
        Schema::table('kunjungans', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
