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

<div class="mb-4">
    <!-- Action mengarah ke halaman detail AO itu sendiri untuk memicu filter -->
    <form action="{{ url('admin/kunjungan-detail/' . request()->route('kode_ao')) }}" method="GET" class="d-flex align-items-center gap-2">
        
        <!-- Dropdown Pilih Bulan -->
        <select name="bulan" onchange="this.form.submit()" class="form-select form-select-sm" style="width: 130px; border-radius: 4px; border: 1px solid #28a745;">
            @foreach(range(1, 12) as $m)
                @php $mVal = sprintf('%02d', $m); @endphp
                <option value="{{ $mVal }}" {{ (request('bulan', date('m'))) == $mVal ? 'selected' : '' }}>
                    {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
                </option>
            @endforeach
        </select>

        <!-- Dropdown Pilih Tahun -->
        <select name="tahun" onchange="this.form.submit()" class="form-select form-select-sm" style="width: 90px; border-radius: 4px; border: 1px solid #28a745;">
            @foreach(range(date('Y')-2, date('Y')+1) as $y)
                <option value="{{ $y }}" {{ (request('tahun', date('Y'))) == $y ? 'selected' : '' }}>{{ $y }}</option>
            @endforeach
        </select>

        <!-- Tombol Export (Gunakan formaction agar menembak route export excel) -->
        <button type="submit" formaction="{{ route('admin.kunjungan.export') }}" class="btn btn-success btn-sm shadow-sm" 
            style="background-color: #28a745 !important; color: white !important; padding: 6px 15px; border-radius: 4px; display: inline-flex; align-items: center; border: none;">
            <input type="hidden" name="kode_ao" value="{{ request()->route('kode_ao') }}">
            <i class="fas fa-file-excel fa-sm text-white" style="margin-right: 8px !important;"></i> 
            <strong>Export Data AO</strong>
        </button>

        <!-- Tombol Download Foto (pilih format) -->
        <button type="button" onclick="pilihFormatDownloadFoto()" class="btn btn-primary btn-sm shadow-sm" 
            style="background-color: #3b82f6 !important; color: white !important; padding: 6px 15px; border-radius: 4px; display: inline-flex; align-items: center; border: none;">
            <i class="fas fa-download fa-sm text-white" style="margin-right: 8px !important;"></i> 
            <strong>Download Foto</strong>
        </button>
    </form>
</div>

<script>
function pilihFormatDownloadFoto() {
    const kodeAo = "{{ request()->route('kode_ao') }}";
    const bulan = document.querySelector('select[name="bulan"]')?.value || '';
    const tahun = document.querySelector('select[name="tahun"]')?.value || '';

    Swal.fire({
        title: 'Download Foto Kunjungan',
        html: 'Pilih format arsip foto kunjungan untuk AO ini.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: '<i class="fas fa-file-archive"></i> .tar.gz (Linux)',
        cancelButtonText: '<i class="fas fa-file-zipper"></i> .zip',
        reverseButtons: true,
        confirmButtonColor: '#3b82f6',
        cancelButtonColor: '#6c757d'
    }).then((result) => {
        if (result.isConfirmed) {
            const url = `{{ route('admin.kunjungan.download-foto', ':kode_ao') }}?format=tar.gz&bulan=${bulan}&tahun=${tahun}`.replace(':kode_ao', encodeURIComponent(kodeAo));
            window.location.href = url;
        } else if (result.dismiss === Swal.DismissReason.cancel) {
            const url = `{{ route('admin.kunjungan.download-foto', ':kode_ao') }}?format=zip&bulan=${bulan}&tahun=${tahun}`.replace(':kode_ao', encodeURIComponent(kodeAo));
            window.location.href = url;
        }
    });
}
</script>

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
                    <td style="padding: 10px; border-right: 2px solid #000;">{{ $data_detail->firstItem() + $loop->index }}</td>
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

    <div style="margin-top: 20px; display: flex; justify-content: center;">
        {{ $data_detail->links() }}
    </div>

    @include('admin.partials.modals')
</div>