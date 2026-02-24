<div class="page-title" style="margin-bottom: 25px;">
    <h2 style="font-size: 24px; font-weight: 800; color: #000; margin-bottom: 5px;">Detail Kunjungan</h2>
    <p style="font-size: 14px; font-weight: 600;">
        <span onclick="window.location.href='/admin/dashboard'" style="cursor:pointer; color:#4e4bc1;">Dashboard</span> 
        
        <span style="margin: 0 5px;">></span> 
        
        <span onclick="loadAdminPage('data-kunjungan')" style="cursor:pointer; color:#4e4bc1;">Data Kunjungan</span>
        
        <span style="margin: 0 5px;">></span> 
        
        <span style="color: #007bff;">Detail Kunjungan</span>
    </p>
</div>

<div class="table-responsive">
   <table style="width: 100%; border-collapse: collapse; border: 2px solid #000;">
        <thead>
            <tr style="background: #f0f0f0; border-bottom: 2px solid #000;">
                <th style="padding: 10px; border-right: 2px solid #000;">No</th>
                <th style="padding: 10px; border-right: 2px solid #000;">Tanggal</th>
                <th style="padding: 10px; border-right: 2px solid #000;">No. Angsuran</th>
                <th style="padding: 10px; border-right: 2px solid #000;">Nama Nasabah</th>
                <th style="padding: 10px; border-right: 2px solid #000;">Alamat</th>
                <th style="padding: 10px; border-right: 2px solid #000;">Hasil</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data_detail as $item)
                <tr style="border-bottom: 2px solid #000; text-align: center;">
                    <td style="padding: 10px; border-right: 2px solid #000;">{{ $loop->iteration }}</td>
                    <td style="padding: 10px; border-right: 2px solid #000;">
                        {{ \Carbon\Carbon::parse($item->created_at)->format('d-m-Y') }}
                    </td>
                    <td style="padding: 10px; border-right: 2px solid #000;">
                        <b>{{ $item->no_nasabah }}</b>
                    </td>
                    <td style="padding: 10px; border-right: 2px solid #000; text-align: left;">
                        {{ strtoupper($item->nama_nasabah) }}
                    </td>
                    <td style="padding: 10px; border-right: 2px solid #000; text-align: left;">
                        {{ $item->alamat_master ?? ($item->alamat_rencana ?? 'Alamat Tidak Ditemukan') }}
                    </td>
                    <td style="padding: 10px; border-right: 2px solid #000;">
                        <button type="button" 
                                onclick="showVisitDetail({{ json_encode($item) }})"
                                style="background: #3498db; color: white; border: none; padding: 5px 10px; border-radius: 5px; cursor: pointer;">
                            <i class="fa-solid fa-eye"></i>
                        </button>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" style="padding: 20px;">Data kunjungan tidak ditemukan.</td></tr>
            @endforelse
        </tbody>
    </table>

    {{-- Sertakan file modal di sini --}}
    @include('admin.partials.modals')
</div>

<script>
    // Gunakan window.namaFungsi agar bisa dipanggil dari atribut onclick
    window.showVisitDetail = function(data) {
        if (!data) return;

        // Isi Foto
        const fotoPath = data.foto_kunjungan 
            ? `/uploads/kunjungan/${data.foto_kunjungan}` 
            : '/assets/no-image.png';
        document.getElementById('view-foto').src = fotoPath;

        // Isi Status dan Janji Bayar
        document.getElementById('view-lokasi').innerText = data.ada_di_lokasi || '-';
        document.getElementById('view-janji').innerText = data.tgl_janji_bayar || 'Tidak Ada Janji';

        // Isi Catatan
        document.getElementById('view-catatan').innerText = data.catatan || 'Tidak ada catatan kunjungan.';

        // Tampilkan Modal
        const modal = document.getElementById('modalDetailKunjungan');
        if (modal) {
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }
    };

    window.closeVisitDetail = function() {
        const modal = document.getElementById('modalDetailKunjungan');
        if (modal) {
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
        }
    };
</script>