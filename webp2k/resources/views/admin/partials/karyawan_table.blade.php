<div class="page-title">
    <h2>Data Karyawan</h2>
    <div class="breadcrumb">
        <a href="/admin/dashboard">Dashboard > </a>
        <span style="color: #3b82f6;">Data Karyawan</span>
    </div>
</div>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <button onclick="openModalTambah()" style="background-color: #28a745; color: white; border: none; padding: 10px 20px; border-radius: 10px; font-weight: 700; display: flex; align-items: center; cursor: pointer;">
        <span style="font-size: 20px; margin-right: 8px;">+</span> Tambah
    </button>
    
    <div style="position: relative;">
        <input type="text" placeholder="Pencarian.." style="padding: 8px 15px; border-radius: 20px; border: 1px solid #ccc; width: 250px;">
    </div>
</div>

<div class="table-responsive">
    <table style="width: 100%; border-collapse: collapse; border: 2px solid #000; background-color: #fff;">
        <thead>
            <tr style="border-bottom: 2px solid #000; text-align: center; background-color: #fcfcfc;">
                <th style="padding: 15px; border-right: 2px solid #000; width: 60px;">No</th>
                <th style="padding: 15px; border-right: 2px solid #000; width: 120px;">Kode AO</th>
                <th style="padding: 15px; border-right: 2px solid #000;">Nama</th>
                <th style="padding: 15px; border-right: 2px solid #000; width: 120px;">Status</th>
                <th style="padding: 15px; width: 150px;">Option</th>
            </tr>
        </thead>
        <tbody style="font-weight: 700; font-size: 14px; text-align: center;">
            @foreach($karyawanData as $index => $item)
            <tr style="border-bottom: 2px solid #000;">
                <td style="padding: 15px; border-right: 2px solid #000;">{{ $index + 1 }}</td>
                <td style="padding: 15px; border-right: 2px solid #000;">{{ $item['kode'] }}</td>
                <td style="padding: 15px; border-right: 2px solid #000; text-align: left; padding-left: 20px;">{{ $item['nama'] }}</td>
                <td style="padding: 15px; border-right: 2px solid #000;">{{ $item['status'] }}</td>
                <td style="padding: 10px; display: flex; justify-content: center; gap: 15px; align-items: center;">
                    <button title="Ubah Data" class="btn-edit" onclick="openModalEdit()" style="background: none; border: none; padding: 0;">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                        </svg>
                    </button>

                    <button title="Detail" class="btn-info-circle" onclick="openModalDetail()">
                        <span style="font-family: 'Times New Roman', serif; font-size: 18px;">i</span>
                    </button>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div style="margin-top: 30px; background-color: #f2f2f2; padding: 25px; border-radius: 15px; border: 1px solid #ddd;">
    <h3 style="font-weight: 800; font-size: 18px; margin-bottom: 20px; color: #000;">Petunjuk !</h3>
    
    <div style="display: flex; align-items: center; margin-bottom: 20px;">
        <div style="width: 45px; height: 45px; background: #eee; border: 2px solid #000; border-radius: 8px; display: flex; align-items: center; justify-content: center; margin-right: 20px;">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
            </svg>
        </div>
        <p style="font-weight: 700; font-size: 15px; margin: 0; color: #333;">
            Klik tombol tersebut pada tabel untuk mengubah data karyawan
        </p>
    </div>

    <div style="display: flex; align-items: center;">
        <div style="width: 45px; display: flex; justify-content: center; margin-right: 20px;">
            <div style="width: 32px; height: 32px; border: 2.5px solid #000; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 900; font-family: 'Times New Roman', serif; font-size: 18px;">
                i
            </div>
        </div>
        <p style="font-weight: 700; font-size: 15px; margin: 0; color: #333;">
            Klik tombol tersebut pada tabel untuk melihat detail informasi data karyawan
        </p>
    </div>
</div>