<div class="page-title">
    <h2>Dokumen</h2>
    <div class="breadcrumb">
       <a href="/user/dashboard">Dashboard > </a> <span style="color: #3b82f6;">Dokumen</span>
    </div>
</div>

    <div class="search-container">
        <div class="search-box">
            <input type="text" id="searchInput" placeholder="Pencarian..">
            <i class="fa-solid fa-xmark" style="position: absolute; right: 12px; top: 8px; font-size: 10px; color: #aaa;"></i>
        </div>
    </div>

   <table class="table-dokumen">
        <thead>
            <tr>
                <th style="width: 50px;">No</th>
                <th>Kode</th>
                <th>Nasabah</th>
                <th style="width: 50px;">KOL</th>
                <th>Bulan</th>
                <th>Tgl Kunjungan</th> <th style="width: 100px;">Option</th> </tr>
        </thead>
        <tbody id="documentTable">
            @forelse($dokumen as $index => $item)
            <tr style="text-align: center; border-bottom: 1px solid #ddd;">
                <td style="padding: 12px; border: 1px solid #333;">{{ $index + 1 }}</td>
                <td style="padding: 12px; border: 1px solid #333;">{{ $item->kode_ao }}</td>
                <td style="padding: 12px; border: 1px solid #333; text-align: left;">{{ strtoupper($item->nama_nasabah) }}</td>
                <td style="padding: 12px; border: 1px solid #333;">{{ $item->kol }}</td>
                
                {{-- KOLOM BULAN --}}
                <td style="padding: 12px; border: 1px solid #333;">
                    @php
                        try {
                            echo \Carbon\Carbon::parse($item->periode)->translatedFormat('F Y');
                        } catch (\Exception $e) {
                            echo $item->periode;
                        }
                    @endphp
                </td>

                {{-- KOLOM TANGGAL KUNJUNGAN --}}
                <td style="padding: 12px; border: 1px solid #333;">
                    {{ \Carbon\Carbon::parse($item->tgl_lapor)->translatedFormat('d M Y') }}
                </td>

                {{-- KOLOM OPTION --}}
                <td style="padding: 12px; border: 1px solid #333;">
                    <div style="display: flex; justify-content: center; gap: 12px;">
                        <a href="{{ route('export.pdf', $item->id) }}" target="_blank" title="Download PDF">
                            <i class="fa-solid fa-file-pdf" style="font-size: 20px; color: #e74c3c;"></i>
                        </a>
                        <a href="{{ route('export.word', $item->id) }}" title="Download Word">
                            <i class="fa-solid fa-file-word" style="font-size: 20px; color: #3498db;"></i>
                        </a>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" style="padding: 20px; text-align: center;">Belum ada dokumen laporan yang tersedia.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

<div style="margin-top: 20px; display: flex; justify-content: center;">
    {{ $dokumen->links('pagination::bootstrap-4') }}
</div>

</div>