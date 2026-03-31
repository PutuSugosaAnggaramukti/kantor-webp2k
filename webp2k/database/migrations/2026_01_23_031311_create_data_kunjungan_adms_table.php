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
        Schema::create('data_kunjungan_adms', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('karyawan_id');
            $table->string('kode_ao')->nullable();
            $table->string('no_angsuran')->nullable();
            $table->string('nama_nasabah');
            $table->text('alamat_nasabah')->nullable();
            
            // Finansial (Sesuai Controller)
            $table->decimal('nominal', 15, 2)->default(0);
            $table->decimal('sisa_pokok', 15, 2)->default(0);
            
            $table->string('kol')->default('1');
            $table->boolean('is_hb')->default(false); // Kolom baru untuk Hapus Buku
            
            $table->string('bulan'); // Format YYYY-MM
            $table->date('tanggal')->nullable(); // Kolom baru untuk Tanggal Instruksi Kunjungan
            
            $table->foreign('karyawan_id')->references('id')->on('karyawans')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('data_kunjungan_adms');
    }
};