<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kunjungan_janji_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kunjungan_id')->constrained('kunjungans')->cascadeOnDelete();
            $table->date('tgl_lama')->nullable();
            $table->date('tgl_baru')->nullable();
            $table->string('diubah_oleh')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['kunjungan_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kunjungan_janji_histories');
    }
};
