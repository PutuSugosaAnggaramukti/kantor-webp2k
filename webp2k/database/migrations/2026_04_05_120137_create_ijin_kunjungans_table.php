<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ijin_kunjungans', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('karyawan_id');
            $table->string('kode_ao'); // Untuk memudahkan tracking tanpa join terus-menerus
            $table->date('tanggal'); // Tanggal tidak bisa hadir
            $table->enum('jenis_ijin', ['Sakit', 'Ijin', 'Tugas Kantor', 'Lainnya']);
            $table->text('alasan'); // Detail alasan
            $table->string('bukti_foto')->nullable(); // Jika ada foto surat dokter/keterangan
            $table->timestamps();

            // Indexing untuk mempercepat query dashboard admin
            $table->index(['tanggal', 'kode_ao']);
            
            // Foreign key ke tabel karyawans
            $table->foreign('karyawan_id')->references('id')->on('karyawans')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ijin_kunjungans');
    }
};