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
                <th style="padding: 10px; border-right: 2px solid #000;">Catatan Kunjungan</th>
                <th style="padding: 10px; border-right: 2px solid #000;">Janji Bayar</th>
                <th style="padding: 10px; border-right: 2px solid #000;">Nominal Sanggup</th>
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
                    <td style="padding: 10px; border-right: 2px solid #000; text-align: left; font-size: 12px;">
                        {{ $item->alamat_master ?? ($item->alamat_rencana ?? 'Alamat Tidak Ditemukan') }}
                    </td>
                    
                    <td style="padding: 10px; border-right: 2px solid #000; text-align: left; font-size: 12px;">
                        {{ $item->catatan ?? '-' }}
                    </td>
                    <td style="padding: 10px; border-right: 2px solid #000;">
                        {{ $item->tgl_janji_bayar ? \Carbon\Carbon::parse($item->tgl_janji_bayar)->format('d-m-Y') : '-' }}
                    </td>
                    <td style="padding: 10px; border-right: 2px solid #000; text-align: right;">
                        {{ $item->nominal_janji_bayar > 0 ? 'Rp ' . number_format($item->nominal_janji_bayar, 0, ',', '.') : '-' }}
                    </td>

                    <td style="padding: 10px; border-right: 2px solid #000;">
                        <button type="button" 
                                onclick='showVisitDetail(@json($item))' 
                                class="btn-view">
                            <i class="fas fa-eye"></i>
                        </button>
                    </td>
                </tr>
            @empty
                <tr><td colspan="9" style="padding: 20px; text-align: center;">Data kunjungan tidak ditemukan.</td></tr>
            @endforelse
        </tbody>
    </table>

    @include('admin.partials.modals')
</div>