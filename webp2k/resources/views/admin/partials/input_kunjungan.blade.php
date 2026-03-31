<style>
    .ao-header {
        cursor: pointer;
        transition: background 0.3s ease;
    }
    .ao-header:hover {
        background-color: #3b38a3 !important; 
    }
    .table-container {
        display: none;
    }
    .ao-section.active .table-container {
        display: block;
    }
    .fa-chevron-down {
        transition: transform 0.3s ease;
    }
    .ao-section.active .fa-chevron-down {
        transform: rotate(180deg);
    }
    #dropdown_no_angsuran option {
        display: block !important;
        visibility: visible !important;
        opacity: 1 !important;
    }
    .select2-results__option[aria-disabled="true"] {
        display: block !important;
    }
</style>

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
    
    <div class="ao-section" style="margin-bottom: 20px;">
        <div class="ao-header" onclick="toggleAo(this)" style="background-color: #4e4bc1; color: white; padding: 15px 20px; border-radius: 12px; display: flex; justify-content: space-between; align-items: center; border: 2px solid #000;">
            <h3 style="margin: 0; font-size: 18px; font-weight: 800; text-transform: uppercase; display: flex; align-items: center; gap: 12px;">
                <i class="fa-solid fa-chevron-down"></i>
                <i class="fa-solid fa-user-tie"></i> 
                {{ $group->first()->karyawan->nama ?? 'Nama AO Tidak Ditemukan' }} ({{ $kodeAo }})
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

        <div class="table-container">
            <div class="table-responsive" style="max-height: 400px; overflow-y: auto; border: 2px solid #000; border-top: none; border-radius: 0 0 12px 12px;">
                <table style="width: 100%; border-collapse: collapse; background-color: #fff; position: relative;">
                    <thead>
                        <tr style="position: sticky; top: 0; z-index: 10; background-color: #f8f9fa; border-bottom: 2px solid #000; text-align: center;">
                            <th style="padding: 15px; border-right: 2px solid #000; width: 60px;">No</th>
                            <th style="padding: 15px; border-right: 2px solid #000; width: 140px;">No Angsuran</th> <th style="padding: 15px; border-right: 2px solid #000; width: 130px;">Bulan</th>
                            <th style="padding: 15px; border-right: 2px solid #000; width: 130px; background-color: #eef2ff;">Tgl Kunjungan</th>
                            <th style="padding: 15px; border-right: 2px solid #000;">Data Nasabah</th> <th style="padding: 15px; border-right: 2px solid #000; width: 130px;">Nominal</th> 
                            <th style="padding: 15px; border-right: 2px solid #000; width: 130px;">Sisa Pokok</th> 
                            <th style="padding: 15px; border-right: 2px solid #000; width: 60px;">KOL</th>
                        </tr>
                    </thead>

                    <tbody style="font-weight: 800; font-size: 14px; color: #000;">
                        @foreach($group as $index => $item)
                        @php $isPrioritas = ($item->kol == 5); @endphp
                        <tr class="row-kunjungan" data-no-angsuran="{{ $item->no_angsuran }}" style="border-bottom: 2px solid #000; text-align: center; {{ $isPrioritas ? 'background-color: #fff5f5;' : '' }}">
                            <td style="padding: 12px; border-right: 2px solid #000;">{{ $index + 1 }}</td>
                            
                            <td style="padding: 12px; border-right: 2px solid #000; color: #444;">
                                {{ $item->no_angsuran ?? '-' }}
                            </td>

                            <td style="padding: 12px; border-right: 2px solid #000;">
                                {{ \Carbon\Carbon::parse($item->bulan)->translatedFormat('F Y') }}
                            </td>

                            {{-- ISI TANGGAL KUNJUNGAN BARU --}}
                            <td style="padding: 12px; border-right: 2px solid #000; color: #4e4bc1; background-color: #fcfdff;">
                                @if($item->tanggal)
                                    <i class="fa-regular fa-calendar-check"></i> 
                                    {{ \Carbon\Carbon::parse($item->tanggal)->translatedFormat('d M Y') }}
                                @else
                                    <span style="color: #ccc; font-weight: 400;">Belum diatur</span>
                                @endif
                            </td>

                            <td style="padding: 12px; border-right: 2px solid #000; text-align: left; padding-left: 15px;">
                                <span style="font-size: 15px; display: block; margin-bottom: 4px;">{{ $item->nama_nasabah ?? $item->nasabah }}</span>
                                
                                <small style="color: #666; font-weight: 600; font-size: 11px; display: block; line-height: 1.2;">
                                    <i class="fa-solid fa-location-dot"></i> {{ $item->alamat_nasabah ?? $item->alamat ?? '-' }}
                                </small>

                                @if($isPrioritas)
                                    <small style="color: #d32f2f; font-size: 10px; font-weight: 900; margin-top: 5px; display: block;">
                                        <i class="fa-solid fa-triangle-exclamation"></i> WAJIB (KPI)
                                    </small>
                                @endif
                            </td>

                            <td style="padding: 12px; border-right: 2px solid #000; text-align: right; color: #4e4bc1;">
                                {{ number_format($item->nominal ?? 0, 0, ',', '.') }}
                            </td>
                            
                            <td style="padding: 12px; border-right: 2px solid #000; text-align: right; color: #d32f2f;">
                                {{ number_format($item->sisa_pokok ?? 0, 0, ',', '.') }}
                            </td>

                            <td style="padding: 12px; border-right: 2px solid #000;">
                                <span style="padding: 4px 10px; border-radius: 6px; background-color: {{ $isPrioritas ? '#d32f2f' : '#eee' }}; color: {{ $isPrioritas ? '#ffffff' : '#333333' }};">
                                    {{ $item->kol }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endforeach
@endif

@include('admin.partials.modals') 

<script>
    // Gunakan pendekatan IIFE atau pastikan fungsi tidak dideklarasikan ulang jika memungkinkan
    (function() {
        // 1. Fungsi Toggle Accordion AO
        window.toggleAo = function(element) {
            const section = element.parentElement;
            section.classList.toggle('active');
        };

        // 2. Fungsi Modal Import
        window.openModalImport = function() {
            const modal = document.getElementById('modalImport');
            if (modal) modal.style.display = 'flex';
        };

        // 3. Fungsi Modal Tambah Jadwal (VERSI PERBAIKAN TOTAL)
      window.openModalKunjungan = function() {
            const modal = document.querySelector('#modalTambahKunjungan');
            if (modal) {
                modal.style.display = 'flex';
                
                // Cari semua dropdown nasabah
                const dropdown = modal.querySelector('#dropdown_no_angsuran');
                if (dropdown) {
                    // 1. Hancurkan Select2 jika ada
                    if ($(dropdown).hasClass("select2-hidden-accessible")) {
                        $(dropdown).select2('destroy');
                    }

                    // 2. Bersihkan atribut 'hidden' atau 'style' yang aneh-aneh dari tiap option
                    const options = dropdown.options;
                    for (let i = 0; i < options.length; i++) {
                        options[i].removeAttribute('hidden');
                        options[i].disabled = false;
                        options[i].style.cssText = "display: block !important; visibility: visible !important;";
                    }

                    // 3. Reset value ke awal
                    dropdown.value = "";

                    // 4. Inisialisasi ulang Select2 dengan Parent ke Modal
                    $(dropdown).select2({
                        dropdownParent: $(modal)
                    });
                }
                
                // Reset form lainnya
                const form = modal.querySelector('#formTambahKunjungan');
                if(form) form.reset();
            }
        };

        window.closeModalKunjungan = function() {
            const modals = document.querySelectorAll('#modalTambahKunjungan');
            modals.forEach(m => m.style.display = 'none');
        };

        // --- LOGIC SIMPAN JADWAL ---
      window.simpanJadwalManual = function() {
            // 1. Ambil form dari modal yang sedang aktif
            const activeModal = Array.from(document.querySelectorAll('#modalTambahKunjungan')).find(m => m.style.display === 'flex');
            const form = activeModal ? activeModal.querySelector('#formTambahKunjungan') : document.getElementById('formTambahKunjungan');
            
            if (!form) return;

            // 2. Cek validasi form (required fields)
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }

            const formData = new FormData(form);

            fetch('/admin/datakunjungan/store', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: data.message,
                        showConfirmButton: false,
                        timer: 1500
                    });

                    closeModalKunjungan();
                    
                    // 3. REFRESH TOTAL
                    // Kita buang loadAdminPage karena itu penyebab Ahmad Sujarwo tersaring/hilang.
                    // Dengan reload, semua dropdown akan ditarik ulang dengan kondisi bersih.
                    location.reload(); 
                    
                } else {
                    Swal.fire({ 
                        icon: 'error', 
                        title: 'Gagal', 
                        text: data.message || 'Terjadi kesalahan' 
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({ 
                    icon: 'error', 
                    title: 'Error', 
                    text: 'Gagal terhubung ke server' 
                });
            });
        };
    })();
</script>