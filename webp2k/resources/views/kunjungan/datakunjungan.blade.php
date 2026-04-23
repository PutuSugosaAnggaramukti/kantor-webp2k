<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIPANTAU P2K</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/kunjunganuser.css') }}">

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <style>
        .btn-excel { cursor: pointer; transition: 0.3s; }
        .btn-excel:hover { background-color: #16a34a !important; }
        .nav-item { cursor: pointer; }
        .status-circle { width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto; }
        .bg-success { background-color: #22c55e; color: white; }
        .bg-pending { border: 2px solid #1e293b; color: #1e293b; }
    </style>
</head>
<body>

    <div class="wrapper">
        <div class="sidebar">
            <h1>Menu</h1>

            {{-- TOMBOL KEMBALI KE DASHBOARD USER --}}
         <a href="javascript:void(0)" 
            onclick="window.location.href='/user/dashboard'" 
            class="nav-item {{ request()->is('user/dashboard*') ? 'active' : '' }}" 
            id="menu-dashboard">
                <i class="fa-solid fa-gauge-high"></i> Dashboard Utama
         </a>

            {{-- Menu yang sudah ada sebelumnya --}}
            <a href="javascript:void(0)" onclick="loadPage('data-kunjungan')" class="nav-item {{ request()->is('data-kunjungan*') ? 'active' : '' }}" id="menu-data">
                <i class="fa-solid fa-user-plus"></i> Data Kunjungan
            </a>
            
            <a href="javascript:void(0)" onclick="loadPage('laporan-kunjungan')" class="nav-item {{ request()->is('laporan-kunjungan*') ? 'active' : '' }}" id="menu-laporan">
                <i class="fa-solid fa-file-lines"></i> Laporan Kunjungan
            </a>
            
            <a href="javascript:void(0)" onclick="loadPage('dokumen')" class="nav-item {{ request()->is('dokumen*') ? 'active' : '' }}" id="menu-dokumen">
                <i class="fa-solid fa-file-invoice"></i> Dokumen
            </a>
            
            <a href="javascript:void(0)" onclick="loadPage('pengaturan')" class="nav-item {{ request()->is('pengaturan*') ? 'active' : '' }}" id="menu-pengaturan">
                <i class="fa-solid fa-gear"></i> Pengaturan
            </a>
        </div>

        <div class="main-container" style="flex: 1; display: flex; flex-direction: column;">
            <div class="header">
                <div class="logo-section">
                    <img src="{{ asset('assets/logo.png') }}" alt="Logo">
                    <span>SIPANTAU<br>P2K</span>
                </div>
               <div class="user-profile" style="position: relative; cursor: pointer;">
                    <span style="font-weight: 700;">{{ Auth::guard('karyawan')->user()->nama }}</span>
                    <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::guard('karyawan')->user()->nama) }}&background=4e4bc1&color=fff" alt="User">
                    <i class="fa-solid fa-chevron-down" style="font-size: 12px; margin-left: 5px; color: #666;"></i>

                    <div id="userDropdown" style="display: none; position: absolute; right: 0; top: 50px; width: 150px; background: white; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); z-index: 1000; border: 1px solid #eee;">
                        <ul style="list-style: none; margin: 0; padding: 0;">
                            <li style="padding: 10px 15px; border-bottom: 1px solid #eee; font-size: 14px; color: #333;">
                                <i class="fa-solid fa-user" style="margin-right: 8px;"></i> Profil
                            </li>
                            <li style="padding: 0;">
                                <form action="{{ route('logout') }}" method="POST" id="logout-form">
                                    @csrf
                                    <button type="submit" style="width: 100%; text-align: left; padding: 10px 15px; background: none; border: none; color: #e74c3c; cursor: pointer; font-family: inherit; font-size: 14px; font-weight: 600;">
                                        <i class="fa-solid fa-right-from-bracket" style="margin-right: 8px;"></i> Logout
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="main-content">
                <div id="konten-utama">
                    @include('kunjungan.partials.data_table')
                </div>
            </div>
        </div>
    </div>

    <div class="footer">
        Sistem Aplikasi P2K
    </div>

    @include('kunjungan.partials.modals')

  <script>
    // --- State Global ---
    let fileSiapUpload = null;
    const formatRp = new Intl.NumberFormat('id-ID');

    // --- Fungsi Navigasi & Load Halaman ---
    function loadPage(pageName) {
        const contentArea = document.getElementById('konten-utama');
        contentArea.style.opacity = '0.3';

        fetch(`/user/${pageName}-content`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => {
            if (!response.ok) throw new Error('Gagal memuat halaman');
            return response.text();
        })
        .then(html => {
            contentArea.innerHTML = html;
            contentArea.style.opacity = '1';
            updateSidebarActive(pageName);
        })
        .catch(error => {
            console.error('Fetch error:', error);
            contentArea.style.opacity = '1';
        });
    }

    function loadContent(url) {
        const contentArea = document.getElementById('konten-utama');
        contentArea.style.opacity = '0.3';

        $.ajax({
            url: url,
            type: 'GET',
            success: function(response) {
                $('#konten-utama').html(response);
                contentArea.style.opacity = '1';
            },
            error: function(xhr) {
                contentArea.style.opacity = '1';
                alert('Gagal memuat konten.');
            }
        });
    }

    function updateSidebarActive(pageName) {
        document.querySelectorAll('.nav-item').forEach(el => el.classList.remove('active'));
        const menuMap = {
            'laporan-kunjungan': 'menu-laporan',
            'dokumen': 'menu-dokumen',
            'pengaturan': 'menu-pengaturan'
        };
        const targetId = menuMap[pageName] || 'menu-data';
        const activeEl = document.getElementById(targetId);
        if (activeEl) activeEl.classList.add('active');
    }

    // --- Fungsi Modal (General) ---
    function openModal(nama, kodeAO, noAngsuran) {
        document.getElementById('form-no-nasabah').value = noAngsuran;
        document.getElementById('form-nama-nasabah').value = nama;
        document.getElementById('display-kode-ao').value = kodeAO;
        document.getElementById('display-nama').value = nama;
        document.getElementById('visitModal').style.display = 'flex';
        updateGPSLocation('form-koordinat', 'location-status');
    }

    function openDetailModal(kode, angsuran, nama, alamat, nominal, sisa, kol, kodeAo, namaAo) {
        document.getElementById('detail-kode').innerText = kode || '-';
        document.getElementById('detail-angsuran').innerText = angsuran || '-';
        document.getElementById('detail-nama').innerText = nama || '-';
        document.getElementById('detail-alamat').innerText = alamat || '-';

        const parseNominal = (val) => val ? 'Rp ' + formatRp.format(Number(val.toString().replace(/[^0-9.-]+/g,""))) : 'Rp 0';
        document.getElementById('detail-nominal').innerText = parseNominal(nominal);
        document.getElementById('detail-sisa').innerText = parseNominal(sisa);

        document.getElementById('detail-kol').innerText = kol || '-';
        document.getElementById('detail-kode-ao').innerText = kodeAo || '-';
        document.getElementById('detail-nama-ao').innerText = namaAo || '-';
        document.getElementById('detailModal').style.display = 'flex';
    }

    function closeModal() {
        const modals = ['visitModal', 'detailModal', 'modalManual'];
        modals.forEach(id => {
            const el = document.getElementById(id);
            if (el) {
                el.style.display = 'none';
                const form = el.querySelector('form');
                if (form) form.reset();
            }
        });
        const statusText = document.getElementById('location-status');
        if (statusText) statusText.innerHTML = '';
        fileSiapUpload = null;
    }

    // --- Fungsi Geolocation ---
   function updateGPSLocation(inputId, statusId) {
        const input = document.getElementById(inputId);
        const status = document.getElementById(statusId);
        
        if (status) status.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Mencari lokasi (Pastikan GPS HP Aktif)...';

        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                (pos) => {
                    const loc = `${pos.coords.latitude}, ${pos.coords.longitude}`;
                    if (input) input.value = loc;
                    if (status) {
                        // Beri info akurasi juga agar AO tahu lokasinya sudah pas atau belum
                        const akurasi = Math.round(pos.coords.accuracy);
                        status.innerHTML = `<span style="color: #28a745;"><i class="fas fa-check-circle"></i> Lokasi Terkunci (Akurasi: ${akurasi}m)</span>`;
                    }
                },
                (error) => {
                    let pesanError = "GPS Error";
                    switch(error.code) {
                        case error.PERMISSION_DENIED:
                            pesanError = "Izin Lokasi Ditolak Browser!";
                            break;
                        case error.POSITION_UNAVAILABLE:
                            pesanError = "Sinyal GPS Tidak Tersedia";
                            break;
                        case error.TIMEOUT:
                            pesanError = "Waktu Tunggu Habis (Sinyal Lemah)";
                            break;
                    }
                    if (status) status.innerHTML = `<span style="color: #dc3545;"><i class="fas fa-times-circle"></i> ${pesanError}</span>`;
                },
                { 
                    enableHighAccuracy: true, 
                    timeout: 15000, // Dinaikkan jadi 15 detik
                    maximumAge: 0   // Memaksa browser mengambil lokasi baru, bukan lokasi cache
                }
            );
        } else {
            if (status) status.innerHTML = "Browser tidak mendukung GPS";
        }
    }

    function confirmDeleteJadwal(id, nama, noAngsuran) { 
    Swal.fire({
        title: 'Hapus Jadwal?',
        html: `Apakah Anda yakin ingin menghapus jadwal?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="fa fa-trash"></i> Ya, Hapus!',
        cancelButtonText: 'Batal',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Mohon Tunggu',
                text: 'Sedang menghapus data...',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });

            // GUNAKAN ID UNTUK URL
            let url = "{{ route('hapus.jadwal', ':id') }}"; 
            url = url.replace(':id', id); 

            fetch(url, {
                method: 'DELETE', // Gunakan DELETE sesuai route:list Mas tadi
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}' // Tambahkan CSRF Token agar tidak error 419
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Terhapus!',
                        text: data.success,
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire('Gagal!', data.error || 'Terjadi kesalahan.', 'error');
                }
            })
            .catch(error => {
                Swal.fire('Error!', 'Koneksi ke server terputus.', 'error');
            });
        }
    });
}
    // --- Manajemen Akun & Settings ---
    function switchSettingsTab(tab) {
        const isAkun = tab === 'akun';
        document.getElementById('section-akun').style.display = isAkun ? 'block' : 'none';
        document.getElementById('section-sandi').style.display = isAkun ? 'none' : 'block';
        
        document.getElementById('tab-btn-akun').classList.toggle('tab-active', isAkun);
        document.getElementById('tab-btn-sandi').classList.toggle('tab-active', !isAkun);
    }

    async function handleAjaxSettings(route, bodyData, successMsg) {
        const token = document.querySelector('meta[name="csrf-token"]')?.content || document.querySelector('input[name="_token"]')?.value;
        try {
            const response = await fetch(route, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token },
                body: JSON.stringify(bodyData)
            });
            const res = await response.json();
            if (response.ok) {
                Swal.fire('Berhasil!', successMsg || res.success, 'success');
                return res;
            }
            throw new Error(res.error || 'Terjadi kesalahan');
        } catch (e) {
            Swal.fire('Gagal', e.message, 'error');
        }
    }

    // --- Avatar Management ---
    function previewAvatar(input) {
        if (input.files && input.files[0]) {
            fileSiapUpload = input.files[0];
            const reader = new FileReader();
            reader.onload = (e) => document.getElementById('display-avatar').src = e.target.result;
            reader.readAsDataURL(fileSiapUpload);
        }
    }

    async function simpanAvatarKeServer() {
        if (!fileSiapUpload) return Swal.fire('Info', 'Pilih foto terlebih dahulu!', 'info');
        
        const formData = new FormData();
        formData.append('avatar', fileSiapUpload);
        const token = document.querySelector('meta[name="csrf-token"]').content;

        try {
            const response = await fetch("{{ route('settings.avatar') }}", {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': token }
            });
            const res = await response.json();
            if (response.ok) {
                Swal.fire('Berhasil!', 'Avatar diperbarui', 'success');
                const url = res.url + '?t=' + new Date().getTime();
                document.querySelectorAll('.profile-avatar-img, .user-profile img, #display-avatar').forEach(img => img.src = url);
                fileSiapUpload = null;
            }
        } catch (e) { Swal.fire('Error', 'Gagal upload', 'error'); }
    }

    // --- Event Listeners ---
    document.addEventListener('DOMContentLoaded', function() {
        // Deep linking page
        const targetPage = new URLSearchParams(window.location.search).get('page');
        if (targetPage) loadPage(targetPage);

        // Alert Session
        @if(session('success'))
            Swal.fire({ icon: 'success', title: 'Berhasil!', text: "{{ session('success') }}", timer: 2500, showConfirmButton: false });
        @endif

        @if($errors->any())
            Swal.fire({ icon: 'error', title: 'Oops...', text: 'Periksa kembali inputan Anda' });
        @endif
    });

    window.addEventListener('keydown', (e) => { if (e.key === 'Escape') closeModal(); });

    document.addEventListener('click', function (e) {
        // Dropdown Toggle
        if (e.target.closest('.user-profile')) {
            const dd = document.getElementById("userDropdown");
            dd.style.display = (dd.style.display === "block") ? "none" : "block";
        } else if (!e.target.closest('.user-profile')) {
            if(document.getElementById("userDropdown")) document.getElementById("userDropdown").style.display = "none";
        }

        // Close Modal on Overlay Click
        if (e.target.classList.contains('modal-overlay') || e.target.id === 'visitModal' || e.target.id === 'detailModal') {
            closeModal();
        }
    });

    document.getElementById('btn-save-kunjungan').addEventListener('click', function(e) {
    e.preventDefault(); // Mencegah submit default browser

    const btn = this; // Simpan referensi tombol
    const form = btn.closest('form');
    const formData = new FormData(form);

    // 1. Cek jika sedang loading (mencegah double click)
    if (btn.disabled) return;

    // 2. Konfirmasi awal sebelum simpan
    Swal.fire({
        title: 'Simpan Laporan?',
        text: "Pastikan foto yang diunggah memiliki data GPS (EXIF).",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3f36b1',
        cancelButtonColor: '#cbd5e1',
        confirmButtonText: 'Ya, Simpan!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            
            // LOCK: Matikan tombol dan ganti teksnya
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';

            // 3. Tampilkan Loading (Sangat penting untuk proses upload foto)
            Swal.fire({
                title: 'Sedang Memproses...',
                html: 'Menyimpan data dan memvalidasi lokasi foto.',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // 4. Kirim data menggunakan Fetch API
            fetch(form.action, {
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
                    // 5. Notifikasi Berhasil
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: data.success,
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        closeModal();
                        location.reload(); 
                    });
                } else {
                    // UNLOCK: Aktifkan kembali jika gagal
                    btn.disabled = false;
                    btn.innerHTML = 'Ya, Simpan!';

                    // 6. Notifikasi Gagal
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: data.error || 'Terjadi kesalahan saat menyimpan.',
                    });
                }
            })
            .catch(error => {
                // UNLOCK: Aktifkan kembali jika error koneksi
                btn.disabled = false;
                btn.innerHTML = 'Ya, Simpan!';
                
                console.error('Error:', error);
                Swal.fire('Error', 'Gagal terhubung ke server.', 'error');
            });
        }
    });
});

    // --- Handler Pagination AJAX ---
$(document).on('click', '.pagination a', function(e) {
    e.preventDefault();
    
    // AMBIL URL ASLI (Contoh: https://sipantau.../user/data-kunjungan?page=3)
    let url = $(this).attr('href'); 
    
    if (url) {
        const contentArea = document.getElementById('konten-utama');
        contentArea.style.opacity = '0.3';

        $.ajax({
            url: url, // Pakai URL utuh, jangan dipotong-potong
            type: 'GET',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            success: function(response) {
                $('#konten-utama').html(response);
                contentArea.style.opacity = '1';
                window.scrollTo({ top: 0, behavior: 'smooth' });
            },
            error: function(xhr) {
                contentArea.style.opacity = '1';
                console.error("Error pagination detail:", xhr.responseText);
            }
        });
    }
});

function openManualModal() {
    const modal = document.getElementById('modalManual');
    if (modal) {
        modal.style.display = 'flex';
        // Jalankan deteksi GPS untuk modal manual
        updateGPSLocation('manual-koordinat', 'manual-location-status');
    } else {
        Swal.fire('Error', 'Modal mandiri tidak ditemukan di DOM.', 'error');
    }
}

document.getElementById('formKunjunganMandiri').addEventListener('submit', function(e) {
    e.preventDefault(); // Mencegah browser pindah halaman

    // 1. Tampilkan Loading (Penting karena proses validasi GPS foto cukup berat)
    Swal.fire({
        title: 'Sedang Menyimpan...',
        text: 'Memvalidasi data dan lokasi GPS foto.',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    // 2. Ambil data dari form
    const formData = new FormData(this);

    // 3. Kirim via Fetch API (AJAX)
    fetch(this.action, {
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
            // 4. Jika Berhasil
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: data.success,
                timer: 2000,
                showConfirmButton: false
            }).then(() => {
                closeModal(); // Tutup modal
                location.reload(); // Refresh halaman untuk melihat data baru
            });
        } else {
            // 5. Jika Gagal (Error Validasi EXIF, dsb)
            Swal.fire({
                icon: 'error',
                title: 'Gagal Simpan',
                text: data.error || 'Terjadi kesalahan sistem.'
            });
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire('Error', 'Gagal terhubung ke server.', 'error');
    });
});

function simpanJadwalMandiri() {
    // Ambil data dari form
    let formData = {
        _token: "{{ csrf_token() }}",
        no_angsuran: $('#no_angsuran').val(),
        nama_nasabah: $('#nama_nasabah').val(),
        alamat_nasabah: $('#alamat_nasabah').val(),
        kol: $('#kol_nasabah').val(),
        bulan: $('#bulan_input').val(),
        tanggal: $('#tanggal_kunjungan').val()
    };

    // Validasi sederhana sebelum kirim
    if (!formData.no_angsuran || !formData.tanggal) {
        Swal.fire('Peringatan', 'Mohon pilih nasabah dan tanggal kunjungan!', 'warning');
        return;
    }

    $.ajax({
        url: "{{ route('ao.kunjungan.store') }}", // Arahkan ke KunjunganController
        type: "POST",
        data: formData,
        beforeSend: function() {
            Swal.showLoading();
        },
        success: function(response) {
            if (response.success) {
                Swal.fire('Berhasil!', response.message, 'success').then(() => {
                    // Refresh halaman atau reset form
                    location.reload(); 
                });
            }
        },
        error: function(xhr) {
            let errorMsg = xhr.responseJSON ? xhr.responseJSON.message : 'Terjadi kesalahan server';
            Swal.fire('Gagal!', errorMsg, 'error');
        }
    });
}

function openModalAO() {
    document.getElementById('modalTambahJadwalAO').style.display = 'block';
}

function closeModalAO() {
    document.getElementById('modalTambahJadwalAO').style.display = 'none';
    $('#formTambahJadwalAO')[0].reset();
    $('#txt_alamat, #txt_kol').text('-');
}

function openModalUbahJadwal() {
    const modal = document.getElementById('modalEditJadwalGlobal');
    if (modal) {
        modal.classList.add('show');
        document.body.style.overflow = 'hidden';
    }
}

function closeModalUbahJadwal() {
    const modal = document.getElementById('modalEditJadwalGlobal');
    if (modal) {
        modal.classList.remove('show');
        document.body.style.overflow = 'auto';
    }
}

$('#select_nasabah').on('change', function() {
    let selected = $(this).find(':selected');
    let no_angsuran = $(this).val();
    let nama = selected.data('nama');
    let alamat = selected.data('alamat');
    let kol = selected.data('kol');
    let kode = selected.data('kode'); // Tambahan baru
    let rekening = selected.data('rekening'); // Tambahan baru

    // Update Tampilan Info (Sesuai permintaan kamu: Kode, Rekening, Alamat)
    $('#txt_kode').text(kode || '-');
    $('#txt_rekening').text(rekening || '-');
    $('#txt_alamat').text(alamat || '-');

    // Update Hidden Input untuk dikirim via AJAX
    $('#no_angsuran').val(no_angsuran);
    $('#nama_nasabah').val(nama);
    $('#alamat_nasabah').val(alamat);
    $('#kol_nasabah').val(kol);
});

$(document).ready(function() {
        

        $(document).on('click', '#toggleCurrentPasswordAO', function() {
            const inputField = $('#current_password');
            const currentType = inputField.attr('type');

            const newType = (currentType === 'password') ? 'text' : 'password';
            inputField.attr('type', newType);
            
      
            $(this).toggleClass('fa-eye fa-eye-slash');
        });


        $(document).on('click', '#toggleNewPasswordAO', function() {
            const inputField = $('#new_password');
            const currentType = inputField.attr('type');
            
            const newType = (currentType === 'password') ? 'text' : 'password';
            inputField.attr('type', newType);
            
            $(this).toggleClass('fa-eye fa-eye-slash');
        });
    });

function updateSandiAO() {
    const current_password = $('#current_password').val();
    const new_password = $('#new_password').val();

    if (!current_password || !new_password) {
        Swal.fire('Peringatan', 'Harap isi semua kolom password!', 'warning');
        return;
    }

    handleAjaxSettings("{{ route('settings.sandi') }}", {
        current_password: current_password,
        new_password: new_password
    }, 'Kata sandi berhasil diperbarui!')
    .then(res => {
        if (res && res.success) {
            $('#current_password').val('');
            $('#new_password').val('');
        }
    });
}
</script>


</body>
</html>