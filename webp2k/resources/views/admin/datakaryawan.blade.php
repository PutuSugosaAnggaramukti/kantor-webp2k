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
     document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const targetPage = urlParams.get('page');
    
    document.querySelectorAll('.nav-item').forEach(nav => nav.classList.remove('active'));

    if (targetPage && targetPage !== 'data-karyawan') {
        loadAdminPage(targetPage); 
    } else {
        const menuKaryawan = document.getElementById('menu-data-karyawan');
        if (menuKaryawan) {
            menuKaryawan.classList.add('active');
        }
    }
});

function loadAdminPage(pageName) {
    const contentArea = document.getElementById('main-content-area'); 
    
    if(!contentArea) {
        console.error("ID main-content-area tidak ditemukan!");
        return;
    }

    contentArea.style.opacity = '0.3';

    fetch(`/admin/${pageName}-content`) 
        .then(response => {
            if (!response.ok) throw new Error('Route tidak ditemukan atau server error');
            return response.text();
        })
        .then(html => {
            contentArea.innerHTML = html;
            contentArea.style.opacity = '1';

            // --- TAMBAHAN LOGIKA FINISHING UNTUK SIDEBAR ---
            // 1. Cari semua elemen menu sidebar
            const allNavItems = document.querySelectorAll('.nav-item');
            
            // 2. Hapus class 'active' dari semua menu
            allNavItems.forEach(nav => nav.classList.remove('active'));

            // 3. Tambahkan class 'active' ke menu yang sesuai dengan pageName
            // Pastikan ID di HTML sidebar kamu adalah 'menu-data-karyawan', 'menu-nasabah', dll.
            const activeMenu = document.getElementById(`menu-${pageName}`);
            if (activeMenu) {
                activeMenu.classList.add('active');
            }
            // ----------------------------------------------
        })
        .catch(error => {
            console.error('Error:', error);
            contentArea.style.opacity = '1';
            contentArea.innerHTML = `<div class="alert alert-danger">Gagal memuat halaman ${pageName}</div>`;
        });
}

$.ajaxSetup({
    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
});
var isProcessingKaryawan = false;

// --- 2. CORE FUNCTIONS ---
window.loadAdminPage = function(pageName, element) {
    const contentArea = document.getElementById('main-content-area');
    if (!contentArea) return;
    document.querySelectorAll('.nav-item').forEach(i => i.classList.remove('active'));
    let menuToActive = element || document.getElementById(`menu-${pageName}`) || (pageName.includes('nasabah-detail') ? document.getElementById('menu-nasabah') : null);
    if (menuToActive) menuToActive.classList.add('active');

    fetch(`/admin/${pageName}-content`)
        .then(res => res.text())
        .then(html => {
            contentArea.innerHTML = html;
            history.pushState({page: pageName}, "", `/admin/${pageName.replace('-content', '')}`);
        })
        .catch(err => console.error("Gagal memuat:", err));
};

// --- 3. MODAL KARYAWAN (TAMBAH, EDIT, DETAIL) ---
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

// --- 4. MODAL EXPORT & FILTER (SOLUSI MASALAHMU) ---

// Untuk Modal Export di Halaman Nasabah
function openModalExportNasabah() { 
    document.getElementById('modalExportNasabah').style.display = 'block'; 
}
function closeModalExportNasabah() { 
    document.getElementById('modalExportNasabah').style.display = 'none'; 
}

// Untuk Modal Export di Halaman Pelaporan (Tadi ini yang tidak muncul)
function openModalExportPelaporan() { 
    const modal = document.getElementById('modalExportPelaporan');
    modal.style.display = 'flex'; // Pakai flex agar align-items center bekerja
}
function closeModalExportPelaporan() { 
    document.getElementById('modalExportPelaporan').style.display = 'none'; 
}

// Untuk Modal Filter (Cari Data)
function openModalFilter() { 
    document.getElementById('modalFilterNasabah').style.display = 'block'; 
}
function closeModalFilter() { 
    document.getElementById('modalFilterNasabah').style.display = 'none'; 
}

// --- 5. MODAL KUNJUNGAN ---
function openModalKunjungan() { 
    $('#modalTambahKunjungan').css('display', 'flex'); 
    refreshKaryawanDropdown(); 
}
function closeModalKunjungan() { 
    $('#modalTambahKunjungan').hide(); 
    $('#formTambahKunjungan')[0].reset(); 
}

// --- 6. GLOBAL CLICK MONITOR (GABUNGAN SEMUA) ---
window.onclick = function(event) {
    const modalIDs = [
        'modalTambahKaryawan', 'modalEditKaryawan', 'modalDetailKaryawan', 
        'modalExportNasabah', 'modalFilterNasabah', 'modalExportPelaporan', 
        'modalTambahKunjungan'
    ];

    modalIDs.forEach(id => {
        const m = document.getElementById(id);
        if (event.target == m) {
            // Khusus modal yang pakai flex, set none. Yang lain juga none.
            m.style.display = 'none'; 
        }
    });

    if (!event.target.closest('.user-profile-container')) {
        const dd = document.getElementById('dropdownLogout');
        if (dd) dd.style.display = 'none';
    }
};

function refreshKaryawanDropdown() {
    $.ajax({
        url: "/admin/get-karyawan-list",
        type: "GET",
        success: function(response) {
            let dropdown = $('select[name="karyawan_id"]');
            dropdown.empty().append('<option value="">-- Pilih AO --</option>');
            response.forEach(k => dropdown.append(`<option value="${k.id}">${k.nama}</option>`));
        }
    });
}

// --- 5. FILTER AJAX (NASABAH ONLY) ---
function applyFilterAJAX(event) {
    if (event) event.preventDefault();
    const targetBody = document.getElementById('isi-tabel-nasabah');
    if (!targetBody) return; // Pengaman agar tidak error di halaman pelaporan

    const tglAwal = document.getElementById('tgl_awal_filter').value;
    const tglAkhir = document.getElementById('tgl_akhir_filter').value;

    if (!tglAwal || !tglAkhir) { alert('Silakan pilih kedua tanggal!'); return; }

    targetBody.style.opacity = '0.5';
    const url = `{{ route('admin.nasabah.filter') }}?tanggal_awal=${tglAwal}&tanggal_akhir=${tglAkhir}`;
    
    fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
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

// --- 6. SINGLE WINDOW ONCLICK (GABUNGAN SEMUA) ---
window.onclick = function(event) {
    // Tutup Modal jika klik di luar box
    const modalIDs = ['modalExportPelaporan', 'modalFilterNasabah', 'modalExportNasabah', 'modalDetailKaryawan', 'modalEditKaryawan', 'modalTambahKunjungan', 'modalTambahKaryawan'];
    
    modalIDs.forEach(id => {
        const m = document.getElementById(id);
        if (event.target == m) m.style.display = 'none';
    });

    // Tutup Dropdown Logout
    if (!event.target.closest('.user-profile-container')) {
        const dd = document.getElementById('dropdownLogout');
        if (dd) dd.style.display = 'none';
    }
};

    </script>
</body>
</html>