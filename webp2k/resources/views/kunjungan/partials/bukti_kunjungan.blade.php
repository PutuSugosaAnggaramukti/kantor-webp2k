@php
    /**
     * 1. DEFINISI FUNGSI HELPER
     */
    if (!function_exists('getGpsDecimal')) {
        function getGpsDecimal($exifCoord, $hemi) {
            $degrees = count($exifCoord) > 0 ? (is_float($exifCoord[0]) ? $exifCoord[0] : eval("return {$exifCoord[0]};")) : 0;
            $minutes = count($exifCoord) > 1 ? (is_float($exifCoord[1]) ? $exifCoord[1] : eval("return {$exifCoord[1]};")) : 0;
            $seconds = count($exifCoord) > 2 ? (is_float($exifCoord[2]) ? $exifCoord[2] : eval("return {$exifCoord[2]};")) : 0;
            $flip = ($hemi == 'S' || $hemi == 'W') ? -1 : 1;
            return $flip * ($degrees + ($minutes / 60) + ($seconds / 3600));
        }
    }

    /**
     * 2. LOGIKA PENGOLAHAN DATA FOTO
     */
    $fotos = json_decode($detail->foto_kunjungan);
    
    // Pastikan $fotos selalu dalam bentuk array
    if (!is_array($fotos)) {
        $fotos = $detail->foto_kunjungan ? [$detail->foto_kunjungan] : [];
    }
    
    // Ambil foto pertama untuk EXIF
    $namaFotoUtama = count($fotos) > 0 ? $fotos[0] : null;
    
    $pathFoto = $namaFotoUtama ? public_path('uploads/kunjungan/' . $namaFotoUtama) : null;
    $waktuFoto = null;
    $isOldPhoto = false;
    $koordinatExif = null;

    if ($pathFoto && file_exists($pathFoto)) {
        $exif = @exif_read_data($pathFoto);
        if ($exif) {
            $dateTag = $exif['DateTimeOriginal'] ?? $exif['DateTime'] ?? $exif['FileDateTime'] ?? null;
            if ($dateTag) {
                $waktuFoto = \Carbon\Carbon::parse($dateTag);
                $waktuUpload = \Carbon\Carbon::parse($detail->created_at);
                if ($waktuFoto->diffInHours($waktuUpload) > 2) { 
                    $isOldPhoto = true; 
                }
            }
            if (isset($exif['GPSLatitude']) && isset($exif['GPSLongitude'])) {
                $lat = getGpsDecimal($exif['GPSLatitude'], $exif['GPSLatitudeRef']);
                $lon = getGpsDecimal($exif['GPSLongitude'], $exif['GPSLongitudeRef']);
                $koordinatExif = $lat . ',' . $lon;
            }
        }
    }

    /**
     * 3. LOGIKA EKSTRAKSI NOMINAL
     */
    $nominalTampil = $detail->nominal_janji_bayar;
    if ($nominalTampil <= 0 && !empty($detail->catatan)) {
        preg_match_all('/\d+([\.]\d+)*/', $detail->catatan, $matches);
        if (!empty($matches[0])) {
            foreach ($matches[0] as $match) {
                $cleanNumber = str_replace('.', '', $match);
                if (is_numeric($cleanNumber) && $cleanNumber >= 10000) {
                    $nominalTampil = $cleanNumber;
                    break;
                }
            }
        }
    }
@endphp

<style>
    .detail-section {
        background: #f8f9fa; /* Background abu-abu lembut */
        border: 1px solid #e9ecef;
        border-radius: 15px;
        padding: 20px;
        margin-bottom: 20px;
    }
    .section-label {
        display: block;
        font-size: 13px;
        color: #6c757d;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        font-weight: 700;
        margin-bottom: 12px;
    }
    .section-value {
        font-size: 18px;
        font-weight: 700;
        color: #2d3436;
        margin: 0;
    }
</style>

<div class="page-title">
    <h2>Detail Bukti</h2>
    <div class="breadcrumb">
       <a href="javascript:void(0)" onclick="loadPage('dashboard')">Dashboard > </a> 
       <a href="javascript:void(0)" onclick="loadPage('laporan-kunjungan')">Laporan Kunjungan > </a> 
       <span style="color: #3b82f6; font-weight: 600;">Detail Bukti</span>
    </div>
</div>

<div class="main-card" style="background: white; border-radius: 20px; padding: 30px; margin-top: 20px; box-shadow: 0 10px 25px rgba(0,0,0,0.05);">
    
    <div style="display: grid; grid-template-columns: 350px 1fr; gap: 30px;">
        
       {{-- SISI KIRI: FOTO --}}
        <div>
            <div class="detail-section" style="text-align: center; padding: 15px;">
                <span class="section-label">Foto Kunjungan ({{ count($fotos) }})</span>
                
                {{-- Gunakan Grid agar jika ada 4 foto, tampilannya rapi --}}
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 10px;">
                    @forelse($fotos as $foto)
                        <div style="border-radius: 12px; overflow: hidden; border: 3px solid white; box-shadow: 0 4px 10px rgba(0,0,0,0.1);">
                            <a href="{{ asset('uploads/kunjungan/' . $foto) }}" target="_blank">
                                <img src="{{ asset('uploads/kunjungan/' . $foto) }}" 
                                    style="width: 100%; height: 180px; object-fit: cover; display: block;"
                                    onerror="this.src='https://placehold.co/400x600?text=Foto+Tidak+Ditemukan'">
                            </a>
                        </div>
                    @empty
                        <div style="padding: 60px 20px; background: #eee; color: #999; border-radius: 12px;">
                            <i class="fa-solid fa-image" style="font-size: 40px; margin-bottom: 10px;"></i><br>
                            <span style="font-size: 14px;">Foto tidak tersedia</span>
                        </div>
                    @endforelse
                </div>
                
                <small style="display: block; margin-top: 15px; color: #888;">
                    <i class="fa-solid fa-magnifying-glass-plus"></i> Klik foto untuk memperbesar
                </small>
            </div>
        </div>

        {{-- SISI KANAN: DATA --}}
        <div>
            {{-- 1. Verifikasi Waktu --}}
            <div class="detail-section" style="border-left: 5px solid {{ $isOldPhoto ? '#ff7675' : '#55efc4' }};">
                <span class="section-label"><i class="fa-solid fa-shield-check"></i> Verifikasi Waktu Kunjungan</span>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div>
                        <small style="color: #888; font-size: 11px;">Laporan Dikirim:</small><br>
                        <strong style="font-size: 16px;">{{ \Carbon\Carbon::parse($detail->created_at)->translatedFormat('d M Y, H:i') }}</strong>
                    </div>
                    <div style="border-left: 1px solid #dee2e6; padding-left: 20px;">
                        <small style="color: #888; font-size: 11px;">Foto Diambil:</small><br>
                        <strong style="font-size: 16px; color: {{ $isOldPhoto ? '#d63031' : '#2d3436' }};">
                            {{ $waktuFoto ? $waktuFoto->translatedFormat('d M Y, H:i') : 'Metadata Tidak Ditemukan' }}
                        </strong>
                    </div>
                </div>
                @if($isOldPhoto)
                    <div style="margin-top: 15px; padding: 8px 12px; background: #fff5f5; border-radius: 6px; color: #d63031; font-size: 12px; font-weight: 700;">
                        <i class="fa-solid fa-circle-exclamation"></i> Peringatan: Foto diambil jauh sebelum laporan dikirim!
                    </div>
                @endif
            </div>

           {{-- 2. Koordinat --}}
            <div class="detail-section">
                <span class="section-label"><i class="fa-solid fa-location-dot"></i> Koordinat Lokasi</span>
                
                {{-- Gabungkan koordinat dari EXIF atau Database --}}
                @php 
                    $fixCoord = $koordinatExif ?? $detail->koordinat; 
                @endphp

                @if($fixCoord && $fixCoord !== '-')
                    <p class="section-value">
                        <a href="https://www.google.com/maps/search/?api=1&query={{ $fixCoord }}" 
                        target="_blank" 
                        title="Klik untuk buka di Google Maps"
                        style="color: #4e4bc1; text-decoration: none; border-bottom: 2px dashed #4e4bc1; transition: 0.2s;">
                        {{ $fixCoord }} 
                        <i class="fa-solid fa-up-right-from-square" style="font-size: 14px; margin-left: 5px;"></i>
                        </a>
                    </p>
                    
                    @if($koordinatExif)
                        <small style="color: #27ae60; font-weight: 600;">
                            <i class="fa-solid fa-circle-check"></i> Koordinat akurat terdeteksi dari foto.
                        </small>
                    @else
                        <small style="color: #f39c12; font-weight: 600;">
                            <i class="fa-solid fa-triangle-exclamation"></i> Menggunakan koordinat sistem aplikasi.
                        </small>
                    @endif
                @else
                    <p class="section-value">-</p>
                    <small style="color: #e74c3c;">Koordinat tidak tersedia.</small>
                @endif
            </div>

            {{-- 3. Catatan --}}
            <div class="detail-section">
                <span class="section-label"><i class="fa-solid fa-pen-to-square"></i> Catatan Petugas</span>
                <div style="background: white; padding: 15px; border-radius: 10px; border: 1px solid #e9ecef; color: #444; line-height: 1.6; font-size: 15px;">
                    {!! nl2br(e($detail->catatan ?? 'Tidak ada catatan kunjungan.')) !!}
                </div>
            </div>

           {{-- 4. Status Janji Bayar (Hanya Muncul Jika Ada Tanggal Janji) --}}
                @if($detail->tgl_janji_bayar)
                <div class="detail-section" style="background: #fff9db; border-color: #ffe066;">
                    <span class="section-label" style="color: #856404;"><i class="fa-solid fa-calendar-check"></i> Janji Bayar Nasabah</span>
                    <p class="section-value" style="color: #e67e22; margin-bottom: 5px;">
                        {{ \Carbon\Carbon::parse($detail->tgl_janji_bayar)->translatedFormat('d F Y') }}
                    </p>

                    {{-- Tampilkan Nominal Kesanggupan (Hasil Olahan Logika di Atas) --}}
                    <div style="border-top: 1px dashed #ffe066; margin-top: 10px; padding-top: 8px;">
                        <span class="section-label" style="color: #856404; font-size: 11px;">Nominal Kesanggupan</span>
                        <p class="section-value" style="color: #d32f2f; font-size: 18px; font-weight: 900; margin: 0;">
                            {{-- GUNAKAN VARIABEL $nominalTampil DI SINI --}}
                            @if($nominalTampil > 0)
                                Rp {{ number_format($nominalTampil, 0, ',', '.') }}
                            @else
                                <span style="font-size: 14px; color: #9e7e1a; font-weight: 400;">(Nominal tidak disebutkan)</span>
                            @endif
                        </p>
                    </div>
                </div>
                @endif

    {{-- Tombol Kembali --}}
    <div style="margin-top: 20px; border-top: 1px solid #eee; padding-top: 25px;">
        <button onclick="loadPage('laporan-kunjungan')" 
                style="padding: 12px 30px; background-color: #4e4bc1; color: white; border-radius: 10px; font-weight: 700; border: none; cursor: pointer; transition: 0.2s;">
            <i class="fa-solid fa-chevron-left"></i> KEMBALI KE LAPORAN
        </button>
    </div>
</div>