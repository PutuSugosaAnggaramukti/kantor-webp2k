<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('kunjungans', function (Blueprint $table) {
            $table->string('kol', 2)->nullable()->after('nama_nasabah');
        });
    }
    
    public function down(): void
    {
        Schema::table('kunjungans', function (Blueprint $table) {
            //
        });
    }
};
