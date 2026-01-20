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
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $item->no_nasabah ?? 'PG.803' }}</td>
                <td>{{ $item->nama_nasabah }}</td>
                <td>{{ $item->kol ?? 'LANCAR' }}</td>
                <td>NOV 2025</td>
                <td class="p-4 border border-black">
                    <div class="icon-container">
                        <a href="#" title="Download PDF">
                            <i class="fa-solid fa-file-pdf btn-doc btn-pdf"></i>
                        </a>
                        
                        <a href="#" title="Download Word">
                            <i class="fa-solid fa-file-word btn-doc btn-word"></i>
                        </a>
                    </div>
                </td>
            </tr>
            @empty
            @endforelse
        </tbody>
    </table>
</div>