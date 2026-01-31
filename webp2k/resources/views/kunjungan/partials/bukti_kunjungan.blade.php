@php
    $pathFoto = public_path('uploads/kunjungan/' . $detail->foto_kunjungan);
    $waktuFoto = null;
    $isOldPhoto = false;

    if (!empty($detail->foto_kunjungan) && file_exists($pathFoto)) {
        // Gunakan @ untuk meredam error jika file bukan JPEG murni
        $exif = @exif_read_data($pathFoto);
        
        // 1. Ambil Waktu (Coba beberapa tag sekaligus)
        $dateTag = $exif['DateTimeOriginal'] ?? $exif['DateTime'] ?? $exif['FileDateTime'] ?? null;
        
        if ($dateTag) {
            $waktuFoto = \Carbon\Carbon::parse($dateTag);
            $waktuUpload = \Carbon\Carbon::parse($detail->created_at);
            if ($waktuFoto->diffInHours($waktuUpload) > 2) {
                $isOldPhoto = true;
            }
        } else {
            // Jika benar-benar kosong, ambil waktu file sistem
            $waktuFoto = \Carbon\Carbon::createFromTimestamp(filemtime($pathFoto));
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
                <span class="section-label">Foto Kunjungan</span>
                <div style="border-radius: 12px; overflow: hidden; border: 4px solid white; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
                    @if($detail->foto_kunjungan)
                       <img src="{{ asset('uploads/kunjungan/' . $detail->foto_kunjungan) }}" style="width: 100%; display: block;">
                    @else
                        <div style="padding: 60px 20px; background: #eee; color: #999;">
                            <i class="fa-solid fa-image" style="font-size: 40px; margin-bottom: 10px;"></i><br>
                            <span style="font-size: 14px;">Foto tidak tersedia</span>
                        </div>
                    @endif
                </div>
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
                <p class="section-value">{{ $detail->koordinat ?? '-' }}</p>
                @if($detail->koordinat)
                    <a href="https://www.google.com/maps/search/?api=1&query={{ $detail->koordinat }}" target="_blank" 
                       style="display: inline-block; margin-top: 10px; color: #4e4bc1; text-decoration: none; font-weight: 700; font-size: 13px;">
                        LIHAT DI GOOGLE MAPS <i class="fa-solid fa-arrow-up-right-from-square"></i>
                    </a>
                @endif
            </div>

            {{-- 3. Catatan --}}
            <div class="detail-section">
                <span class="section-label"><i class="fa-solid fa-pen-to-square"></i> Catatan Petugas</span>
                <div style="background: white; padding: 15px; border-radius: 10px; border: 1px solid #e9ecef; color: #444; line-height: 1.6; font-size: 15px;">
                    {!! nl2br(e($detail->catatan ?? 'Tidak ada catatan kunjungan.')) !!}
                </div>
            </div>

            {{-- 4. Status Janji Bayar (Hanya Muncul Jika Ada) --}}
            @if($detail->tgl_janji_bayar)
            <div class="detail-section" style="background: #fff9db; border-color: #ffe066;">
                <span class="section-label" style="color: #856404;"><i class="fa-solid fa-calendar-check"></i> Janji Bayar Nasabah</span>
                <p class="section-value" style="color: #e67e22;">
                    {{ \Carbon\Carbon::parse($detail->tgl_janji_bayar)->translatedFormat('d F Y') }}
                </p>
            </div>
            @endif
        </div>
    </div>

    {{-- Tombol Kembali --}}
    <div style="margin-top: 20px; border-top: 1px solid #eee; padding-top: 25px;">
        <button onclick="loadPage('laporan-kunjungan')" 
                style="padding: 12px 30px; background-color: #4e4bc1; color: white; border-radius: 10px; font-weight: 700; border: none; cursor: pointer; transition: 0.2s;">
            <i class="fa-solid fa-chevron-left"></i> KEMBALI KE LAPORAN
        </button>
    </div>
</div>