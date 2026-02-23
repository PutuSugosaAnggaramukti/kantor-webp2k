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
        Schema::table('nasabahs', function (Blueprint $table) {
            // Cek apakah kolom 'nominal' BELUM ada
            if (!Schema::hasColumn('nasabahs', 'nominal')) {
                $table->bigInteger('nominal')->nullable()->after('alamat');
            }
            
            // Cek apakah kolom 'sisa_pokok' BELUM ada
            if (!Schema::hasColumn('nasabahs', 'sisa_pokok')) {
                $table->bigInteger('sisa_pokok')->nullable()->after('nominal');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('nasabahs', function (Blueprint $blueprint) {
            $blueprint->dropColumn(['nominal', 'sisa_pokok']);
        });
    }
};
