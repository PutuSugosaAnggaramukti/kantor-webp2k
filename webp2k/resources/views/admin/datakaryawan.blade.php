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
                    onclick="loadAdminPage('adm-kunjungan', this)" class="nav-item {{ request()->is('admin/kunjungan*') ? 'active' : '' }}">
                    <i class="fa-solid fa-clipboard-check"></i> Data Kunjungan
                </a>
                
                <a href="javascript:void(0)" 
                    onclick="loadAdminPage('nasabah', this)" class="nav-item {{ request()->is('admin/nasabah*') ? 'active' : '' }}" id="menu-nasabah">
                    <i class="fa-solid fa-address-card"></i> Data Nasabah
                </a>

                <a href="javascript:void(0)" 
                    onclick="loadAdminPage('pelaporan', this)" class="nav-item {{ request()->is('pelaporan*') ? 'active' : '' }}" id="menu-pelaporan">
                    <i class="fa-solid fa-file-signature"></i> Pelaporan
                </a>

                <a href="javascript:void(0)" 
                    onclick="loadAdminPage('dokumen', this)" class="nav-item" id="menu-dokumen">
                    <i class="fa-solid fa-file-lines"></i> Dokumen
                </a>

                <div class="dropdown-wrapper">
                    <a href="javascript:void(0)" class="nav-item dropdown-toggle" id="menu-input-data">
                        <div style="display: flex; align-items: center;">
                            <i class="fa-solid fa-file-circle-plus"></i> 
                            <span>Input Data Kunjungan</span>
                        </div>
                        <i class="fa-solid fa-chevron-down arrow-icon" style="font-size: 14px; margin-right: 10px;"></i> 
                    </a>
                    
                    <div class="dropdown-container" id="dropdown-input">
                        <a href="javascript:void(0)" onclick="loadAdminPage('jadwal')" class="sub-nav-item" id="menu-jadwal">
                            <i class="fa-solid fa-calendar-days"></i> Jadwal Kunjungan
                        </a>
                        
                        <a href="javascript:void(0)" onclick="loadAdminPage('input-baru')" class="sub-nav-item">
                            <i class="fa-solid fa-circle-plus"></i> Tambah Data
                        </a>
                    </div>
                </div>
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
                        <a href="javascript:void(0)" style="display: flex; align-items: center; padding: 12px 15px; text-decoration: none; color: #333; font-weight: 700; font-size: 14px; transition: 0.3s;">
                            <i class="fa-solid fa-user" style="margin-right: 10px; color: #3f36b1;"></i> Profil
                        </a>
                        <hr style="margin: 0; border: 0; border-top: 1px solid #eee;">
                        <form action="" method="POST" id="logout-form">
                            @csrf
                            <a href="javascript:void(0)" onclick="confirmLogout()" style="display: flex; align-items: center; padding: 12px 15px; text-decoration: none; color: #e74c3c; font-weight: 700; font-size: 14px; transition: 0.3s;">
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
          // Gunakan nama ini agar cocok dengan link di sidebar Anda
        window.loadAdminPage = function(pageName, element) {
            console.log("Memuat halaman:", pageName);
            
            const contentArea = document.getElementById('main-content-area'); 
            if (!contentArea) {
                console.error("Error: ID 'main-content-area' tidak ditemukan!");
                return;
            }

            contentArea.style.opacity = '0.5';
            
            // Memanggil route Laravel
            fetch(`/admin/${pageName}-content`)
                .then(res => {
                    if (!res.ok) throw new Error('Halaman tidak ditemukan');
                    return res.text();
                })
                .then(html => {
                    contentArea.innerHTML = html;
                    contentArea.style.opacity = '1';

                    history.pushState({page: pageName}, "", `/admin/${pageName}`);
                    
                    // Mengatur class active pada menu
                    document.querySelectorAll('.nav-item, .sub-nav-item, .menu-item').forEach(i => {
                        i.classList.remove('active');
                    });
                    
                    if(element) {
                        element.classList.add('active');
                    }
                })
                .catch(err => {
                    console.error(err);
                    contentArea.innerHTML = '<div style="padding:20px; color:red;">Gagal memuat konten.</div>';
                    contentArea.style.opacity = '1';
                });
        };

    // Alias navigasiAdmin ke loadAdminPage agar link Data Karyawan tidak error
    window.navigasiAdmin = window.loadAdminPage;

        function setActiveMenuOnLoad() {
            const currentPath = window.location.pathname;
            const navItems = document.querySelectorAll('.nav-item');

            navItems.forEach(item => {
                if (currentPath.includes('karyawan') && item.innerText.includes('Karyawan')) {
                    item.classList.add('active');
                } else if (currentPath.includes('kunjungan') && item.innerText.includes('Kunjungan')) {
                    item.classList.add('active');
                }
            });
        }

        window.addEventListener('DOMContentLoaded', setActiveMenuOnLoad);

      function updateAdminSidebar(pageName) {
            document.querySelectorAll('.nav-item').forEach(item => {
                item.classList.remove('active');
            });

            const activeMenu = document.getElementById(`menu-${pageName}`);
            if (activeMenu) {
                activeMenu.classList.add('active');
            }
        }

        const modalEdit = document.getElementById('modalEditKaryawan');

        function openModalEdit() {
            if (modalEdit) {
                modalEdit.style.display = 'flex';
            }
        }

        function closeModalEdit() {
            if (modalEdit) {
                modalEdit.style.display = 'none';
            }
        }

        window.addEventListener('click', function(event) {
            if (event.target === modalEdit) {
                closeModalEdit();
            }
        });

        document.querySelector('.dropdown-toggle').addEventListener('click', function() {
            const dropdown = document.getElementById('dropdown-input');
            const arrow = this.querySelector('.arrow-icon');
            
            dropdown.classList.toggle('show');
            arrow.classList.toggle('rotate');
        });

       function openModalTambah() {
            const modal = document.getElementById('modalTambahKaryawan');
            if (modal) {
                modal.style.display = 'flex'; 
            }
        }

        function closeModalTambah() {
            const modal = document.getElementById('modalTambahKaryawan');
            if (modal) {
                modal.style.display = 'none';
            }
        }

        const modalDetail = document.getElementById('modalDetailKaryawan');

        function openModalDetail() {
            if (modalDetail) {
                modalDetail.style.display = 'flex';
            }
        }

        function closeModalDetail() {
            if (modalDetail) {
                modalDetail.style.display = 'none';
            }
        }

        window.addEventListener('click', function(event) {
            if (event.target === modalDetail) {
                closeModalDetail();
            }
        });

        function openModalExport() {
            $('#modalExportNasabah').fadeIn(); 
        }

        function closeModalExport() {
            $('#modalExportNasabah').fadeOut();
        }

        function openModalFilter() {
            $('#modalFilterNasabah').fadeIn();
        }

        function closeModalFilter() {
            $('#modalFilterNasabah').fadeOut();
        }

        function openExportModal() {
            const modal = document.getElementById('modalExportPelaporan');
            if (modal) {
                modal.style.display = 'flex';
            }
        }


        window.addEventListener('click', function(event) {
            const modal = document.getElementById('modalExportPelaporan');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        });

        $(window).on('click', function(event) {
            if ($(event.target).is('.modal-overlay')) {
                closeModalExport();
                closeModalFilter();
            }
        });
           
        function toggleDropdown() {
            const dropdown = document.getElementById('dropdownLogout');
            const isHidden = dropdown.style.display === 'none' || dropdown.style.display === '';
            
            dropdown.style.display = isHidden ? 'block' : 'none';
        }

        window.onclick = function(event) {
            if (!event.target.closest('.user-profile-container')) {
                const dropdown = document.getElementById('dropdownLogout');
                if (dropdown) dropdown.style.display = 'none';
            }
        }

        function confirmLogout() {
            if (confirm('Apakah Anda yakin ingin keluar?')) {
                document.getElementById('logout-form').submit();
            }
        }

        $(document).ready(function() {
        let isProcessing = false; // Flag tambahan untuk mencegah klik beruntun

        // Gunakan .off() untuk memastikan hanya ada SATU handler yang aktif
        $(document).off('submit', '#formTambahKaryawan').on('submit', '#formTambahKaryawan', function(e) {
            e.preventDefault();
            
            // Jika sedang memproses, jangan lakukan apa-apa
            if (isProcessing) return false;

            let form = $(this);
            let submitButton = form.find('button[type="submit"]');
            
            // Aktifkan mode processing
            isProcessing = true;
            submitButton.prop('disabled', true).text('Processing...');

            $.ajax({
                url: "{{ route('karyawan.store') }}",
                type: "POST",
                data: new FormData(this),
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: 'Data karyawan kedua berhasil disimpan.',
                            timer: 1500,
                            showConfirmButton: false
                        });

                        closeModalTambah();
                        form[0].reset(); // Sangat penting agar data lama tidak terkirim lagi
                        
                        // Muat ulang halaman data-karyawan
                        loadAdminPage('data-karyawan'); 
                    }
                },
                error: function(xhr) {
                    if (xhr.status === 422) {
                        let errors = xhr.responseJSON.errors;
                        let errorString = Object.values(errors).flat().join('<br>');
                        Swal.fire('Gagal!', errorString, 'warning');
                    } else {
                        Swal.fire('Error!', 'Terjadi kesalahan sistem.', 'error');
                    }
                },
                complete: function() {
                    // Matikan mode processing agar tombol bisa digunakan lagi nanti
                    isProcessing = false;
                    submitButton.prop('disabled', false).text('Save');
                }
            });
            return false;
        });
    });
    </script>
</body>
</html>