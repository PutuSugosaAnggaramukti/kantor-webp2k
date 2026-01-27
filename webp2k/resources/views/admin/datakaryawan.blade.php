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
                id="menu-rekap-kunjungan" onclick="loadAdminPage('data-kunjungan', this)" class="nav-item {{ request()->is('admin/kunjungan*') ? 'active' : '' }}">
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

            <a href="javascript:void(0)" onclick="loadAdminPage('adm-kunjungan', this)" class="nav-item" id="menu-input-jadwal">
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

        window.loadAdminPage = function(pageName, element) {
            const contentArea = document.getElementById('main-content-area');
            if (!contentArea) return;

            // Menghapus class active dari semua menu sidebar dulu
            document.querySelectorAll('.nav-item').forEach(i => i.classList.remove('active'));

            // LOGIKA BARU: Tentukan menu mana yang harus aktif
            let menuToActive = element; // defaultnya adalah elemen yang diklik

            if (!element) {
                // Jika dipicu tanpa klik langsung (misal dari tombol NEXT)
                if (pageName.includes('nasabah-detail')) {
                    menuToActive = document.getElementById('menu-nasabah');
                } else {
                    menuToActive = document.getElementById(`menu-${pageName}`);
                }
            } else {
                // Jika klik dari sidebar, tapi kita ingin memastikan detail tetap menginduk ke nasabah
                if (pageName.includes('nasabah-detail')) {
                    menuToActive = document.getElementById('menu-nasabah');
                }
            }

            // Aktifkan menu yang sesuai
            if (menuToActive) menuToActive.classList.add('active');

            // Proses fetch data seperti biasa
            const fetchUrl = `/admin/${pageName}-content`;
            fetch(fetchUrl)
                .then(res => res.text())
                .then(html => {
                    contentArea.innerHTML = html;
                    // Update URL browser agar rapi
                    history.pushState({page: pageName}, "", `/admin/${pageName.replace('-content', '')}`);
                })
                .catch(err => console.error("Gagal memuat halaman:", err));
        };
                
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

      $(document).off('submit', '#formTambahKunjungan').on('submit', '#formTambahKunjungan', function(e) {
            e.preventDefault();
            
            const form = $(this);
            const btn = form.find('button[type="submit"]');
            
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');

            $.ajax({
                url: "/admin/datakunjungan/store",
                type: "POST",
                // GUNAKAN FormData agar input 'date' dan 'month' terkirim dengan sempurna
                data: new FormData(this), 
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        Swal.fire('Berhasil!', 'Data kunjungan telah disimpan.', 'success');
                        closeModalKunjungan();
                        
                        // Refresh ke halaman rekap kunjungan
                        loadAdminPage('adm-kunjungan'); 
                    }
                },
                error: function(xhr) {
                    console.error(xhr.responseText);
                    Swal.fire('Error!', 'Gagal menyimpan data. Pastikan semua input terisi.', 'error');
                },
                complete: function() {
                    btn.prop('disabled', false).text('Simpan');
                }
            });
        });

        function openModalKunjungan() {
            $('#selectKaryawan').html('<option value="">Mengambil data...</option>');
            $.ajax({
                url: "/admin/get-karyawan-list", 
                type: "GET",
                success: function(response) {
                    let options = '<option value="">-- Pilih AO --</option>';
                    response.forEach(function(k) {
                        options += `<option value="${k.id}">${k.nama}</option>`;
                    });
                    $('#selectKaryawan').html(options);
                },
                error: function() {
                    $('#selectKaryawan').html('<option value="">Gagal memuat data</option>');
                }
            });
            $('#modalTambahKunjungan').css('display', 'flex');
        }

        function closeModalKunjungan() {
            $('#modalTambahKunjungan').hide();
            $('#formTambahKunjungan')[0].reset();
        }

        function refreshKaryawanDropdown() {
            $.ajax({
                url: "/get-karyawan-list",
                type: "GET",
                success: function(response) {
                    // Cari elemen select di dalam modal
                    let dropdown = $('select[name="karyawan_id"]');
                    dropdown.empty(); // Kosongkan daftar lama
                    dropdown.append('<option value="">-- Pilih AO --</option>');

                    // Masukkan data terbaru dari database
                    response.forEach(function(karyawan) {
                        dropdown.append(`<option value="${karyawan.id}">${karyawan.nama}</option>`);
                    });
                },
                error: function() {
                    console.error("Gagal mengambil data karyawan terbaru.");
                }
            });
        }

       function openModalEdit(id) {
            // Tambahkan /admin/ sesuai dengan prefix di Route
            const url = `/admin/karyawan/${id}/edit`; 

            fetch(url, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(response => {
                if (!response.ok) throw new Error('Cek kembali, apakah Anda sudah login sebagai admin?');
                return response.json();
            })
            .then(data => {
                // Isi input modal
                document.getElementById('edit_kode_ao').value = data.kode_ao;
                document.getElementById('edit_nama').value = data.nama;
                document.getElementById('edit_username').value = data.username;
                document.getElementById('edit_status').value = data.status;
                
                // JANGAN LUPA: Update juga action form-nya agar pakai /admin/
                const form = document.getElementById('formEditKaryawan');
                form.action = `/admin/karyawan/${id}`; 

                // Tampilkan Modal
                document.getElementById('modalEditKaryawan').style.display = 'flex';
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Gagal mengambil data. Pastikan URL-nya adalah: ' + url);
            });
        }

        function openModalDetail(id) {
        // Kita panggil route 'show' dengan prefix /admin
            fetch(`/admin/karyawan/${id}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => {
                if (!response.ok) throw new Error('Gagal mengambil data');
                return response.json();
            })
            .then(data => {
                document.getElementById('det_kode_ao').value = data.kode_ao;
                document.getElementById('det_nama').value = data.nama;
                document.getElementById('det_username').value = data.username;
                document.getElementById('det_status').value = data.status;

            
                document.getElementById('modalDetailKaryawan').style.display = 'flex';
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat memuat data detail.');
            });
        }

        // 2. FUNGSI UNTUK MENUTUP MODAL DETAIL
        function closeModalDetail() {
            document.getElementById('modalDetailKaryawan').style.display = 'none';
        }

        window.onclick = function(event) {
            const modalDetail = document.getElementById('modalDetailKaryawan');
            const modalEdit = document.getElementById('modalEditKaryawan');
            
            if (event.target == modalDetail) {
                closeModalDetail();
            }
            if (event.target == modalEdit) {
                closeModalEdit(); 
            }
        }

        function updateKaryawanDropdown() {
            $.ajax({
                url: "/get-karyawan-list", 
                type: "GET",
                success: function(response) {
                    let dropdown = $('select[name="karyawan_id"]');
                    dropdown.empty(); 
                    dropdown.append('<option value="">-- Pilih AO --</option>');
                    
                    // Isi dengan data terbaru
                    response.forEach(function(k) {
                        dropdown.append(`<option value="${k.id}">${k.nama}</option>`);
                    });
                }
            });
        }

    </script>
</body>
</html>