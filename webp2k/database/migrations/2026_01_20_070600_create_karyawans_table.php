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
        Schema::create('karyawans', function (Blueprint $table) {
            $table->id();
            $table->string('kode_ao')->unique(); // Sesuai label "Kode AO"
            $table->string('nama');              // Sesuai label "Nama"
            $table->string('username')->unique(); // Sesuai label "Username"
            $table->string('password');          // Sesuai label "Password"
            $table->enum('status', ['Aktif', 'Non Aktif'])->default('Aktif'); // Sesuai label "Status"
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('karyawans');
    }
};
