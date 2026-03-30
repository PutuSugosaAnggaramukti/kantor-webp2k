<div class="page-title" style="margin-bottom: 25px;">
    <h2 style="font-size: 24px; font-weight: 800; color: #000; margin-bottom: 5px;">Dokumen</h2>
    <p style="font-size: 14px; font-weight: 600;">
        <span onclick="loadAdminPage('dashboard')" style="cursor: pointer; color: #000;" onmouseover="this.style.color='#007bff'" onmouseout="this.style.color='#000'">
            Dashboard
        </span> 
        <span style="margin: 0 5px;">></span> 
        <span style="color: #007bff;">Dokumen</span>
    </p>
</div>

<div style="display: flex; justify-content:flex-end; align-items: center; margin-bottom: 20px;">    
    <div style="position: relative;">
        <input type="text" id="searchInput" placeholder="Pencarian Nama..." style="padding: 8px 15px; border-radius: 20px; border: 1px solid #ccc; width: 250px;">
    </div>
</div>

<div class="table-responsive">
    <table style="width: 100%; border-collapse: collapse; border: 2px solid #000; background-color: #fff;">
        <thead>
            <tr style="border-bottom: 2px solid #000; text-align: center; background-color: #fcfcfc;">
                <th style="padding: 15px; border-right: 2px solid #000; width: 60px;">No</th>
                <th style="padding: 15px; border-right: 2px solid #000; width: 150px;">Tgl Master</th>
                <th style="padding: 15px; border-right: 2px solid #000; width: 150px;">No.Ang</th>
                <th style="padding: 15px; border-right: 2px solid #000;">Nama Nasabah</th>
                <th style="padding: 15px; width: 100px;">Option</th>
            </tr>
        </thead>
        <tbody id="documentTable" style="font-weight: 700; font-size: 14px;">
            @forelse($dokumen_all as $index => $item)
            <tr style="border-bottom: 2px solid #000; text-align: center; background-color: #fff;">
                {{-- Penyesuaian nomor urut agar kontinu di halaman berikutnya --}}
                <td style="padding: 15px; border-right: 2px solid #000;">
                    {{ $dokumen_all->firstItem() + $index }}
                </td>
                <td style="padding: 15px; border-right: 2px solid #000;">
                    {{ $item->created_at ? \Carbon\Carbon::parse($item->created_at)->format('d-m-Y') : '-' }}
                </td>
                <td style="padding: 15px; border-right: 2px solid #000;">{{ $item->no_angsuran }}</td>
                <td style="padding: 15px; border-right: 2px solid #000; text-align: left; padding-left: 20px; text-transform: uppercase;">
                    {{ $item->nasabah }}
                </td>
                <td style="padding: 15px; text-align: center;">
                    <a href="{{ url('/admin/download-word/' . $item->no_angsuran) }}">
                        <button type="button" style="background: none; border: none; cursor: pointer; padding: 0;">
                            <i class="fa-regular fa-file-word" style="font-size: 24px; color: #2b579a;"></i>
                        </button>
                    </a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" style="padding: 30px; text-align: center; color: #888;">
                    Data nasabah tidak ditemukan.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- Tombol Navigasi Halaman --}}
<div style="margin-top: 20px; display: flex; justify-content: center;">
    {!! $dokumen_all->links() !!}
</div>

<script>
    document.getElementById('searchInput').addEventListener('keyup', function() {
        let filter = this.value.toUpperCase();
        let rows = document.querySelector("#documentTable").rows;
        
        for (let i = 0; i < rows.length; i++) {
            let nameCol = rows[i].cells[3].textContent.toUpperCase();
            if (nameCol.indexOf(filter) > -1) {
                rows[i].style.display = "";
            } else {
                rows[i].style.display = "none";
            }      
        }
    });
</script>