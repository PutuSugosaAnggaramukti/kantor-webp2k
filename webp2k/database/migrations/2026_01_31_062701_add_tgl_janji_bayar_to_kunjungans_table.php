<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
        public function up(): void
    {
        Schema::table('kunjungans', function (Blueprint $table) {
            $table->date('tgl_janji_bayar')->nullable()->after('catatan');
        });
    }

    public function down(): void
    {
        Schema::table('kunjungans', function (Blueprint $table) {
            $table->dropColumn('tgl_janji_bayar');
        });
    }
};
