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
       Schema::create('nasabahs', function (Blueprint $table) {
        $table->id();
        $table->string('kode'); // Contoh: PG.803
        $table->string('no_angsuran'); 
        $table->string('nasabah'); // Nama Nasabah
        $table->text('alamat');
        $table->decimal('nominal', 15, 2); // Untuk Nominal Pinjaman
        $table->decimal('sisa_pokok', 15, 2);
        $table->string('kol'); // Contoh: Lancar, DPK
        $table->string('bulan'); // Contoh: NOV 2025
        $table->string('kode_ao'); // Contoh: C-006
        $table->string('nama_ao'); // Contoh: Fajar Setyahartadi
        $table->boolean('sudah_kunjung')->default(false); // Untuk ceklis di Laporan
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nasabahs');
    }
};
