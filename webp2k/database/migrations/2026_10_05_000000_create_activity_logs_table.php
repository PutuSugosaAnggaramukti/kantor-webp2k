<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        \App\Models\ActivityLog::createTable();
    }

    public function down(): void
    {
        \Illuminate\Support\Facades\Schema::dropIfExists('activity_logs');
    }
};
