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
            
            <a href="javascript:void(0)" onclick="loadAdminPage('data-karyawan', this)" class="nav-item">
                <i class="fa-solid fa-users"></i> Data Karyawan
            </a>

            <a href="javascript:void(0)" 
                onclick="loadAdminPage('adm-kunjungan', this)" 
                class="nav-item {{ request()->is('admin/kunjungan*') ? 'active' : '' }}">
                <i class="fa-solid fa-clipboard-check"></i> Data Kunjungan
            </a>
            
            <a href="javascript:void(0)" 
                onclick="loadAdminPage('nasabah', this)" 
                class="nav-item {{ request()->is('admin/nasabah*') ? 'active' : '' }}" id="menu-nasabah">
                <i class="fa-solid fa-address-card"></i> Data Nasabah
            </a>

            <a href="javascript:void(0)" 
                onclick="loadAdminPage('pelaporan', this)" 
                class="nav-item {{ request()->is('pelaporan*') ? 'active' : '' }}" id="menu-pelaporan">
                <i class="fa-solid fa-file-signature"></i> Pelaporan
            </a>

            <a href="javascript:void(0)" 
                onclick="loadAdminPage('dokumen', this)" 
                class="nav-item" id="menu-dokumen">
                <i class="fa-solid fa-file-lines"></i> Dokumen
            </a>

            <a href="javascript:void(0)" 
                onclick="loadAdminPage('adm-kunjungan', this)" 
                class="nav-item" id="menu-input-jadwal">
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
        // --- 1. GLOBAL SETUP & AJAX SECURITY ---
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        var isProcessingKaryawan = false;

        // --- 2. NAVIGASI (LOAD PAGE) ---
        window.loadAdminPage = function(pageName, element) {
            const contentArea = document.getElementById('main-content-area'); 
            if (!contentArea) return;

            contentArea.style.opacity = '0.5';
            
            fetch(`/admin/${pageName}-content`)
                .then(res => {
                    if (!res.ok) throw new Error('Halaman tidak ditemukan');
                    return res.text();
                })
                .then(html => {
                    contentArea.innerHTML = html;
                    contentArea.style.opacity = '1';
                    history.pushState({page: pageName}, "", `/admin/${pageName}`);
                    
                    document.querySelectorAll('.nav-item, .sub-nav-item').forEach(i => i.classList.remove('active'));
                    if(element) element.classList.add('active');
                })
                .catch(err => {
                    contentArea.innerHTML = '<div style="padding:20px; color:red;">Gagal memuat konten.</div>';
                    contentArea.style.opacity = '1';
                });
        };

        // --- 3. FORM SUBMIT HANDLER (ANTI-DUPLICATE) ---
        // Letakkan di luar $(document).ready agar terdaftar sekali secara global
        $(document).off('submit', '#formTambahKaryawan').on('submit', '#formTambahKaryawan', function(e) {
            e.preventDefault();

            if (isProcessingKaryawan) return false;

            const form = $(this);
            const btn = form.find('button[type="submit"]');
            
            isProcessingKaryawan = true;
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');

            $.ajax({
                url: "/admin/karyawan/store",
                type: "POST",
                data: new FormData(this),
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: response.message,
                            timer: 1500,
                            showConfirmButton: false
                        });
                        
                        // 1. Tutup Modal
                        closeModalTambah();

                        // 2. Reset Form secara total
                        const formElement = document.getElementById('formTambahKaryawan');
                        if (formElement) {
                            formElement.reset(); // Reset inputan standar
                            $(formElement).find('input[type="hidden"]').val(''); // Bersihkan hidden input jika ada
                        }

                        // 3. Muat ulang tabel
                        loadAdminPage('data-karyawan'); 
                    }
                },
                error: function(xhr) {
                    if (xhr.status === 422) {
                        let errors = xhr.responseJSON.errors;
                        let errorMsg = Object.values(errors).flat().join('<br>');
                        Swal.fire('Gagal!', errorMsg, 'warning');
                    } else if (xhr.status === 419) {
                        Swal.fire({ icon: 'error', title: 'Sesi Berakhir', text: 'Memperbarui sesi...' }).then(() => location.reload());
                    } else {
                        Swal.fire('Error!', 'Terjadi kesalahan server.', 'error');
                    }
                },
                complete: function() {
                    // Beri jeda sedikit sebelum unlock untuk mencegah sisa klik "ghosting"
                    setTimeout(() => {
                        isProcessingKaryawan = false;
                        btn.prop('disabled', false).text('Save');
                    }, 500);
                }
            });
        });

        // --- 4. UI HELPER FUNCTIONS ---
        function openModalTambah() { document.getElementById('modalTambahKaryawan').style.display = 'flex'; }
        function closeModalTambah() { document.getElementById('modalTambahKaryawan').style.display = 'none'; }
        
        function toggleDropdown() {
            const dropdown = document.getElementById('dropdownLogout');
            dropdown.style.display = (dropdown.style.display === 'none' || dropdown.style.display === '') ? 'block' : 'none';
        }

        function confirmLogout() {
            Swal.fire({
                title: 'Logout?',
                text: "Anda akan keluar dari sesi ini.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3f36b1',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, Logout'
            }).then((result) => {
                if (result.isConfirmed) document.getElementById('logout-form').submit();
            });
        }

        // Close dropdown when clicking outside
        window.onclick = function(event) {
            if (!event.target.closest('.user-profile-container')) {
                const dropdown = document.getElementById('dropdownLogout');
                if (dropdown) dropdown.style.display = 'none';
            }
        };

      $(document).on('submit', '#formTambahKunjungan', function(e) {
            e.preventDefault();
            const btn = $(this).find('button[type="submit"]');
            btn.prop('disabled', true).text('Saving...');

            $.ajax({
                url: "/admin/datakunjungan/store",
                type: "POST",
                data: $(this).serialize(),
                success: function(response) {
                    Swal.fire('Berhasil!', response.message, 'success');
                    closeModalKunjungan();
                    loadAdminPage('adm-kunjungan'); // Refresh tabel
                },
                error: function() {
                    Swal.fire('Error!', 'Gagal menyimpan data.', 'error');
                },
                complete: function() {
                    btn.prop('disabled', false).text('Simpan');
                }
            });
        });

        function openModalKunjungan() {
            $('#modalTambahKunjungan').css('display', 'flex');
        }

        function closeModalKunjungan() {
            $('#modalTambahKunjungan').hide();
            $('#formTambahKunjungan')[0].reset();
        }
    </script>
</body>
</html>