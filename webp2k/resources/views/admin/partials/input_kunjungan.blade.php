<div class="page-title" style="margin-bottom: 25px;">
    <h2 style="font-size: 24px; font-weight: 800; color: #000; margin-bottom: 5px;">Input Jadwal Kunjungan</h2>
    <p style="font-size: 14px; font-weight: 600;">
        <span onclick="window.location.href='/admin/dashboard'" style="cursor:pointer; color:#4e4bc1;">Dashboard</span> 
        <span style="margin: 0 5px;">></span> 
        <span style="color: #007bff;">Jadwal Kunjungan</span>
    </p>
</div>

<div style="background-color: #fff4e5; border-left: 5px solid #ffa117; padding: 15px 20px; border-radius: 8px; margin-bottom: 25px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); border: 1px solid #ffeeba;">
    <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
        <i class="fa-solid fa-circle-exclamation" style="color: #ffa117; font-size: 20px;"></i>
        <h4 style="margin: 0; color: #856404; font-weight: 800; font-size: 16px; text-transform: uppercase;">
            PENGINGAT STANDAR KPI (WAJIB)
        </h4>
    </div>
    <ul style="margin: 0; padding-left: 25px; color: #856404; font-weight: 700; font-size: 13px; line-height: 1.6;">
        <li>Minimal kuota jadwal adalah <span style="text-decoration: underline;">10 kunjungan per hari</span> untuk setiap personil AO.</li>
        <li>Wajib mencantumkan minimal satu nasabah <span style="color: #d32f2f;">KOL 5</span> sebagai target utama penilaian KPI.</li>
        <li>Akurasi koordinat dan foto akan divalidasi sistem berdasarkan jadwal ini.</li>
    </ul>
</div>

<div style="margin-bottom: 25px; display: flex; gap: 12px; flex-wrap: wrap;">
    <button onclick="openModalKunjungan()" style="background-color: #28a745; color: white; border: none; padding: 12px 25px; border-radius: 8px; font-weight: 700; cursor: pointer; display: flex; align-items: center; gap: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
        <i class="fa-solid fa-plus"></i> Tambah Jadwal Baru
    </button>

    <button type="button" class="btn-import-excel" onclick="openModalImport()">
         <i class="fa-solid fa-file-excel"></i> Import dari Excel
    </button>
</div>

@if($kunjungansGrouped->isEmpty())
    <div style="text-align: center; padding: 50px; background: #f8f9fa; border-radius: 15px; border: 2px dashed #ccc;">
        <h3 style="color: #666;">Belum ada jadwal kunjungan yang dibuat.</h3>
    </div>
@else
    @foreach($kunjungansGrouped as $kodeAo => $group)
    @php 
        $totalJadwal = $group->count();
        $hasKol5 = $group->contains('kol', 5);
        $isTargetMet = $totalJadwal >= 10;
    @endphp
    
    <div class="ao-section" style="margin-bottom: 40px;">
        <div style="background-color: #4e4bc1; color: white; padding: 15px 20px; border-radius: 12px 12px 0 0; display: flex; justify-content: space-between; align-items: center; border: 2px solid #000; border-bottom: none;">
            <h3 style="margin: 0; font-size: 18px; font-weight: 800; text-transform: uppercase;">
                <i class="fa-solid fa-user-tie"></i> {{ $group->first()->karyawan->nama ?? 'Nama AO Tidak Ditemukan' }} ({{ $kodeAo }})
            </h3>
            
            <div style="display: flex; gap: 10px; align-items: center;">
                @if($hasKol5)
                    <span style="background: #27ae60; color: white; padding: 4px 12px; border-radius: 20px; font-size: 11px; font-weight: 800; border: 1px solid white;">
                        <i class="fa-solid fa-check"></i> KOL 5 ADA
                    </span>
                @else
                    <span style="background: #d32f2f; color: white; padding: 4px 12px; border-radius: 20px; font-size: 11px; font-weight: 800; border: 1px solid white;">
                        <i class="fa-solid fa-xmark"></i> KOL 5 BELUM ADA
                    </span>
                @endif

                <span style="background: {{ $isTargetMet ? '#27ae60' : '#d32f2f' }}; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 800; border: 1px solid white;">
                    Total: {{ $totalJadwal }} / 10
                </span>
            </div>
        </div>

        <div class="table-responsive">
            <table style="width: 100%; border-collapse: collapse; border: 2px solid #000; background-color: #fff; border-radius: 0 0 12px 12px; overflow: hidden;">
                <thead>
                    <tr style="border-bottom: 2px solid #000; text-align: center; background-color: #f8f9fa;">
                        <th style="padding: 15px; border-right: 2px solid #000; width: 60px;">No</th>
                        <th style="padding: 15px; border-right: 2px solid #000; width: 150px;">Bulan</th>
                        <th style="padding: 15px; border-right: 2px solid #000;">Nama Nasabah</th>
                        <th style="padding: 15px; border-right: 2px solid #000; width: 120px;">KOL</th>
                        <th style="padding: 15px; width: 100px;">Option</th>
                    </tr>
                </thead>
                <tbody style="font-weight: 800; font-size: 15px; color: #000;">
                    @foreach($group as $index => $item)
                    @php $isPrioritas = ($item->kol == 5); @endphp
                    
                    <tr class="row-kunjungan" 
                        data-no-angsuran="{{ $item->no_angsuran }}" 
                        style="border-bottom: 2px solid #000; text-align: center; {{ $isPrioritas ? 'background-color: #fff5f5;' : '' }}">
                        
                        <td style="padding: 15px; border-right: 2px solid #000;">{{ $index + 1 }}</td>
                        <td style="padding: 15px; border-right: 2px solid #000;">
                            {{ \Carbon\Carbon::parse($item->bulan)->translatedFormat('F Y') }}
                        </td>
                        <td style="padding: 15px; border-right: 2px solid #000; text-align: left; padding-left: 20px;">
                            {{ $item->nama_nasabah }}
                            @if($isPrioritas)
                                <br><small style="color: #d32f2f; font-size: 11px;"><i class="fa-solid fa-triangle-exclamation"></i> WAJIB DIKUNJUNGI (KPI)</small>
                            @endif
                        </td>
                        <td style="padding: 15px; border-right: 2px solid #000;">
                            <span style="
                                padding: 5px 12px; 
                                border-radius: 6px; 
                                display: inline-block;
                                background-color: {{ $isPrioritas ? '#d32f2f' : '#eee' }}; 
                                color: {{ $isPrioritas ? '#ffffff' : '#333333' }};
                                border: {{ $isPrioritas ? 'none' : '1px solid #ccc' }};
                            ">
                                {{ $item->kol }}
                            </span>
                        </td>
                        <td style="padding: 15px; text-align: center; border: none;">
                            <button onclick="openModalEditKunjungan('{{ $item->id }}')" style="background: none; border: none; cursor: pointer;" title="Edit Jadwal">
                                <i class="fa-solid fa-pen-to-square" style="font-size: 18px; color: #333;"></i>
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endforeach
@endif

@include('admin.partials.modals') 

<script>
    function openModalImport() {
        document.getElementById('modalImport').style.display = 'flex';
    }
    function closeModalImport() {
        document.getElementById('modalImport').style.display = 'none';
    }
</script>