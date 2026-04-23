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

<div class="mb-3">
    <a href="{{ route('admin.export.ao', $kode_ao) }}" 
    class="btn btn-success btn-sm shadow-sm" 
    style="background-color: #28a745 !important; color: white !important; padding: 6px 15px; text-decoration: none; border-radius: 4px; display: inline-flex; align-items: center;">
        
        <i class="fas fa-file-excel fa-sm text-white" style="margin-right: 12px !important;"></i> 
        
        <strong>Export Semua Data AO</strong>
    </a>
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
                <th style="padding: 10px; border-right: 2px solid #000;">Status</th>
                <th style="padding: 10px;">Hasil</th>
            </tr>
        </thead>
                <tbody>
            @forelse($data_detail as $item)
                <tr style="border-bottom: 2px solid #000; text-align: center;">
                    <td style="padding: 10px; border-right: 2px solid #000;">{{ $loop->iteration }}</td>
                    <td style="padding: 10px; border-right: 2px solid #000;">
                        {{-- Prioritas tgl realisasi (dari AO), fallback ke tgl rencana (dari Admin) --}}
                        {{ \Carbon\Carbon::parse($item->tgl_realisasi ?? ($item->tanggal ?? $item->created_at))->format('d-m-Y') }}
                    </td>
                    <td style="padding: 10px; border-right: 2px solid #000;">
                        <b>{{ $item->no_angsuran }}</b>
                    </td>
                    <td style="padding: 10px; border-right: 2px solid #000; text-align: left;">
                        {{ strtoupper($item->nama_nasabah) }}
                    </td>
                    <td style="padding: 10px; border-right: 2px solid #000; text-align: left; font-size: 11px;">
                        {{ $item->alamat_master ?? ($item->alamat_nasabah ?? 'Alamat Tidak Ditemukan') }}
                    </td>
                    
                    <td style="padding: 10px; border-right: 2px solid #000; text-align: left; font-size: 11px;">
                        {{-- Ambil catatan lapangan AO, jika kosong baru tampilkan catatan rencana admin --}}
                        {{ $item->catatan_lapangan ?? ($item->catatan ?? '-') }}
                    </td>

                    <td style="padding: 10px; border-right: 2px solid #000;">
                        {{-- Ambil tgl janji dari hasil AO, fallback ke data rencana --}}
                        @php $tgl = $item->tgl_janji_hasil ?? ($item->tgl_janji_bayar ?? null); @endphp
                        {{ $tgl ? \Carbon\Carbon::parse($tgl)->format('d-m-Y') : '-' }}
                    </td>

                    <td style="padding: 10px; border-right: 2px solid #000; text-align: right;">
                        {{-- Ambil nominal dari hasil AO, fallback ke data rencana --}}
                        @php $nominal = $item->nominal_janji_hasil ?? ($item->nominal_janji_bayar ?? 0); @endphp
                        {{ $nominal > 0 ? 'Rp ' . number_format($nominal, 0, ',', '.') : '-' }}
                    </td>

                    <td style="padding: 10px; border-right: 2px solid #000;">
                        @if($item->id_kunjungan)
                            <select class="status-select" 
                                    data-id="{{ $item->id_kunjungan }}"
                                    data-kode-ao="{{ $kode_ao }}"
                                    style="padding: 5px; border-radius: 5px; font-weight: bold; font-size: 11px; cursor: pointer;
                                    {{ $item->status_kunjungan == 'Sudah Bayar' ? 'background-color: #d4edda; color: #155724;' : '' }}
                                    {{ $item->status_kunjungan == 'Gagal Bayar' ? 'background-color: #f8d7da; color: #721c24;' : '' }}
                                    {{ $item->status_kunjungan == 'Menunggu Pembayaran' ? 'background-color: #fff3cd; color: #856404;' : '' }}">
                                <option value="Menunggu Pembayaran" {{ $item->status_kunjungan == 'Menunggu Pembayaran' ? 'selected' : '' }}>⏳ Menunggu</option>
                                <option value="Sudah Bayar" {{ $item->status_kunjungan == 'Sudah Bayar' ? 'selected' : '' }}>✅ Sudah Bayar</option>
                                <option value="Gagal Bayar" {{ $item->status_kunjungan == 'Gagal Bayar' ? 'selected' : '' }}>❌ Gagal Bayar</option>
                            </select>
                        @else
                            <span style="background-color: #e9ecef; color: #6c757d; padding: 5px 10px; border-radius: 5px; font-size: 10px; font-weight: bold; display: inline-block; border: 1px dashed #adb5bd;">
                                Belum Kunjungan
                            </span>
                        @endif
                    </td>

                    <td style="padding: 10px;">
                        @if($item->id_kunjungan)
                            <button type="button" onclick='showVisitDetail(@json($item))' class="btn-view" style="background: #007bff; color: white; border: none; padding: 5px 10px; border-radius: 5px; cursor: pointer;">
                                <i class="fas fa-eye"></i>
                            </button>
                        @else
                            <button disabled style="background: #ccc; color: #666; border: none; padding: 5px 10px; border-radius: 5px; cursor: not-allowed;">
                                <i class="fas fa-eye-slash"></i>
                            </button>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="10" style="padding: 20px; text-align: center;">Data tidak ditemukan.</td></tr>
            @endforelse
        </tbody>
    </table>

    @include('admin.partials.modals')
</div>