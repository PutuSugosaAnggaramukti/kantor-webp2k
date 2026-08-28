<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - SIPANTAU P2K</title>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('css/karyawan.css') }}">

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>

   <div class="wrapper">
        <div class="sidebar">
            <h2>Menu</h2>

            {{-- 1. DASHBOARD UTAMA --}}
           <a href="javascript:void(0)" 
            onclick="window.location.href='/admin/dashboard'" 
            class="nav-item {{ request()->is('admin/dashboard*') ? 'active' : '' }}" 
            id="menu-dashboard">
                <i class="fa-solid fa-gauge-high"></i> Dashboard Utama
            </a>

            <hr style="border: 0.5px solid rgba(255,255,255,0.1); margin: 10px 15px;">

            {{-- 2. DATA TIM P2K --}}
            <a href="javascript:void(0)" 
                onclick="loadAdminPage('data-karyawan', this)" 
                class="nav-item {{ request()->is('admin/data-karyawan*') ? 'active' : '' }}" 
                id="menu-data-karyawan">
                <i class="fa-solid fa-users"></i> Data Tim P2K
            </a>

            {{-- 3. DATA KUNJUNGAN --}}
            <a href="javascript:void(0)" 
                onclick="loadAdminPage('data-kunjungan', this)" 
                class="nav-item {{ request()->is('admin/kunjungan*') ? 'active' : '' }}" 
                id="menu-data-kunjungan">
                <i class="fa-solid fa-clipboard-check"></i> Data Kunjungan
            </a>
            
            {{-- 4. DATA NASABAH --}}
            <a href="javascript:void(0)" 
                onclick="loadAdminPage('nasabah', this)" 
                class="nav-item {{ request()->is('admin/nasabah*') ? 'active' : '' }}" 
                id="menu-nasabah">
                <i class="fa-solid fa-address-card"></i> Data Nasabah
            </a>

            {{-- 5. PELAPORAN --}}
            <a href="javascript:void(0)" 
                onclick="loadAdminPage('pelaporan', this)" 
                class="nav-item {{ Request::is('admin/pelaporan*') ? 'active' : '' }}" 
                id="menu-pelaporan">
                <i class="fa-solid fa-file-signature"></i> 
                <span>Pelaporan</span>
            </a>

            {{-- 6. DOKUMEN --}}
            <a href="javascript:void(0)" 
                onclick="loadAdminPage('dokumen', this)" 
                class="nav-item {{ Request::is('admin/dokumen*') ? 'active' : '' }}" 
                id="menu-dokumen">
                <i class="fa-solid fa-file-word"></i> 
                <span>Dokumen</span>
            </a>

            {{-- 7. INPUT JADWAL --}}
            <a href="javascript:void(0)" 
                onclick="loadAdminPage('adm-kunjungan', this)" 
                class="nav-item {{ Request::is('admin/adm-kunjungan*') ? 'active' : '' }}" 
                id="menu-adm-kunjungan">
                <i class="fa-solid fa-calendar-plus"></i> Input Jadwal Kunjungan
            </a>

            {{-- 8. PENGATURAN ADMIN --}}
            <a href="javascript:void(0)" 
                onclick="loadAdminPage('pengaturan', this)" 
                class="nav-item {{ request()->is('admin/pengaturan*') ? 'active' : '' }}" 
                id="menu-pengaturan">
                <i class="fa-solid fa-gear"></i> Pengaturan
            </a>
        </div>

        <div class="main-container">
            <div class="header">
                <div class="logo-section">
                    <img src="{{ asset('assets/logo.png') }}" alt="Logo">
                    <span>SIPANTAU<br>P2K</span>
                </div>
                
                <div class="user-profile-container" style="position: relative;">
                    <div class="user-profile" onclick="toggleDropdown()" style="cursor: pointer; display: flex; align-items: center;">
                        <span>Admin</span>
                        <i class="fa-solid fa-chevron-down" style="margin-left: 8px; font-size: 12px; color: #666;"></i>
                    </div>

                    <div id="dropdownLogout" style="display: none; position: absolute; right: 0; top: 110%; width: 160px; background-color: #fff; border-radius: 12px; box-shadow: 0 8px 20px rgba(0,0,0,0.15); border: 1px solid #eee; z-index: 9999; overflow: hidden;">
                        <form action="{{ route('logout') }}" method="POST" id="logout-form">
                            @csrf
                            <a href="javascript:void(0)" onclick="confirmLogout()" style="display: flex; align-items: center; padding: 12px 15px; text-decoration: none; color: #e74c3c; font-weight: 700; font-size: 14px;">
                                <i class="fa-solid fa-right-from-bracket" style="margin-right: 10px;"></i> Logout
                            </a>
                        </form>
                        <a href="javascript:void(0)" onclick="transitionToAdminPage('pengaturan')" style="display: flex; align-items: center; padding: 12px 15px; text-decoration: none; color: #333; font-weight: 600; font-size: 14px; border-bottom: 1px solid #f5f5f5; transition: background 0.2s;">
                            <i class="fa-solid fa-user-gear" style="margin-right: 10px; color: #3b82f6;"></i> Pengaturan Akun
                        </a>
                    </div>
                </div>
            </div>

           <div class="main-content">
                <div class="content-padding">
                    <div id="main-content-area">
                        @if(isset($content))
                            {{-- Saat Refresh: Menampilkan HTML dari Controller --}}
                            {!! $content !!}
                        @else
                            {{-- Saat Akses Awal: Menampilkan Tabel Karyawan --}}
                            @include('admin.partials.karyawan_table', ['karyawan' => $karyawan ?? collect()])
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="main-footer">
        <p>SIPANTAU P2K 2026</p>
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

    // 2. Tentukan Menu Mana yang Harus Aktif
    let menuToActive = element;
    if (!menuToActive) {
        if (pageName.includes('pelaporan') || pageName.includes('laporan')) {
            menuToActive = document.getElementById('menu-pelaporan');
        } else if (pageName.includes('adm-kunjungan')) {
            menuToActive = document.getElementById('menu-adm-kunjungan');
        } else if (pageName.includes('kunjungan')) {
            menuToActive = document.getElementById('menu-data-kunjungan');
        } else if (pageName.includes('nasabah')) {
            menuToActive = document.getElementById('menu-nasabah');
        } else if (pageName.includes('dokumen')) {
            menuToActive = document.getElementById('menu-dokumen');
        } else if (pageName.includes('karyawan')) {
            menuToActive = document.getElementById('menu-data-karyawan');
        } else if (pageName.includes('pengaturan')) { 
            menuToActive = document.getElementById('menu-pengaturan');
        } 
        else {
            menuToActive = document.getElementById(`menu-${pageName}`);
        }
    }

    if (menuToActive) menuToActive.classList.add('active');

    contentArea.style.opacity = '0.3';
    
    let cleanName = pageName.replace('-content', '');
    let fetchUrl = `/admin/${cleanName}-content`;

    fetch(fetchUrl, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'text/html'
        }
    })
    .then(res => res.text())
    .then(html => {
        // --- BAGIAN YANG BERUBAH ---
        contentArea.innerHTML = html;
        contentArea.style.opacity = '1';
        
        // JALANKAN ULANG SCRIPT (Agar Tombol Export/Fitur JS muncul kembali)
        const scripts = contentArea.querySelectorAll("script");
        scripts.forEach(oldScript => {
            const newScript = document.createElement("script");
            // Salin atribut script (jika ada src, type, dll)
            Array.from(oldScript.attributes).forEach(attr => newScript.setAttribute(attr.name, attr.value));
            // Salin isi script-nya
            newScript.appendChild(document.createTextNode(oldScript.innerHTML));
            // Ganti script lama dengan yang baru agar dieksekusi browser
            oldScript.parentNode.replaceChild(newScript, oldScript);
        });
        // --- AKHIR PERUBAHAN ---

        history.pushState({page: cleanName}, "", `/admin/${cleanName}`);
    })
    .catch(err => {
        console.error("Gagal memuat:", err);
        contentArea.style.opacity = '1';
    });
};

document.addEventListener("DOMContentLoaded", function() {
    const urlParams = new URLSearchParams(window.location.search);
    const pageToLoad = urlParams.get('page');
    const currentPath = window.location.pathname;

    // 1. Logika penentuan Menu Active
    if (currentPath.includes('adm-kunjungan')) {
        setActiveMenuOnly('menu-adm-kunjungan');
    } else if (currentPath.includes('data-kunjungan')) {
        setActiveMenuOnly('menu-data-kunjungan');
    } else if (currentPath.includes('nasabah')) {
        setActiveMenuOnly('menu-nasabah');
    } else if (currentPath.includes('pelaporan')) {
        setActiveMenuOnly('menu-pelaporan');
    } else if (currentPath.includes('dokumen')) {
        setActiveMenuOnly('menu-dokumen');
    } else if (currentPath.includes('pengaturan')) {
        setActiveMenuOnly('menu-pengaturan');
        if (typeof window.loadAdminPage === 'function') {
            window.loadAdminPage('pengaturan', document.getElementById('menu-pengaturan'));
        }
    }
    else if (currentPath.includes('data-karyawan') || currentPath === '/admin') {
        setActiveMenuOnly('menu-data-karyawan');
    }

    // --- TAMBAHAN BARU: LOGIKA PENCARIAN (DEBOUNCE) ---
    let searchTimer;
    $(document).on('keyup', '#searchInput', function() {
        clearTimeout(searchTimer);
        let keyword = $(this).val();
        let currentPath = window.location.pathname; // Cek Sam lagi di menu mana

        searchTimer = setTimeout(function() {
            // 1. Jika Sam lagi di halaman NASABAH
            if (currentPath.includes('nasabah')) {
                let params = new URLSearchParams(window.location.search);
                let currentTab = params.get('tab') || '1'; 
                fetchNasabah(currentTab, keyword);
            } 
            
            // 2. Jika Sam lagi di halaman DOKUMEN
            else if (currentPath.includes('dokumen')) {
                fetchDokumen(keyword); 
            }

            else if (currentPath.includes('pelaporan')) {
                fetchPelaporan(keyword);
            }

            // 3. Jika Sam lagi di halaman DATA KARYAWAN (opsional jika butuh cari karyawan)
            else if (currentPath.includes('data-karyawan')) {
                // fetchKaryawan(keyword); // Jika ada fungsinya
            }
        }, 500); 
    });

    // --- FUNGSI KHUSUS PENCARIAN DOKUMEN ---
    function fetchDokumen(keyword) {
        const contentArea = $('#main-content-area');
        contentArea.css('opacity', '0.5');

        $.ajax({
            url: "/admin/dokumen-content", // Route khusus dokumen
            method: "GET",
            data: { search: keyword },
            success: function(data) {
                contentArea.html(data);
                contentArea.css('opacity', '1');
                
                // Update URL agar saat direfresh, hasil pencarian tetap nempel di URL
                let newUrl = window.location.pathname + '?search=' + encodeURIComponent(keyword);
                window.history.pushState({path: newUrl}, '', newUrl);
            },
            error: function(xhr) {
                contentArea.css('opacity', '1');
                console.error("Error Search Dokumen:", xhr.responseText);
            }
        });
    }

    $(document).on('keyup', '#searchInput', function() {
    clearTimeout(searchTimer);
    let keyword = $(this).val();
    let currentPath = window.location.pathname;

    searchTimer = setTimeout(function() {
        if (currentPath.includes('nasabah')) {
            fetchNasabah(null, keyword);
        } 
        else if (currentPath.includes('dokumen')) {
            fetchDokumen(keyword); 
        }
        else if (currentPath.includes('data-kunjungan')) {
            fetchRekapKunjungan(keyword);
        }
        else if (currentPath.includes('pelaporan')) {
            console.log("Mengirim request pencarian pelaporan..."); // Cek di console
            fetchPelaporan(keyword);
        }
    }, 500); 
});

// Fungsi penarik data Rekap Kunjungan
    function fetchRekapKunjungan(keyword) {
        const container = $('#isi-tabel-rekap');
        container.css('opacity', '0.5');

        $.ajax({
            url: "/admin/data-kunjungan-content", // Sesuaikan dengan route content kamu
            method: "GET",
            data: { search: keyword },
            success: function(data) {
                container.html(data);
                container.css('opacity', '1');
            }
        });
    }

   function fetchPelaporan(keyword) {
    // Kita pakai ID yang sudah kita buat di HTML tadi: isi-tabel-pelaporan
    const container = $('#isi-tabel-pelaporan'); 
    
    // Kasih efek loading di wadah tabelnya
    container.css('opacity', '0.5');

    $.ajax({
        url: "/admin/pelaporan", 
        method: "GET",
        data: { 
            search: keyword,
            ajax: true 
        },
        success: function(data) {
            // Tumpahkan datanya ke wadah yang benar
            container.html(data); 
            container.css('opacity', '1');
        },
        error: function(xhr) {
            container.css('opacity', '1');
            console.error("Error Search Pelaporan:", xhr.responseText);
        }
    });
}
    // --- AKHIR TAMBAHAN ---

    // 2. Logika Load AJAX
    if (pageToLoad) {
        const contentArea = document.getElementById('main-content-area');
        if (contentArea && contentArea.innerHTML.trim().length === 0) {
             loadAdminPage(pageToLoad);
        }
    }
});


function setActiveMenuOnly(menuId) {
    document.querySelectorAll('.nav-item').forEach(i => i.classList.remove('active'));
    const activeMenu = document.getElementById(menuId);
    if (activeMenu) {
        activeMenu.classList.add('active');
    }
}

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

$(document).on('change', '.status-select', function() {
    let statusPilihan = $(this).val(); 
    let id = $(this).data('id');
    let kode_ao = $(this).data('kode-ao'); 
    
    // Tampilkan loading sebentar biar user tahu proses sedang jalan
    $(this).css('opacity', '0.5');

    $.ajax({
        url: "/admin/kunjungan/update-status/" + id,
        type: 'PATCH',
        data: {
            _token: $('meta[name="csrf-token"]').attr('content'),
            status: statusPilihan,
            is_filled: 1
        },
        success: function(response) {
            // 1. Notifikasi Berhasil
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: 'Status kunjungan diperbarui.',
                timer: 1000,
                showConfirmButton: false
            });

            // 2. Refresh Konten (Penting agar data tidak balik ke default)
            if (kode_ao) {
                loadAdminPage('kunjungan-detail/' + kode_ao);
            } else {
                // Jika tidak ada kode_ao, refresh halaman saat ini saja
                const currentPath = window.location.pathname.replace('/admin/', '');
                loadAdminPage(currentPath);
            }
        },
        error: function(xhr) {
            console.error("Detail Error:", xhr.responseText);
            Swal.fire('Error', 'Gagal menyimpan ke database!', 'error');
            // Balikin opacity kalau error
            $('.status-select').css('opacity', '1');
        }
    });
});

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
                refreshNoAnggotaDropdown();
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

function fetchNasabah(tab = null, search = null) {
    // Gunakan nilai dari parameter, atau dari URL, atau default ke '1'
    let urlParams = new URLSearchParams(window.location.search);
    let currentTab = tab ?? urlParams.get('tab') ?? '1'; 
    let currentSearch = search ?? $('#searchInput').val() ?? '';

    const contentArea = $('#main-content-area');
    contentArea.css('opacity', '0.5');

    $.ajax({
        // Pastikan route name ini BENAR di web.php Anda
        url: "/admin/nasabah-content", 
        method: "GET",
        data: { 
            tab: currentTab, 
            search: currentSearch 
        },
        success: function(data) {
            contentArea.html(data);
            contentArea.css('opacity', '1');
            
            // Update URL agar sinkron
            let newUrl = window.location.pathname + '?tab=' + currentTab + '&search=' + encodeURIComponent(currentSearch);
            window.history.pushState({path: newUrl}, '', newUrl);
        },
        error: function(xhr) {
            contentArea.css('opacity', '1');
            console.error("Error Detail:", xhr.responseText);
        }
    });
}

function toggleAo(element) {
    // Mencari elemen konten setelah header accordion yang diklik
    const content = element.nextElementSibling;
    const icon = element.querySelector('.fa-chevron-down');

    if (content.style.display === "block") {
        content.style.display = "none";
        if(icon) icon.style.transform = "rotate(0deg)";
    } else {
        content.style.display = "block";
        if(icon) icon.style.transform = "rotate(180deg)";
    }
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

// FUNGSI PINDAH TAB (NASABAH REGULER vs HB)
function switchTab(type) {
    // 1. Ambil kata kunci pencarian yang mungkin sedang diketik user
    let currentSearch = $('#searchInput').val() || '';

    // 2. Panggil fungsi fetchNasabah yang sudah kita buat sebelumnya
    // Fungsi ini sudah menangani AJAX, Loading effect, dan Update URL
    fetchNasabah(type, currentSearch);
}

// FUNGSI AUTO-FILL NASABAH BERDASARKAN NO ANGSURAN

$(document).on('select2:unselect', '#dropdown_no_angsuran', function() {
    resetFormKunjungan();
});

// 2. FUNGSI RESET INPUTAN MODAL
function resetFormKunjungan() {
    $('#display_nama').val('');
    $('#display_alamat').val('');
    $('#display_kol').val('');
    $('#dropdown_no_angsuran').val('');
}

// 3. FUNGSI LOAD DAFTAR NOMOR ANGGOTA (Panggil saat modal dibuka)
   function openModalImportNasabah() {
    const modal = document.getElementById('importNasabahModal');
    modal.style.display = 'flex'; // Gunakan flex agar centering aktif
}

function closeModalImportNasabah() {
    const modal = document.getElementById('importNasabahModal');
    modal.style.display = 'none';
}

function openModalImportHB() {
    document.getElementById('modalImportHB').style.display = 'flex';
}

function closeModalImportHB() {
    document.getElementById('modalImportHB').style.display = 'none';
}

// Tambahan fungsi update nama file jika belum ada
function updateFileName(input, targetId) {
    const fileName = input.files[0] ? input.files[0].name : 'Klik atau tarik file ke sini';
    document.getElementById(targetId).textContent = fileName;
}

function updateFileName(input, targetId) {
    const fileNameSpan = document.getElementById(targetId);
    if (input.files && input.files[0]) {
        let name = input.files[0].name;
        // Menampilkan nama file dengan ikon agar lebih keren
        fileNameSpan.innerHTML = '<i class="fa-solid fa-file-circle-check"></i> ' + name;
        fileNameSpan.style.color = "#1e293b"; // Warna teks jadi lebih gelap saat file terpilih
    } else {
        fileNameSpan.innerHTML = "Klik atau tarik file ke sini";
        fileNameSpan.style.color = "#3b82f6";
    }
}

$(document).on('click', '.pagination a', function(e) {
    e.preventDefault();
    let url = $(this).attr('href');
    if (!url || url === '#') return;

    let container = $('#main-content-area');
    container.css('opacity', '0.5');

    $.ajax({
        url: url,
        type: 'GET',
        headers: { 'X-Requested-With': 'XMLHttpRequest' }, // Penting agar Controller tahu ini AJAX
        success: function(data) {
            // PERBAIKAN: Karena Controller merender partial, 'data' biasanya adalah HTML mentah.
            // Langsung masukkan ke container tanpa filter jika partial-nya tidak membungkus ID yang sama.
            container.html(data).css('opacity', '1');

            window.history.pushState(null, null, url);

            // Scroll halus ke atas area konten
            $('html, body').animate({ 
                scrollTop: container.offset().top - 100 
            }, 300);
        },
        error: function(xhr) {
            container.css('opacity', '1');
            console.error("Error pagination:", xhr.responseText);
        }
    });
});


function showDetailKunjungan(kode_ao, bulan = null, tahun = null, nama = null) {
    const contentArea = document.getElementById('main-content-area');
    if (!contentArea) return;

    // Ambil bulan & tahun dari URL jika tidak dikirim sebagai parameter
    const urlParams = new URLSearchParams(window.location.search);
    const bulanFinal = bulan || urlParams.get('bulan') || new Date().getMonth() + 1;
    const tahunFinal = tahun || urlParams.get('tahun') || new Date().getFullYear();
    const namaFinal = nama !== null ? nama : urlParams.get('nama');

    contentArea.style.opacity = '0.3';

    let url = `/admin/kunjungan-detail/${kode_ao}?bulan=${bulanFinal}&tahun=${tahunFinal}`;
    if (namaFinal && String(namaFinal).trim() !== '') {
        url += `&nama=${encodeURIComponent(String(namaFinal).trim())}`;
    }

    fetch(url, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'text/html'
        }
    })
    .then(res => res.text())
    .then(html => {
        contentArea.innerHTML = html;
        contentArea.style.opacity = '1';
        history.pushState({page: 'detail-kunjungan'}, "", url);
    })
    .catch(err => {
        console.error("Gagal memuat detail:", err);
        contentArea.style.opacity = '1';
        Swal.fire('Error', 'Gagal memuat halaman detail', 'error');
    });
}

$(document).on('submit', '#modalImport form', function(e) {
    e.preventDefault();
    let formData = new FormData(this);
    
    Swal.fire({ title: 'Memproses...', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });

    $.ajax({
        url: $(this).attr('action'),
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(res) {
            closeModalImport();
            Swal.fire('Berhasil!', res.message, 'success');
            loadAdminPage('adm-kunjungan'); // Refresh tabel
        },
        error: function(xhr) {
            Swal.fire('Error', 'Terjadi kesalahan saat import data', 'error');
        }
    });
});


function openModalImport() {
    const modal = document.getElementById('modalImport');
    if (modal) {
        modal.style.display = 'flex';
    } else {
        console.error("Elemen modalImport tidak ditemukan!");
    }
}


function closeModalImport() {
    const modal = document.getElementById('modalImport');
    if (modal) {
        modal.style.display = 'none';
    }
}

$(document).ready(function() {
        // Gunakan Event Delegation agar klik selalu terbaca meski halaman dimuat via AJAX
        $(document).off('click', '#toggleCurrentPassword').on('click', '#toggleCurrentPassword', function() {
            const input = $('#current_password');
            const type = input.attr('type') === 'password' ? 'text' : 'password';
            input.attr('type', type);
            
            // Toggle Class Ikon
            $(this).toggleClass('fa-eye fa-eye-slash');
        });

        $(document).off('click', '#toggleNewPassword').on('click', '#toggleNewPassword', function() {
            const input = $('#new_password');
            const type = input.attr('type') === 'password' ? 'text' : 'password';
            input.attr('type', type);
            
            // Toggle Class Ikon
            $(this).toggleClass('fa-eye fa-eye-slash');
        });
    });

function initPasswordToggle(toggleId, inputId) {
        const toggleIcon = document.getElementById(toggleId);
        const passwordInput = document.getElementById(inputId);

        if (toggleIcon && passwordInput) {
            toggleIcon.addEventListener('click', function() {
                // Toggle tipe input
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                
                // Toggle ikon mata
                this.classList.toggle('fa-eye');
                this.classList.toggle('fa-eye-slash');
            });
        }
    }

    initPasswordToggle('toggleCurrentPassword', 'current_password');
    initPasswordToggle('toggleNewPassword', 'new_password');

function updateSandiAdmin() {
        const current_password = $('#current_password').val();
        const new_password = $('#new_password').val();

        if (!current_password || !new_password) {
            Swal.fire('Peringatan', 'Harap isi semua kolom password!', 'warning');
            return;
        }

        handleAjaxSettings("{{ route('admin.settings.sandi') }}", {
            current_password: current_password,
            new_password: new_password
        }, 'Kata sandi berhasil diperbarui!')
        .then(res => {
            if (res && res.success) {
                $('#current_password').val('');
                $('#new_password').val('');
                $('#current_password').attr('type', 'password');
                $('#new_password').attr('type', 'password');
                $('#toggleCurrentPassword, #toggleNewPassword').removeClass('fa-eye-slash').addClass('fa-eye');
            }
        });
 }

function handleAjaxSettings(url, data, successMessage) {
    return $.ajax({
        url: url,
        method: "POST",
        data: {
            ...data,
            _token: "{{ csrf_token() }}"
        },
        success: function(response) {
            // SweetAlert muncul di sini
            if (response.success) {
                Swal.fire('Berhasil', response.message || successMessage, 'success');
            } else {
                Swal.fire('Gagal', response.message || 'Terjadi kesalahan', 'error');
            }
            // PENTING: Kembalikan response agar bisa dibaca oleh .then()
            return response;
        },
        error: function(xhr) {
            let errorMsg = xhr.responseJSON ? xhr.responseJSON.message : 'Terjadi kesalahan server.';
            Swal.fire('Error', errorMsg, 'error');
        }
    });
}


window.showVisitDetail = function(data) {
    console.log("Data yang diterima:", data); // Cek ini di console browser

    // --- 1. FOTO ---
    let fotoSource = '/assets/no_image.png';
    if (data.foto_kunjungan) {
        try {
            let fotos = JSON.parse(data.foto_kunjungan);
            let namaFoto = (Array.isArray(fotos) && fotos.length > 0) ? fotos[0] : data.foto_kunjungan;
            fotoSource = `/uploads/kunjungan/${namaFoto}`;
        } catch (e) {
            fotoSource = `/uploads/kunjungan/${data.foto_kunjungan}`;
        }
    }
    document.getElementById('view-foto').src = fotoSource;

    // --- 2. JAM (prioritas: waktu EXIF foto -> waktu realisasi kunjungan -> created_at) ---
    const jamSource = (data.foto_jam || data.tgl_realisasi || data.created_at || '');
    let jam = '--:--';
    if (jamSource) {
        const parts = jamSource.split(' ');
        if (parts.length >= 2 && parts[1]) {
            jam = parts[1].substring(0, 5);
        } else if (/^\d{1,2}:\d{2}/.test(jamSource)) {
            jam = jamSource.substring(0, 5);
        }
    }
    document.getElementById('view-jam').innerText = `Foto diambil pada jam: ${jam} WIB`;

   // --- 3. KOORDINAT ---
    const linkElem = document.getElementById('view-koordinat-link');
    const textElem = document.getElementById('view-koordinat');
    
    if (data.koordinat && data.koordinat !== '-') {
        // Membersihkan spasi atau karakter aneh, tapi tetap menyisakan angka, titik, koma, dan minus
        let latLng = data.koordinat.replace(/\s/g, ''); 
        
        // PERBAIKAN: Gunakan ${latLng} dengan simbol dollar agar variabel terbaca
        linkElem.href = `https://www.google.com/maps/search/?api=1&query=${latLng}`;
        
        // Menampilkan teks koordinat asli di bawah tombol/link (opsional agar user bisa copy)
        textElem.innerHTML = `<i class="fas fa-map-marker-alt"></i> Lihat di Google Maps<br><small style="color:#888; font-weight:normal;">(${latLng})</small>`;
        textElem.style.color = "#3498db";
    } else {
        linkElem.href = "#";
        textElem.innerText = "Tidak ada lokasi";
        textElem.style.color = "#999";
    }
    // --- 4. TANGGAL JANJI BAYAR ---
    document.getElementById('view-janji').innerText = data.tgl_janji_hasil || '-';

    // --- 5. NOMINAL ---
    let nominal = data.nominal_janji_hasil || 0;
    document.getElementById('view-nominal').innerText = new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0
    }).format(nominal);

    // --- 6. CATATAN ---
    document.getElementById('view-catatan').innerText = data.catatan_lapangan || 'Tidak ada catatan.';

    // Tampilkan Modal
    document.getElementById('modalDetailKunjungan').style.display = 'flex';
}

// Fungsi Tutup
window.closeVisitDetail = function() {
    document.getElementById('modalDetailKunjungan').style.display = 'none';
}

window.closeVisitDetail = function() {
    const modal = document.getElementById('modalDetailKunjungan');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto'; // Mengembalikan scroll bar halaman
    }
};

// Muat ulang halaman Detail Kunjungan via AJAX perubahan filter bulan/tahun.
// didefinisikan GLOBAL sehingga selalu tersedia walaupun partial di-inject via AJAX
// (showDetailKunjungan tidak mengeksekusi ulang <script> pada konten yang disuntikkan).
window.reloadDetailKunjungan = function () {
    const bulanEl = document.querySelector('select[name="bulan"]');
    const tahunEl = document.querySelector('select[name="tahun"]');
    const namaEl = document.querySelector('input[name="nama"]');
    const bulan = bulanEl ? bulanEl.value : '';
    const tahun = tahunEl ? tahunEl.value : '';
    const nama = namaEl ? namaEl.value : '';
    // Baca kode AO dari URL /admin/kunjungan-detail/{kode_ao}
    const pathParts = window.location.pathname.split('/');
    const rawKode = (pathParts.length > 1) ? pathParts[pathParts.length - 1] : '';
    const kodeAo = rawKode.replace('-content', '');
    if (typeof window.showDetailKunjungan === 'function') {
        window.showDetailKunjungan(kodeAo, bulan, tahun, nama);
    } else if (typeof window.loadAdminPage === 'function') {
        // Jika belum berada di halaman detail, arahkan ke sana
        window.loadAdminPage('kunjungan-detail/' + encodeURIComponent(kodeAo));
    }
};

// Delegasi event: filter bulan/tahun & pencarian nama pada halaman Detail Kunjungan dipasang
// secara global sehingga selalu berfungsi walaupun partial di-inject via AJAX
// (menghindari ReferenceError dari onchange inline jika global tidak tersedia).
document.addEventListener('change', function (e) {
    const target = e.target;
    if (target && target.matches && target.matches('select[name="bulan"], select[name="tahun"], input[name="nama"]')) {
        const form = target.closest('form');
        if (form && form.id === 'formFilterDetailKunjungan') {
            e.preventDefault();
            window.reloadDetailKunjungan();
        }
    }
});

// Pencarian nama nasabah otomatis saat mengetik (debounce 400ms)
let namaSearchTimer = null;
document.addEventListener('input', function (e) {
    const target = e.target;
    if (target && target.matches && target.matches('input[name="nama"]')) {
        const form = target.closest('form');
        if (form && form.id === 'formFilterDetailKunjungan') {
            clearTimeout(namaSearchTimer);
            namaSearchTimer = setTimeout(() => window.reloadDetailKunjungan(), 400);
        }
    }
});

// Opsional: Menutup modal jika user klik di luar area putih modal
window.onclick = function(event) {
    const modal = document.getElementById('modalDetailKunjungan');
    if (event.target == modal) {
        closeVisitDetail();
    }
}

// === EDIT & DELETE KUNJUNGAN (Detail Kunjungan Admin) ===
window.editKunjungan = function(id) {
    $.ajax({
        url: '/admin/kunjungan/' + id,
        type: 'GET',
        success: function(data) {
            $('#editKunj_id').val(data.id);
            $('#editKunj_status').val(data.status || 'Menunggu Pembayaran');
            $('#editKunj_tgl_janji').val(data.tgl_janji_bayar || '');
            $('#editKunj_nominal').val(data.nominal_janji_bayar || '');
            $('#editKunj_catatan').val(data.catatan || '');
            document.getElementById('modalEditKunjunganAdmin').style.display = 'flex';
        },
        error: function() {
            Swal.fire('Error', 'Gagal memuat data kunjungan.', 'error');
        }
    });
};

window.closeModalEditKunjunganAdmin = function() {
    document.getElementById('modalEditKunjunganAdmin').style.display = 'none';
};

$(document).on('submit', '#formEditKunjunganAdmin', function(e) {
    e.preventDefault();
    var id = $('#editKunj_id').val();
    var token = $('meta[name="csrf-token"]').attr('content');

    $.ajax({
        url: '/admin/kunjungan/' + id,
        type: 'PUT',
        data: {
            _token: token,
            _method: 'PUT',
            status: $('#editKunj_status').val(),
            tgl_janji_bayar: $('#editKunj_tgl_janji').val(),
            nominal_janji_bayar: $('#editKunj_nominal').val(),
            catatan: $('#editKunj_catatan').val()
        },
        success: function(resp) {
            Swal.fire({ icon: 'success', title: 'Berhasil!', text: resp.message, timer: 1200, showConfirmButton: false });
            closeModalEditKunjunganAdmin();
            window.reloadDetailKunjungan();
        },
        error: function(xhr) {
            var msg = xhr.responseJSON ? xhr.responseJSON.message : 'Terjadi kesalahan.';
            Swal.fire('Error', msg, 'error');
        }
    });
});

window.hapusDataKunjungan = function(id, tipe) {
    Swal.fire({
        title: 'Yakin hapus?',
        text: tipe === 'kunjungan' ? 'Data kunjungan ini akan dihapus permanen.' : 'Data jadwal ini akan dihapus permanen.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal'
    }).then(function(result) {
        if (result.isConfirmed) {
            $.ajax({
                url: '/admin/kunjungan/' + id,
                type: 'DELETE',
                data: { _token: $('meta[name="csrf-token"]').attr('content') },
                success: function(resp) {
                    Swal.fire({ icon: 'success', title: 'Berhasil!', text: resp.message, timer: 1200, showConfirmButton: false });
                    window.reloadDetailKunjungan();
                },
                error: function(xhr) {
                    var msg = xhr.responseJSON ? xhr.responseJSON.message : 'Gagal menghapus.';
                    Swal.fire('Error', msg, 'error');
                }
            });
        }
    });
};


</script>
</body>
</html>