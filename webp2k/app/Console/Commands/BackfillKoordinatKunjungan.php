<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class BackfillKoordinatKunjungan extends Command
{
    protected $signature = 'kunjungan:backfill-koordinat {--dry-run : Hanya analisis, tidak menulis perubahan}';

    protected $description = 'Perbaiki koordinat 0,0 pada kunjungan dari EXIF GPS foto yang sudah terupload';

    public function handle()
    {
        $rows = DB::table('kunjungans')
            ->where(function ($q) {
                $q->whereNull('koordinat')
                    ->orWhere('koordinat', '')
                    ->orWhere('koordinat', '-')
                    ->orWhere('koordinat', 'LIKE', '0%')
                    ->orWhere('koordinat', 'LIKE', '-0%');
            })
            ->select('id', 'foto_kunjungan', 'koordinat')
            ->get();

        $this->info('Menemukan ' . $rows->count() . ' kunjungan dengan koordinat kosong/0,0.');

        $fixed = 0;
        $noFile = 0;
        $noExif = 0;
        $zeroGps = 0;
        $dryRun = $this->option('dry-run');

        foreach ($rows as $row) {
            $fotos = json_decode($row->foto_kunjungan ?? '', true);
            if (!is_array($fotos) || count($fotos) === 0) {
                if (is_string($row->foto_kunjungan) && $row->foto_kunjungan !== '') {
                    $fotos = [$row->foto_kunjungan];
                }
            }
            if (empty($fotos)) { $noFile++; continue; }

            // cek ketersediaan file
            $fileExists = false;
            foreach ($fotos as $foto) {
                if (file_exists(public_path('uploads/kunjungan/' . $foto))) { $fileExists = true; break; }
            }
            if (!$fileExists) { $noFile++; continue; }

            $koordinat = $this->extractKoordinatDariFoto($fotos);
            if ($koordinat) {
                if (!$dryRun) {
                    DB::table('kunjungans')->where('id', $row->id)->update(['koordinat' => $koordinat]);
                }
                $fixed++;
                $this->line("  #{$row->id}: {$koordinat}");
            } elseif ($this->hasZeroGps($fotos)) {
                $zeroGps++;
            } else {
                $noExif++;
            }
        }

        $this->info("Selesai.");
        $this->info("  Dapat diperbaiki      : {$fixed}");
        $this->info("  File foto tidak ada    : {$noFile}");
        $this->info("  Tidak punya EXIF GPS   : {$noExif}");
        $this->info("  EXIF GPS = 0,0         : {$zeroGps}");
        if ($dryRun) {
            $this->warn('Mode DRY-RUN: tidak ada data yang diubah.');
        }
        return 0;
    }

    private function hasZeroGps($fotos)
    {
        foreach ($fotos as $foto) {
            $path = public_path('uploads/kunjungan/' . $foto);
            if (!file_exists($path)) continue;
            $exif = @exif_read_data($path);
            if (!$exif || !isset($exif['GPSLatitude']) || !isset($exif['GPSLongitude'])) continue;
            $lat = $this->gpsDecimal($exif['GPSLatitude'], $exif['GPSLatitudeRef'] ?? 'N');
            $lon = $this->gpsDecimal($exif['GPSLongitude'], $exif['GPSLongitudeRef'] ?? 'E');
            if ($lat == 0 && $lon == 0) return true;
        }
        return false;
    }

    private function extractKoordinatDariFoto($fotos)
    {
        foreach ($fotos as $foto) {
            $path = public_path('uploads/kunjungan/' . $foto);
            if (!file_exists($path)) continue;

            $exif = @exif_read_data($path);
            if (!$exif || !isset($exif['GPSLatitude']) || !isset($exif['GPSLongitude'])) continue;

            $lat = $this->gpsDecimal($exif['GPSLatitude'], $exif['GPSLatitudeRef'] ?? 'N');
            $lon = $this->gpsDecimal($exif['GPSLongitude'], $exif['GPSLongitudeRef'] ?? 'E');

            if ($lat == 0 && $lon == 0) continue;

            return number_format($lat, 6, '.', '') . ', ' . number_format($lon, 6, '.', '');
        }
        return null;
    }

    private function gpsDecimal($exifCoord, $hemi)
    {
        if (!is_array($exifCoord)) return 0;

        $convert = function ($value) {
            if (is_float($value) || is_int($value)) return (float) $value;
            if (is_string($value) && strpos($value, '/') !== false) {
                $parts = explode('/', $value);
                $pembilang = (float) $parts[0];
                $pembagi = (float) $parts[1];
                return ($pembagi > 0) ? ($pembilang / $pembagi) : 0;
            }
            return is_numeric($value) ? (float) $value : 0;
        };

        $degrees = count($exifCoord) > 0 ? $convert($exifCoord[0]) : 0;
        $minutes = count($exifCoord) > 1 ? $convert($exifCoord[1]) : 0;
        $seconds = count($exifCoord) > 2 ? $convert($exifCoord[2]) : 0;

        $flip = ($hemi == 'S' || $hemi == 'W') ? -1 : 1;
        return $flip * ($degrees + ($minutes / 60) + ($seconds / 3600));
    }
}
