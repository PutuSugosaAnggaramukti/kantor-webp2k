<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Sistem Informasi P2K</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/karyawan.css') }}">

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>


    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>

   <div class="wrapper">
        <div class="sidebar">
            <h2>Menu</h2>
            <a href="javascript:void(0)" onclick="loadAdminPage('data-karyawan', this)" class="nav-item" id="menu-data-karyawan">
                <i class="fa-solid fa-users"></i> Data Karyawan
            </a>

            <a href="javascript:void(0)" 
                onclick="loadAdminPage('data-kunjungan', this)" class="nav-item {{ request()->is('admin/kunjungan*') ? 'active' : '' }}" id="menu-data-kunjungan">
                <i class="fa-solid fa-clipboard-check"></i> Data Kunjungan
            </a>
            
            <a href="javascript:void(0)" 
                onclick="loadAdminPage('nasabah', this)" 
                class="nav-item {{ request()->is('admin/nasabah*') ? 'active' : '' }}" id="menu-nasabah">
                <i class="fa-solid fa-address-card"></i> Data Nasabah
            </a>

           <a href="javascript:void(0)" 
            onclick="loadAdminPage('pelaporan', this)" class="nav-item {{ Request::is('admin/pelaporan*') ? 'active' : '' }}" id="menu-pelaporan">
                <i class="fa-solid fa-file-signature"></i> 
                <span>Pelaporan</span>
            </a>

           <a href="javascript:void(0)" onclick="loadAdminPage('dokumen', this)" class="nav-item" id="menu-dokumen">
                <i class="fa-solid fa-file-word"></i> 
                <span>Dokumen</span>
            </a>

            <a href="javascript:void(0)" onclick="loadAdminPage('adm-kunjungan', this)" class="nav-item" id="menu-adm-kunjungan">
                <i class="fa-solid fa-calendar-plus"></i> Input Jadwal Kunjungan
            </a>
        </div>

        <div class="main-container">
            <div class="header">
                <div class="logo-section">
                    <img src="{{ asset('assets/logo.png') }}" alt="Logo">
                    <span>Sistem Informasi<br>P2K</span>
                </div>
                
                <div class="user-profile-container" style="position: relative;">
                    <div class="user-profile" onclick="toggleDropdown()" style="cursor: pointer; display: flex; align-items: center;">
                        <span>Admin</span>
                        <img src="https://i.pravatar.cc/150?u=admin" alt="User">
                        <i class="fa-solid fa-chevron-down" style="margin-left: 8px; font-size: 12px; color: #666;"></i>
                    </div>

                    <div id="dropdownLogout" style="display: none; position: absolute; right: 0; top: 110%; width: 160px; background-color: #fff; border-radius: 12px; box-shadow: 0 8px 20px rgba(0,0,0,0.15); border: 1px solid #eee; z-index: 9999; overflow: hidden;">
                        <form action="{{ route('logout') }}" method="POST" id="logout-form">
                            @csrf
                            <a href="javascript:void(0)" onclick="confirmLogout()" style="display: flex; align-items: center; padding: 12px 15px; text-decoration: none; color: #e74c3c; font-weight: 700; font-size: 14px;">
                                <i class="fa-solid fa-right-from-bracket" style="margin-right: 10px;"></i> Logout
                            </a>
                        </form>
                    </div>
                </div>
            </div>

            <div class="main-content">
                <div class="content-padding">
                    <div id="main-content-area">
                        @include('admin.partials.karyawan_table') 
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="main-footer">
        <p>Sistem Aplikasi P2K</p>
    </footer>

    @include('admin.partials.modals')

   <script>
// 1. GLOBAL SETUP
$.ajaxSetup({
    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
});

// 2. CORE NAVIGATION (AJAX Page Loader)
window.loadAdminPage = function(pageName, element) {
    const contentArea = document.getElementById('main-content-area');
    if (!contentArea) return;

    // 1. Reset class active dari semua menu
    document.querySelectorAll('.nav-item').forEach(i => i.classList.remove('active'));

    // 2. Tentukan Menu Mana yang Harus Aktif (Prioritas Berurutan)
    let menuToActive = element;

    if (!menuToActive) {
        // CEK PELAPORAN DULU (Agar tidak tertabrak kata 'nasabah' di halaman detail)
        if (pageName.includes('pelaporan') || pageName.includes('laporan')) {
            menuToActive = document.getElementById('menu-pelaporan');
        } 
        // Cek Input Jadwal (Agar tidak tertukar dengan Data Kunjungan)
        else if (pageName.includes('adm-kunjungan')) {
            menuToActive = document.getElementById('menu-adm-kunjungan');
        }
        // Cek Data Kunjungan
        else if (pageName.includes('kunjungan')) {
            menuToActive = document.getElementById('menu-data-kunjungan');
        }
        // Cek Nasabah
        else if (pageName.includes('nasabah')) {
            menuToActive = document.getElementById('menu-nasabah');
        }
        // Cek Dokumen
        else if (pageName.includes('dokumen')) {
            menuToActive = document.getElementById('menu-dokumen');
        }
        // Cek Karyawan
        else if (pageName.includes('karyawan')) {
            menuToActive = document.getElementById('menu-data-karyawan');
        }
        else {
            menuToActive = document.getElementById(`menu-${pageName}`);
        }
    }

    // 3. Tambahkan class active
    if (menuToActive) {
        menuToActive.classList.add('active');
    }

    // --- Proses Fetch AJAX ---
    contentArea.style.opacity = '0.3';
    
    // Logika URL Fetch agar dinamis terhadap route yang memiliki param atau tidak
    let fetchUrl = pageName.includes('-content') ? `/admin/${pageName}` : `/admin/${pageName}-content`;

    fetch(fetchUrl)
        .then(res => res.text())
        .then(html => {
            contentArea.innerHTML = html;
            contentArea.style.opacity = '1';
            
            // Push State untuk update URL di browser
            let displayUrl = pageName.replace('-content', '');
            history.pushState({page: pageName}, "", `/admin/${displayUrl}`);
        })
        .catch(err => {
            console.error("Gagal memuat:", err);
            contentArea.style.opacity = '1';
        });
};

document.addEventListener("DOMContentLoaded", function() {
    // 1. Ambil parameter 'page' dari URL browser
    const urlParams = new URLSearchParams(window.location.search);
    const pageToLoad = urlParams.get('page');

    // 2. Jika ada parameter page (misal: ?page=nasabah), jalankan loadAdminPage
    if (pageToLoad) {
        // Beri sedikit delay agar DOM siap
        setTimeout(() => {
            if (typeof window.loadAdminPage === 'function') {
                window.loadAdminPage(pageToLoad);
            }
        }, 100);
    }
});

// 3. MODAL KONTROL (Tambah, Edit, Detail)
function openModalTambah() { document.getElementById('modalTambahKaryawan').style.display = 'flex'; }
function closeModalTambah() { document.getElementById('modalTambahKaryawan').style.display = 'none'; }

function openModalEdit(id) {
    fetch(`/admin/karyawan/${id}/edit`, { headers: { 'Accept': 'application/json' } })
        .then(res => res.json())
        .then(data => {
            document.getElementById('edit_kode_ao').value = data.kode_ao;
            document.getElementById('edit_nama').value = data.nama;
            document.getElementById('edit_username').value = data.username;
            document.getElementById('edit_status').value = data.status;
            document.getElementById('formEditKaryawan').action = `/admin/karyawan/${id}`;
            document.getElementById('modalEditKaryawan').style.display = 'flex';
        });
}
function closeModalEdit() { document.getElementById('modalEditKaryawan').style.display = 'none'; }

function openModalDetail(id) {
    fetch(`/admin/karyawan/${id}`, { headers: { 'Accept': 'application/json' } })
        .then(res => res.json())
        .then(data => {
            document.getElementById('det_kode_ao').value = data.kode_ao;
            document.getElementById('det_nama').value = data.nama;
            document.getElementById('det_username').value = data.username;
            document.getElementById('det_status').value = data.status;
            document.getElementById('modalDetailKaryawan').style.display = 'flex';
        });
}
function closeModalDetail() { document.getElementById('modalDetailKaryawan').style.display = 'none'; }

// --- MODAL KUNJUNGAN ---
function openModalKunjungan() {
    // 1. Panggil fungsi untuk mengambil daftar karyawan terbaru
    refreshKaryawanDropdown();
    refreshNoAnggotaDropdown();
    
    // 2. Tampilkan modal dengan flex agar posisi di tengah
    const modal = document.getElementById('modalTambahKunjungan');
    if (modal) {
        modal.style.display = 'flex';
    }
}

function closeModalKunjungan() {
    const modal = document.getElementById('modalTambahKunjungan');
    if (modal) {
        modal.style.display = 'none';
        // Reset form dan field readonly
        document.getElementById('formTambahKunjungan').reset();
        resetFormKunjungan(); 
    }
}

// --- MODAL EXPORT NASABAH ---
function openModalExportNasabah() {
    const modal = document.getElementById('modalExportNasabah');
    if (modal) {
        modal.style.display = 'block'; 
    }
}

function closeModalExportNasabah() {
    const modal = document.getElementById('modalExportNasabah');
    if (modal) {
        modal.style.display = 'none';
    }
}

// --- MODAL FILTER NASABAH (Jika diperlukan) ---
function openModalFilter() {
    const modal = document.getElementById('modalFilterNasabah');
    if (modal) {
        modal.style.display = 'flex';
    }
}

function closeModalFilter() {
    const modal = document.getElementById('modalFilterNasabah');
    if (modal) {
        modal.style.display = 'none';
    }
}

// --- MODAL EXPORT PELAPORAN ---
function openModalExportPelaporan() {
    const modal = document.getElementById('modalExportPelaporan');
    if (modal) {
        modal.style.display = 'flex'; // Menggunakan flex agar rata tengah jika CSS-mu mendukung
    }
}

function closeModalExportPelaporan() {
    const modal = document.getElementById('modalExportPelaporan');
    if (modal) {
        modal.style.display = 'none';
    }
}

function openModalTambahNasabah() {
    const modal = document.getElementById('modalTambahNasabah');
    if (modal) {
        modal.style.display = 'flex'; 
        console.log("Modal Nasabah Terbuka");
    } else {
        console.error("Gagal! Elemen modalTambahNasabah tidak ditemukan di DOM halaman ini.");
    }
}

function closeModalTambahNasabah() {
    const modal = document.getElementById('modalTambahNasabah');
    if (modal) {
        modal.style.display = 'none';
    }
}

$(document).on('submit', '#modalTambahNasabah form', function(e) {
    e.preventDefault();
    let form = $(this);
    let btnSave = form.find('button[type="submit"]');

    btnSave.prop('disabled', true).html('Menyimpan...');
    
    $.ajax({
        type: "POST",
        url: form.attr('action'),
        data: form.serialize(),
        success: function(response) {
            closeModalTambahNasabah();
            form.trigger('reset');
            Swal.fire({ icon: 'success', title: 'Berhasil!', text: 'Nasabah berhasil ditambahkan', timer: 2000, showConfirmButton: false });
            loadAdminPage('nasabah'); 
        },
        error: function(xhr) {
            let errorMessage = 'Terjadi kesalahan sistem (Error ' + xhr.status + ')';
            
            // Ambil pesan error dari Laravel secara dinamis
            if (xhr.responseJSON && xhr.responseJSON.errors) {
                errorMessage = Object.values(xhr.responseJSON.errors).flat().join('<br>');
            } else if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            }

            Swal.fire({ icon: 'error', title: 'Oops!', html: errorMessage });
        },
        complete: function() {
            btnSave.prop('disabled', false).html('Simpan Data');
        }
    });
});

// 4. HANDLING SUBMIT (Hanya Satu Handler Utama)
document.addEventListener('submit', function(e) {
    if (e.target && e.target.id === 'formTambahKaryawan') {
        e.preventDefault();
        const btnSave = e.target.querySelector('.btn-save');
        
        if (btnSave.disabled) return; // Kunci agar tidak double post

        btnSave.disabled = true;
        btnSave.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';

        fetch("{{ route('karyawan.store') }}", {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: new FormData(e.target)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire({ icon: 'success', title: 'Berhasil!', text: data.message, timer: 2000, showConfirmButton: false });
                e.target.reset();
                closeModalTambah();
                loadAdminPage('data-karyawan');
            } else {
                let errors = Object.values(data.errors).flat().join('<br>');
                Swal.fire({ icon: 'error', title: 'Oops...', html: errors });
            }
        })
        .catch(err => {
            console.error(err);
            Swal.fire('Error', 'Terjadi kesalahan sistem', 'error');
        })
        .finally(() => {
            btnSave.disabled = false;
            btnSave.innerHTML = 'Save';
        });
    }
});

document.addEventListener('submit', function(e) {
    if (e.target && e.target.id === 'formTambahKunjungan') {
        e.preventDefault();

        const btnSave = e.target.querySelector('button[type="submit"]');
        if (btnSave.disabled) return;

        btnSave.disabled = true;
        btnSave.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';

        // Sesuaikan dengan grouping route: /admin + /datakunjungan/store
        fetch("/admin/datakunjungan/store", { 
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: new FormData(e.target)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire({ 
                    icon: 'success', 
                    title: 'Berhasil!', 
                    text: data.message, 
                    timer: 2000, 
                    showConfirmButton: false 
                });
                e.target.reset();
                closeModalKunjungan();
                // Memanggil kembali halaman konten kunjungan agar tabel terupdate
                loadAdminPage('adm-kunjungan'); 
            } else {
                let errors = data.errors ? Object.values(data.errors).flat().join('<br>') : (data.message || 'Gagal menyimpan');
                Swal.fire({ icon: 'error', title: 'Oops...', html: errors });
            }
        })
        .catch(err => {
            console.error(err);
            Swal.fire('Error', 'Terjadi kesalahan sistem atau Route tidak ditemukan', 'error');
        })
        .finally(() => {
            btnSave.disabled = false;
            btnSave.innerHTML = 'Simpan';
        });
    }
});

// 5. GLOBAL CLICK MONITOR (Menutup Modal & Dropdown Logout)
window.onclick = function(event) {
    const modalIDs = ['modalExportPelaporan', 'modalFilterNasabah', 'modalExportNasabah', 'modalDetailKaryawan', 'modalEditKaryawan', 'modalTambahKunjungan', 'modalTambahKaryawan','modalTambahKunjungan'
        ,'modalExportNasabah','modalFilter','modalExportPelaporan','modalTambahNasabah'];
    
    modalIDs.forEach(id => {
        const m = document.getElementById(id);
        if (m && event.target == m) m.style.display = 'none';
    });

    if (!event.target.closest('.user-profile-container')) {
        const dd = document.getElementById('dropdownLogout');
        if (dd) dd.style.display = 'none';
    }
};

// 6. FUNGSI FILTER & DROPDOWN
function applyFilterAJAX(event) {
    if (event) event.preventDefault();
    const targetBody = document.getElementById('isi-tabel-nasabah');
    if (!targetBody) return;

    const tglAwal = document.getElementById('tgl_awal_filter').value;
    const tglAkhir = document.getElementById('tgl_akhir_filter').value;

    if (!tglAwal || !tglAkhir) { Swal.fire('Peringatan', 'Pilih kedua tanggal!', 'warning'); return; }

    targetBody.style.opacity = '0.5';
    fetch(`{{ route('admin.nasabah.filter') }}?tanggal_awal=${tglAwal}&tanggal_akhir=${tglAkhir}`, { 
        headers: { 'X-Requested-With': 'XMLHttpRequest' } 
    })
    .then(res => res.text())
    .then(html => {
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');
        targetBody.innerHTML = doc.getElementById('isi-tabel-nasabah').innerHTML;
        targetBody.style.opacity = '1';
        closeModalFilter();
    })
    .catch(err => { console.error(err); targetBody.style.opacity = '1'; });
}

function refreshKaryawanDropdown() {
    $.get("/admin/get-karyawan-list", function(response) {
        let dropdown = $('#selectKaryawan'); // Pakai ID agar lebih spesifik
        dropdown.empty().append('<option value="">-- Pilih AO --</option>');
        response.forEach(k => {
            dropdown.append(`<option value="${k.id}">${k.nama}</option>`);
        });
    });
}

function toggleDropdown() {
    const dd = document.getElementById('dropdownLogout');
    if (dd) dd.style.display = (dd.style.display === 'none' || dd.style.display === '') ? 'block' : 'none';
}

function confirmLogout() {
    const dd = document.getElementById('dropdownLogout');
    if (dd) dd.style.display = 'none';

    Swal.fire({
        title: 'Yakin ingin keluar?',
        text: "Anda akan diarahkan kembali ke halaman login.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e74c3c', // Warna merah sesuai tema logout
        cancelButtonColor: '#bdc3c7',
        confirmButtonText: 'Ya, Keluar!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            // Cari form berdasarkan ID dan submit
            const logoutForm = document.getElementById('logout-form');
            if (logoutForm) {
                logoutForm.submit();
            } else {
                // Pengaman jika form tidak ditemukan (opsional)
                console.error("Form logout tidak ditemukan!");
            }
        }
    });
}

// FUNGSI AUTO-FILL NASABAH BERDASARKAN NO ANGSURAN
// 1. FUNGSI AUTO-FILL SAAT DROPDOWN DIPILIH
$(document).on('change', '#dropdown_no_angsuran', function() {
    let noAngsuran = $(this).val();
    
    if (noAngsuran) {
        $('#display_nama').val('Memuat data...');

        $.ajax({
            url: '/admin/get-nasabah/' + noAngsuran,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $('#display_nama').val(response.data.nasabah);
                    $('#display_alamat').val(response.data.alamat);
                    $('#display_kol').val(response.data.kol);
                } else {
                    Swal.fire('Info', 'Data nasabah tidak ditemukan', 'info');
                    resetFormKunjungan();
                }
            },
            error: function() {
                Swal.fire('Error', 'Gagal menghubungi server', 'error');
                resetFormKunjungan();
            }
        });
    } else {
        resetFormKunjungan();
    }
});

// 2. FUNGSI RESET INPUTAN MODAL
function resetFormKunjungan() {
    $('#display_nama').val('');
    $('#display_alamat').val('');
    $('#display_kol').val('');
    $('#dropdown_no_angsuran').val('');
}

// 3. FUNGSI LOAD DAFTAR NOMOR ANGGOTA (Panggil saat modal dibuka)
function refreshNoAnggotaDropdown() {
    $.get("/admin/get-daftar-no-anggota", function(data) {
        let dropdown = $('#dropdown_no_angsuran');
        dropdown.empty().append('<option value="">-- Pilih No. Anggota --</option>');
        
        if (data && data.length > 0) {
            data.forEach(n => {
                dropdown.append(`<option value="${n.no_angsuran}">${n.no_angsuran} - ${n.nasabah}</option>`);
            });
            console.log("Data nasabah berhasil dimuat ke dropdown");
        }
    }).fail(function(xhr) {
        console.error("Gagal memuat daftar nasabah:", xhr.statusText);
    });
}

    function openModalImportNasabah() {
        const modal = document.getElementById('importNasabahModal');
        if (modal) {
            modal.style.display = 'flex';
        }
    }

    function closeModalImportNasabah() {
        const modal = document.getElementById('importNasabahModal');
        if (modal) {
            modal.style.display = 'none';
        }
    }

</script>
</body>
</html>