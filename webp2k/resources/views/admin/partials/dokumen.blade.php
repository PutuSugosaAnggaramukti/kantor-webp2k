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
        <input type="text" placeholder="Pencarian..." style="padding: 8px 15px; border-radius: 20px; border: 1px solid #ccc; width: 250px;">
    </div>
</div>

<div class="table-responsive">
    <table style="width: 100%; border-collapse: collapse; border: 2px solid #000; background-color: #fff;">
        <thead>
            <tr style="border-bottom: 2px solid #000; text-align: center; background-color: #fcfcfc;">
                <th style="padding: 15px; border-right: 2px solid #000; width: 60px;">No</th>
                <th style="padding: 15px; border-right: 2px solid #000; width: 150px;">Tanggal</th>
                <th style="padding: 15px; border-right: 2px solid #000; width: 150px;">No.Ang</th>
                <th style="padding: 15px; border-right: 2px solid #000;">Nama</th>
                <th style="padding: 15px; width: 100px;">Option</th>
            </tr>
        </thead>
        <tbody style="font-weight: 700; font-size: 14px;">
            {{-- Menggunakan variabel asli dari Controller --}}
            @forelse($dokumen_all as $index => $item)
            <tr style="border-bottom: 2px solid #000; text-align: center; background-color: #fff;">
                <td style="padding: 15px; border-right: 2px solid #000;">{{ $index + 1 }}</td>
                <td style="padding: 15px; border-right: 2px solid #000;">
                    {{ $item->tanggal ? \Carbon\Carbon::parse($item->tanggal)->format('d-m-Y') : '-' }}
                </td>
                <td style="padding: 15px; border-right: 2px solid #000;">{{ $item->no_angsuran }}</td>
                <td style="padding: 15px; border-right: 2px solid #000; text-align: left; padding-left: 20px; text-transform: uppercase;">
                    {{ $item->nama_nasabah }}
                </td>
               <td style="padding: 15px; text-align: center;">
                    <a href="{{ route('download.docx', $item->id) }}" 
                    target="_blank" 
                    onclick="event.stopPropagation();"
                    download>
                        <button type="button" style="background: none; border: none; cursor: pointer; padding: 0;">
                            <i class="fa-regular fa-file-word" style="font-size: 24px; color: #2b579a;"></i>
                        </button>
                    </a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" style="padding: 30px; text-align: center; color: #888;">
                    Data kunjungan tidak ditemukan.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>