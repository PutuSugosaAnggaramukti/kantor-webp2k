<style>
    /* Gunakan !important untuk mengalahkan class 'active' 
       yang dihapus oleh JavaScript loadPage
    */
    #menu-laporan {
        background-color: #ffffff !important; /* Warna putih agar kontras dengan sidebar biru */
        color: #4e4bc1 !important;           /* Warna teks biru gelap khas menu aktif */
        font-weight: bold !important;
        border-radius: 10px 0 0 10px !important;
        box-shadow: -2px 0 10px rgba(0,0,0,0.1) !important;
    }

    #menu-laporan i {
        color: #4e4bc1 !important;
    }

    /* Hilangkan status active dari menu lain agar tidak ada 2 menu yang menyala */
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
                <img src="{{ asset('storage/' . $detail->foto_kunjungan) }}" alt="Foto Rumah" style="width: 100%; display: block;">
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
        <h3 style="font-size: 22px; font-weight: 700; margin-bottom: 10px;">Catatan</h3>
        <div style="width: 100%; min-height: 150px; border: 1.5px solid #333; border-radius: 10px; padding: 15px; background-color: #fff;">
            <p style="margin: 0; color: #333; font-weight: 500;">
                {{ $detail->catatan ?? 'Tidak ada catatan kunjungan.' }}
            </p>
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