<div class="page-title">
    <h2>Bukti Kunjungan</h2>
    <div class="breadcrumb">
        Dashboard > Laporan Kunjungan > <span style="color: #3b82f6;">Bukti Kunjungan</span>
    </div>
</div>

<div class="main-card" style="background: white; border-radius: 20px; padding: 30px; margin-top: 20px;">
    
    <div class="content-section" style="margin-bottom: 30px;">
        <h3 style="font-size: 22px; font-weight: 700; margin-bottom: 15px;">Foto Kunjungan</h3>
        <div class="photo-container" style="width: 100%; max-width: 400px; border-radius: 15px; overflow: hidden; border: 1px solid #ddd;">
            <img src="{{ asset('images/dummy-house.jpg') }}" alt="Foto Rumah" style="width: 100%; display: block;">
        </div>
    </div>

    <div class="content-section" style="margin-bottom: 30px;">
        <h3 style="font-size: 22px; font-weight: 700; margin-bottom: 10px;">Koordinat Lokasi</h3>
        <p style="font-size: 20px; font-weight: 700; color: #000;">
            -7.888581, 110.3239571, 17
        </p>
    </div>

    <div class="content-section">
        <h3 style="font-size: 22px; font-weight: 700; margin-bottom: 10px;">Catatan</h3>
        <div style="width: 100%; min-height: 150px; border: 1.5px solid #333; border-radius: 10px; padding: 15px; background-color: #fff;">
            <p style="margin: 0; color: #333; font-weight: 500;">
                Nasabah kooperatif dan berjanji akan melakukan pembayaran angsuran pada tanggal 10 bulan depan.
            </p>
        </div>
    </div>

    <div style="margin-top: 40px;">
    <button onclick="loadContent('{{ route('laporan.kunjungan') }}')" 
            style="padding: 12px 25px; background-color: #4e4bc1; color: white; border-radius: 10px; font-weight: 600; border: none; cursor: pointer;">
        <i class="fa-solid fa-arrow-left"></i> Kembali ke Laporan
    </button>
    </div>
</div>