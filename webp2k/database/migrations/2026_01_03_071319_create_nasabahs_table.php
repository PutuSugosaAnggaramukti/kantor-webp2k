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
        $table->string('kode'); 
        $table->string('no_angsuran'); 
        $table->string('nasabah'); 
        $table->text('alamat');
        $table->decimal('nominal', 15, 2); 
        $table->decimal('sisa_pokok', 15, 2);
        $table->string('kol'); 
        $table->string('bulan'); 
        $table->string('kode_ao'); 
        $table->string('nama_ao'); 
        $table->boolean('sudah_kunjung')->default(false); 
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
