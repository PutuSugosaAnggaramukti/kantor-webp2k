<style>
    #menu-laporan {
        background-color: #ffffff !important; 
        color: #4e4bc1 !important;           
        font-weight: bold !important;
        border-radius: 10px 0 0 10px !important;
        box-shadow: -2px 0 10px rgba(0,0,0,0.1) !important;
    }

    #menu-laporan i {
        color: #4e4bc1 !important;
    }

    .nav-item:not(#menu-laporan) {
        background-color: transparent !important;
        color: #ffffff !important;
    }
</style>


<div class="page-title">
    <h2>Dokumen</h2>
    <div class="breadcrumb">
       <a href="javascript:void(0)" onclick="loadPage('dashboard')">Dashboard > </a> 
       <a href="javascript:void(0)" onclick="loadPage('laporan-kunjungan')">Laporan Kunjungan > </a> 
       <span style="color: #3b82f6;">Bukti Kunjungan</span>
    </div>
</div>

<div class="main-card" style="background: white; border-radius: 20px; padding: 30px; margin-top: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
    
    {{-- Foto Kunjungan --}}
    <div class="content-section" style="margin-bottom: 30px;">
        <h3 style="font-size: 22px; font-weight: 700; margin-bottom: 15px;">Foto Kunjungan</h3>
        <div class="photo-container" style="width: 100%; max-width: 400px; border-radius: 15px; overflow: hidden; border: 1px solid #ddd;">
            @if($detail->foto_kunjungan)
               <img src="{{ asset('uploads/kunjungan/' . $detail->foto_kunjungan) }}" alt="Foto Rumah" style="width: 100%; display: block;">
            @else
                <div style="padding: 40px; text-align: center; background: #f9f9f9; color: #999;">
                    <i class="fa-solid fa-image" style="font-size: 40px; margin-bottom: 10px;"></i>
                    <p>Tidak ada foto terlampir</p>
                </div>
            @endif
        </div>
    </div>

    {{-- Koordinat --}}
    <div class="content-section" style="margin-bottom: 30px;">
        <h3 style="font-size: 22px; font-weight: 700; margin-bottom: 10px;">Koordinat Lokasi</h3>
        <p style="font-size: 20px; font-weight: 700; color: #000;">
            {{ $detail->koordinat ?? 'Koordinat tidak tersedia' }}
        </p>
        @if($detail->koordinat)
        <a href="https://www.google.com/maps/search/?api=1&query={{ $detail->koordinat }}" 
            target="_blank" 
            style="color: #3b82f6; text-decoration: none; font-size: 14px; font-weight: 600;">
             <i class="fa-solid fa-location-dot"></i> Lihat Lokasi di Google Maps
         </a>
        @endif
    </div>

   {{-- Catatan --}}
    <div class="content-section">
        <h3 style="font-size: 22px; font-weight: 700; margin-bottom: 15px;">Catatan Kunjungan</h3>
        <div style="width: 100%; min-height: 150px; border: 1.5px solid #333; border-radius: 15px; padding: 20px; background-color: #fff; position: relative;">
            
            {{-- Teks Catatan Utama --}}
            <p style="margin: 0; color: #333; font-weight: 500; line-height: 1.6; font-size: 16px;">
                {!! nl2br(e($detail->catatan ?? 'Tidak ada catatan kunjungan.')) !!}
            </p>

            {{-- Label Janji Bayar Khusus (Jika field tanggal diisi) --}}
            @if($detail->tgl_janji_bayar)
                <div style="margin-top: 20px; padding: 12px 15px; background-color: #fff9f0; border-left: 4px solid #e67e22; border-radius: 5px;">
                    <span style="display: block; font-size: 12px; color: #7f8c8d; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 5px;">
                        <i class="fa-solid fa-clock"></i> Status Kesanggupan
                    </span>
                    <p style="margin: 0; color: #2c3e50; font-weight: 700; font-size: 15px;">
                        Nasabah menyanggupi akan membayar pada tanggal: 
                        <span style="color: #e67e22;">{{ \Carbon\Carbon::parse($detail->tgl_janji_bayar)->translatedFormat('d F Y') }}</span>
                    </p>
                </div>
            @endif
        </div>
    </div>

    {{-- Tombol Kembali --}}
    <div style="margin-top: 40px;">
        <button onclick="loadPage('laporan-kunjungan')" 
                style="padding: 12px 25px; background-color: #4e4bc1; color: white; border-radius: 10px; font-weight: 600; border: none; cursor: pointer;">
            <i class="fa-solid fa-arrow-left"></i> Kembali ke Laporan
        </button>
    </div>
</div>

<script>
    // Opsional: Tetap jalankan JS untuk memastikan sinkronisasi DOM
    (function() {
        const menu = document.getElementById('menu-laporan');
        if (menu) menu.classList.add('active');
    })();
</script>

<script>
    (function() {
        /**
         * Fungsi untuk memaksa status active pada sidebar
         */
        function forceSidebarActive() {
            const menuLaporan = document.getElementById('menu-laporan');
            
            if (menuLaporan) {
                // 1. Bersihkan semua class active dari nav-item lain
                document.querySelectorAll('.nav-item').forEach(nav => {
                    nav.classList.remove('active');
                });

                // 2. Tambahkan class active ke Laporan Kunjungan
                menuLaporan.classList.add('active');

                // 3. Jika template kamu menggunakan parent (li), aktifkan juga
                const parentLi = menuLaporan.closest('li');
                if (parentLi) {
                    parentLi.classList.add('active');
                }
            }
        }

        // Jalankan 3x untuk memastikan tidak tertimpa proses AJAX lain
        forceSidebarActive();
        setTimeout(forceSidebarActive, 100);
        setTimeout(forceSidebarActive, 500);
    })();
</script>