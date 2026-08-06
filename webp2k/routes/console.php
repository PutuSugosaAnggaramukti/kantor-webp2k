<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('kunjungan:backfill-koordinat', function () {
    $this->call(\App\Console\Commands\BackfillKoordinatKunjungan::class);
})->purpose('Perbaiki koordinat 0,0 dari EXIF GPS foto yang sudah terupload');
