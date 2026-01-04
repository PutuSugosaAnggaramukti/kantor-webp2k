<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up()
    {
        Schema::create('kunjungans', function (Blueprint $table) {
            $table->id();
            $table->string('no_nasabah');
            $table->string('nama_nasabah');
            $table->string('keterangan_nasabah')->nullable();
            $table->enum('ada_di_lokasi', ['Ada', 'Tidak Ada']);
            $table->text('catatan')->nullable();
            $table->string('foto_kunjungan'); // Menyimpan path file gambar
            $table->string('koordinat')->nullable(); // Untuk menyimpan latitude, longitude
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kunjungans');
    }
};
