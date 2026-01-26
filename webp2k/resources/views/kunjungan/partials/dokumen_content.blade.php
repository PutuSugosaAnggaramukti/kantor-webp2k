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
                <th style="width: 60px;">No</th>
                <th>Kode</th>
                <th>Nasabah</th>
                <th>KOL</th>
                <th>Bulan</th>
                <th>Kunjungan</th>
            </tr>
        </thead>
       <tbody id="documentTable">
            @forelse($dokumen as $index => $item)
            <tr style="text-align: center; border-bottom: 1px solid #ddd;">
                <td style="padding: 12px; border: 1px solid #333;">{{ $index + 1 }}</td>
                <td style="padding: 12px; border: 1px solid #333;">{{ $item->dataKunjungan->kode_ao ?? '-' }}</td>
                <td style="padding: 12px; border: 1px solid #333; text-align: left;">{{ $item->dataKunjungan->nama_nasabah }}</td>
                <td style="padding: 12px; border: 1px solid #333;">{{ $item->dataKunjungan->kol }}</td>
                <td style="padding: 12px; border: 1px solid #333;">
                    {{ \Carbon\Carbon::parse($item->dataKunjungan->bulan)->translatedFormat('M Y') }}
                </td>
                <td style="padding: 12px; border: 1px solid #333;">
                    <div style="display: flex; justify-content: center; gap: 15px;">
                        {{-- Link Download PDF --}}
                        <a href="{{ route('export.pdf', $item->id) }}" target="_blank" title="Download PDF">
                            <i class="fa-solid fa-file-pdf" style="font-size: 24px; color: #e74c3c; cursor: pointer;"></i>
                        </a>
                        
                        <a href="{{ route('export.word', $item->id) }}" title="Download Word">
                            <i class="fa-solid fa-file-word" style="font-size: 24px; color: #3498db; cursor: pointer;"></i>
                        </a>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" style="padding: 20px; text-align: center;">Belum ada dokumen laporan yang tersedia.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>