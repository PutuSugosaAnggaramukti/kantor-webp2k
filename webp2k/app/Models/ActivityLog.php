<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class ActivityLog extends Model
{
    protected $table = 'activity_logs';

    protected $guarded = [];

    /** @var bool status ketersediaan tabel (dicek sekali per proses) */
    protected static bool $tableReady = false;

    /**
     * Buat tabel activity_logs jika belum ada.
     * Dipakai oleh migration DAN fallback otomatis, supaya fitur tetap jalan
     * di server yang belum menjalankan `php artisan migrate`.
     */
    public static function createTable(): bool
    {
        if (static::$tableReady) {
            return true;
        }

        try {
            if (!Schema::hasTable('activity_logs')) {
                Schema::create('activity_logs', function (Blueprint $table) {
                    $table->id();
                    $table->string('user_type', 20)->default('Tamu'); // Admin / AO / Tamu
                    $table->unsignedBigInteger('user_id')->nullable();
                    $table->string('name')->nullable();
                    $table->string('action');
                    $table->text('description')->nullable();
                    $table->string('ip_address', 45)->nullable();
                    $table->string('user_agent')->nullable();
                    $table->timestamps();
                });
            }
            static::$tableReady = true;
        } catch (\Throwable $e) {
            report($e);
        }

        return static::$tableReady;
    }

    /**
     * Catat aktivitas user. Tidak boleh pernah menjatuhkan proses utama.
     *
     * @param string      $action      nama aktivitas (Login, Simpan Laporan, dll)
     * @param string|null $description keterangan tambahan
     * @param string|null $name        nama user (override, misal saat login gagal)
     * @param string|null $type        tipe user (override, misal 'Tamu')
     */
    public static function record(string $action, ?string $description = null, ?string $name = null, ?string $type = null): void
    {
        try {
            if (!static::createTable()) {
                return;
            }

            $userId = null;

            if ($type === null) {
                if (Auth::guard('web')->check()) {
                    $user = Auth::guard('web')->user();
                    $type = 'Admin';
                    $userId = $user->id;
                    $name = $name ?: $user->name;
                } elseif (Auth::guard('karyawan')->check()) {
                    $user = Auth::guard('karyawan')->user();
                    $type = 'AO';
                    $userId = $user->id;
                    $name = $name ?: $user->username;
                } else {
                    $type = 'Tamu';
                }
            }

            static::create([
                'user_type' => $type,
                'user_id' => $userId,
                'name' => $name,
                'action' => $action,
                'description' => $description,
                'ip_address' => request()->ip(),
                'user_agent' => (string) str((string) request()->userAgent())->limit(254),
            ]);
        } catch (\Throwable $e) {
            // Logging tidak boleh menggagalkan proses utama
            report($e);
        }
    }
}
