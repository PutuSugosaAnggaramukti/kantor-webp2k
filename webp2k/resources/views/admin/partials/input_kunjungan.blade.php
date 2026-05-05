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
            PENGINGAT PENTING UNTUK INPUT JADWAL KUNJUNGAN!
        </h4>
    </div>
    <ul style="margin: 0; padding-left: 25px; color: #856404; font-weight: 700; font-size: 13px; line-height: 1.6;">
        <li>Untuk menambah jadwal secara manual, Admin bisa menekan tombol "Tambah Jadwal Baru"</li>
        <li>Untuk mengimport jadwal dari file Excel, Admin bisa menekan tombol "Import dari Excel"</li>
    </ul>
</div>

<div style="margin-bottom: 25px; display: flex; gap: 12px; flex-wrap: wrap; align-items: center;">
    
    <button onclick="openModalKunjungan()" style="background-color: #28a745; color: white; border: none; padding: 12px 25px; border-radius: 8px; font-weight: 700; cursor: pointer; display: flex; align-items: center; gap: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
        <i class="fa-solid fa-plus"></i> Tambah Jadwal Baru
    </button>

    <button type="button" class="btn-import-excel" onclick="openModalImport()" style="padding: 12px 25px; border-radius: 8px; font-weight: 700;">
         <i class="fa-solid fa-file-excel"></i> Import dari Excel
    </button>

    <button onclick="resetJadwal()" style="background-color: #dc3545; color: white; border: none; padding: 12px 25px; border-radius: 8px; font-weight: 700; cursor: pointer; display: flex; align-items: center; gap: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
        <i class="fa-solid fa-rotate"></i> Reset Jadwal
    </button>

    <button onclick="openModalPilihHapus()" style="background-color: #ffc107; color: #000; border: none; padding: 12px 25px; border-radius: 8px; font-weight: 700; cursor: pointer; display: flex; align-items: center; gap: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
        <i class="fa-solid fa-list-check"></i> Pilih Jadwal
    </button>

    <form action="{{ route('admin.jadwal.export') }}" method="GET" class="d-flex align-items-center gap-2" style="margin: 0;">
        <select name="bulan" class="form-select" style="width: 140px; height: 45px; border-radius: 8px; border: 1px solid #ced4da; font-weight: 600;">
            @foreach(range(1, 12) as $m)
                <option value="{{ sprintf('%02d', $m) }}" {{ date('m') == $m ? 'selected' : '' }}>
                    {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
                </option>
            @endforeach
        </select>

        <select name="tahun" class="form-select" style="width: 100px; height: 45px; border-radius: 8px; border: 1px solid #ced4da; font-weight: 600;">
            @foreach(range(date('Y')-1, date('Y')+1) as $y)
                <option value="{{ $y }}" {{ date('Y') == $y ? 'selected' : '' }}>{{ $y }}</option>
            @endforeach
        </select>

        <button type="submit" 
            style="background-color: #198754; height: 45px; color: white; border: none; padding: 0 25px; border-radius: 8px; font-weight: 700; cursor: pointer; display: flex; align-items: center; gap: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
            <i class="fa-solid fa-file-excel"></i> Export Ke Excel
        </button>
    </form>
</div>

@if($kunjungansGrouped->isEmpty())
    <div style="text-align: center; padding: 50px; background: #f8f9fa; border-radius: 15px; border: 2px dashed #ccc;">
        <h3 style="color: #666;">Belum ada jadwal kunjungan yang dibuat.</h3>
    </div>
@else
   @foreach($kunjungansGrouped as $kodeAo => $group)
    @php 
        $totalJadwal = $group->count();
    @endphp
    
    <div class="ao-section" style="margin-bottom: 20px;">
        <div class="ao-header" onclick="toggleAo(this)" style="background-color: #4e4bc1; color: white; padding: 15px 20px; border-radius: 12px; display: flex; justify-content: space-between; align-items: center; border: 2px solid #000;">
            <h3 style="margin: 0; font-size: 18px; font-weight: 800; text-transform: uppercase; display: flex; align-items: center; gap: 12px;">
                <i class="fa-solid fa-chevron-down"></i>
                <i class="fa-solid fa-user-tie"></i> 
              {{ $group->first()->nama_ao ?? 'Nama AO Tidak Ditemukan' }} ({{ $kodeAo }})
            </h3>
            
            <div style="display: flex; gap: 10px; align-items: center;">
                {{-- Keterangan KOL 5 dihapus sesuai permintaan --}}
                
                {{-- Badge Total hanya menghitung jumlah data di dalam grup --}}
                <span style="background: #27ae60; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 800; border: 1px solid white;">
                    Total: {{ $totalJadwal }}
                </span>
            </div>
        </div>

        <div class="table-container">
            <div class="table-responsive" style="max-height: 400px; overflow-y: auto; border: 2px solid #000; border-top: none; border-radius: 0 0 12px 12px;">
               <table style="width: 100%; border-collapse: collapse; background-color: #fff; position: relative;">
                    <thead>
                        <tr style="position: sticky; top: 0; z-index: 10; background-color: #f8f9fa; border-bottom: 2px solid #000; text-align: center;">
                            <th style="padding: 15px; border-right: 2px solid #000; width: 60px;">No</th>
                            <th style="padding: 15px; border-right: 2px solid #000; width: 140px;">No Angsuran</th> 
                            <th style="padding: 15px; border-right: 2px solid #000; width: 100px;">Kode</th>
                            <th style="padding: 15px; border-right: 2px solid #000; width: 130px;">Bulan</th>
                            <th style="padding: 15px; border-right: 2px solid #000; width: 130px; background-color: #eef2ff;">Tgl Kunjungan</th>
                            <th style="padding: 15px; border-right: 2px solid #000;">Data Nasabah</th> 
                            <th style="padding: 15px; border-right: 2px solid #000; width: 130px;">Nominal</th> 
                            <th style="padding: 15px; border-right: 2px solid #000; width: 130px;">Sisa Pokok</th> 
                            <th style="padding: 15px; border-right: 2px solid #000; width: 60px;">KOL</th>
                            <th style="padding: 15px; width: 80px;">Aksi</th>
                        </tr>
                    </thead>

                    <tbody style="font-weight: 800; font-size: 14px; color: #000;">
                        @foreach($group as $index => $item)
                        {{-- Tambahkan pengecekan ini: Jika ID kosong atau Nama Nasabah tidak ada, lewati --}}
                        @if(!empty($item->id) && (!empty($item->nama_nasabah) || !empty($item->nasabah)))
                            
                            @php $isPrioritas = ($item->kol == 5); @endphp
                        <tr class="row-kunjungan" data-no-angsuran="{{ $item->no_angsuran }}" style="border-bottom: 2px solid #000; text-align: center; {{ $isPrioritas ? 'background-color: #fff5f5;' : '' }}">
                            <td style="padding: 12px; border-right: 2px solid #000;">{{ $index + 1 }}</td>
                            
                            <td style="padding: 12px; border-right: 2px solid #000; color: #444;">
                                {{ $item->no_angsuran ?? '-' }}
                            </td>

                            <td style="padding: 12px; border-right: 2px solid #000; color: #4e4bc1;">
                                {{ $item->kode_nasabah ?? '-' }}
                            </td>

                            <td style="padding: 12px; border-right: 2px solid #000;">
                                {{ \Carbon\Carbon::parse($item->bulan)->translatedFormat('F Y') }}
                            </td>

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

                            <td style="padding: 12px;">
                                <button type="button" 
                                    onclick="hapusJadwalNasabah(this, '{{ $item->nama_nasabah }}')"
                                    data-url="{{ route('admin.hapusJadwalSingle', $item->id) }}"
                                    style="background: #ff4d4d; color: white; border: 2px solid #000; padding: 6px 10px; border-radius: 8px; cursor: pointer;"
                                    onmouseover="this.style.backgroundColor='#cc0000'" 
                                    onmouseout="this.style.backgroundColor='#ff4d4d'">
                                    <i class="fa-solid fa-trash-can"></i>
                                </button>
                            </td>
                        </tr>
                        @endif
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
    (function() {
        window.toggleAo = function(element) {
            const section = element.parentElement;
            section.classList.toggle('active');
        };

        window.openModalImport = function() {
            const modal = document.getElementById('modalImport');
            if (modal) modal.style.display = 'flex';
        };

        window.openModalKunjungan = function() {
                const modal = document.querySelector('#modalTambahKunjungan');
                if (modal) {
                    modal.style.display = 'flex';
                    const dropdown = modal.querySelector('#dropdown_no_angsuran');
                    
                    if (dropdown) {
                        // 1. Hancurkan instance lama agar tidak error 'isConnected'
                        if ($(dropdown).hasClass("select2-hidden-accessible")) {
                            $(dropdown).select2('destroy');
                        }

                        // 2. Inisialisasi dengan fitur AJAX
                    $(dropdown).select2({
                    dropdownParent: $(modal),
                    placeholder: "Cari No Angsuran / Nama Nasabah...",
                    allowClear: true,
                    // UBAH INI:
                    minimumInputLength: 0, 
                    ajax: {
                        url: '/admin/get-daftar-no-anggota',
                        dataType: 'json',
                        delay: 250,
                        data: function (params) {
                            return {
                                q: params.term // Jika kosong, controller akan tetap mengirim 15 data pertama
                            };
                        },
                        processResults: function (data) {
                            return { results: data };
                        },
                        cache: true
                    }
                });

                // 3. Update event listener untuk mengambil data dari objek AJAX
               $(dropdown).off('select2:select').on('select2:select', function (e) {
                    const data = e.params.data; 

                    // 1. Update Kode Nasabah (Label)
                    $('#display_kode').text(data.kode || '-'); 
                    
                    // 2. Update Nama Nasabah (Sesuai ID HTML Anda: display_nama)
                    // Gunakan data.nasabah karena di Controller kuncinya adalah 'nasabah'
                    $('#display_nama').val(data.nasabah || ''); 
                    
                    // 3. Update Alamat dan KOL
                    $('#display_alamat').val(data.alamat || '');
                    $('#display_kol').val(data.kol || '-');

                    // Jika Anda punya input hidden atau field lain dengan ID berbeda, sesuaikan di sini:
                    // $('#input_nama_nasabah').val(data.nasabah || ''); 
                });
            }

            const form = modal.querySelector('#formTambahKunjungan');
            if(form) {
                form.reset();
                $(dropdown).val(null).trigger('change');
                $('#display_kode').text('-');
            }
        }
    };

        window.closeModalKunjungan = function() {
            const modals = document.querySelectorAll('#modalTambahKunjungan');
            modals.forEach(m => m.style.display = 'none');
        };

      window.simpanJadwalManual = function() {
            const activeModal = Array.from(document.querySelectorAll('#modalTambahKunjungan')).find(m => m.style.display === 'flex');
            const form = activeModal ? activeModal.querySelector('#formTambahKunjungan') : document.getElementById('formTambahKunjungan');
            
            if (!form) return;

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

   function resetJadwal() {
        Swal.fire({
            title: 'Buat Jadwal Baru?',
            text: "Semua daftar jadwal kunjungan saat ini akan dihapus untuk memulai jadwal baru!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Hapus Semua!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "{{ route('admin.datakunjungan.reset') }}",
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        Swal.fire('Berhasil!', response.success, 'success');
                        // Ganti 'adm-kunjungan-content' sesuai dengan rute konten yang kamu gunakan
                        loadAdminPage('adm-kunjungan-content'); 
                    },
                    error: function(xhr) {
                        Swal.fire('Gagal!', 'Terjadi kesalahan: ' + xhr.responseText, 'error');
                    }
                });
            }
        })
    }

function renderListHapus(dataAO) {
    let listContainer = document.getElementById('listJadwalHapus');
    listContainer.innerHTML = '';

    if (dataAO.length === 0) {
        document.getElementById('modalPilihHapus').style.display = 'none';
        Swal.fire('Info', 'Tidak ada data AO yang memiliki jadwal bulan ini.', 'info');
        return;
    }

    dataAO.forEach((item) => {
        let rowHtml = `
            <div style="padding: 12px; border-bottom: 1px solid #eee; display: flex; align-items: center; gap: 15px;">
                <input type="checkbox" class="chk-hapus-ao" value="${item.kode_ao}" style="width: 18px; height: 18px; cursor: pointer;">
                <div style="flex-grow: 1;">
                    <div style="font-weight: bold; color: #333; font-size: 14px;">${item.nama}</div>
                    <div style="font-size: 12px; color: #666;">
                        <span style="background: #e9ecef; padding: 2px 6px; border-radius: 4px;">Kode: ${item.kode_ao}</span>
                        <span style="margin-left: 10px; font-weight: 600; color: #dc3545;">(${item.total_jadwal} Nasabah)</span>
                    </div>
                </div>
            </div>
        `;
        listContainer.innerHTML += rowHtml;
    });
}

function openModalPilihHapus() {
    let listContainer = document.getElementById('listJadwalHapus');
    listContainer.innerHTML = '<div style="padding: 20px; text-align: center;">Mengambil data terbaru...</div>';

    fetch("{{ route('kunjungan.getDaftarAOHapus') }}")
        .then(response => response.json())
        .then(dataAO => {
            renderListHapus(dataAO);
            document.getElementById('modalPilihHapus').style.display = 'block';
        });
}

function closeModalPilihHapus() {
    document.getElementById('modalPilihHapus').style.display = 'none';
}

function hapusJadwalNasabah(btn, nama) {
    let urlHapus = $(btn).data('url'); 

    Swal.fire({
        title: 'Hapus Jadwal?',
        text: "Anda akan menghapus jadwal nasabah: " + nama,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            // Berikan loading agar user tahu proses sedang jalan
            Swal.fire({
                title: 'Mohon Tunggu',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });

            $.ajax({
                url: urlHapus,
                type: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    // CEK STATUS DARI CONTROLLER
                    if (response.status === 'success') {
                        Swal.fire('Berhasil!', response.message, 'success').then(() => {
                            location.reload(); // Refresh agar baris hilang total
                        });
                    } else {
                        Swal.fire('Gagal!', response.message, 'error');
                    }
                },
                error: function(xhr) {
                    // Jika 404 (Data sudah tidak ada), tetap beri notif dan reload
                    if(xhr.status === 404) {
                        Swal.fire('Info', 'Data sudah tidak ada di server.', 'info').then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire('Gagal!', 'Terjadi kesalahan sistem (Error ' + xhr.status + ')', 'error');
                    }
                }
            });
        }
    });
}

function prosesHapusPilihan() {
    let selectedAO = [];
    
    // Mengambil Kode AO dari checkbox yang dicentang
    document.querySelectorAll('.chk-hapus-ao:checked').forEach(chk => {
        selectedAO.push(chk.value);
    });

    // Validasi jika tidak ada yang dipilih
    if (selectedAO.length === 0) {
        Swal.fire({
            title: 'Peringatan',
            text: 'Pilih minimal satu AO yang ingin dihapus!',
            icon: 'warning',
            target: 'body' // Memastikan muncul di depan modal
        });
        return;
    }

    // Konfirmasi penghapusan
    Swal.fire({
        title: 'Konfirmasi Hapus',
        text: `Anda akan menghapus SELURUH jadwal untuk ${selectedAO.length} AO yang dipilih. Data pada Pelaporan dan Rekap juga akan berubah. Lanjutkan?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Hapus Semua!',
        cancelButtonText: 'Batal',
        target: 'body' // Memastikan muncul di depan modal
    }).then((result) => {
        if (result.isConfirmed) {
            // Tampilkan loading agar user tahu proses sedang berjalan
            Swal.showLoading();

            $.ajax({
                url: "{{ route('admin.datakunjungan.delete-selected') }}",
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    ids: selectedAO // Mengirim daftar kode AO ke Controller
                },
                success: function(response) {
                    Swal.fire({
                        title: 'Berhasil!',
                        text: response.success,
                        icon: 'success',
                        target: 'body'
                    });
                    
                    closeModalPilihHapus();
                    
                    // Refresh konten halaman
                    if (typeof loadAdminPage === 'function') {
                        loadAdminPage('adm-kunjungan-content'); 
                    } else {
                        location.reload();
                    }
                },
                error: function(xhr) {
                    console.error(xhr.responseText);
                    Swal.fire({
                        title: 'Gagal!',
                        text: 'Terjadi kesalahan saat menghapus data di server.',
                        icon: 'error',
                        target: 'body'
                    });
                }
            });
        }
    });
}
</script>