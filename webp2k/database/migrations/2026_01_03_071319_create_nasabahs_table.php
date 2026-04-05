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
            $table->string('kode')->nullable();
            $table->string('no_angsuran')->unique(); // No. Anggota
            $table->string('rekening_kredit')->nullable();
            $table->string('kode_nasabah')->nullable();
            $table->string('nasabah'); // Nama
            $table->text('alamat')->nullable();
            $table->date('tgl_pinjam')->nullable();
            $table->date('tgl_jt')->nullable();
            $table->decimal('nominal', 15, 2)->default(0);
            $table->decimal('sisa_pokok', 15, 2)->default(0);
            $table->decimal('pokok_per_bulan', 15, 2)->default(0);
            $table->decimal('bunga_per_bulan', 15, 2)->default(0);
            $table->decimal('tunggakan_pokok', 15, 2)->default(0);
            $table->integer('hari_pokok')->default(0);
            $table->decimal('tunggakan_bunga', 15, 2)->default(0);
            $table->integer('hari_bunga')->default(0);
            $table->decimal('denda', 15, 2)->default(0);
            $table->decimal('bakidebet', 15, 2)->default(0); // Total Tunggakan
            $table->string('kol')->default('1');
            $table->string('bulan');
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
