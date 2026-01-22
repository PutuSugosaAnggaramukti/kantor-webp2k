<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - P2K</title>
    <link rel="stylesheet" href="{{ asset('css/admin-dashboard.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

    <div id="loginLoading" class="login-loading">
        <div class="loading-content">
            <img src="{{ asset('assets/logo.png') }}" class="loading-logo">
            <p>Memuat Data...</p>
            <div class="spinner"></div>
        </div>
    </div>

    <header class="navbar-admin">
        <div class="logo-area" style="display: flex; align-items: center; gap: 10px;">
            <img src="{{ asset('assets/logo.png') }}" alt="Logo" height="40">
            <div style="font-weight: bold; line-height: 1.2;">Sistem Informasi <br><span style="color: #3b82f6;">P2K</span></div>
        </div>
        <div class="user-profile-tag">
            <span style="cursor: pointer;" onclick="toggleAdminDropdown()">
                {{ Auth::user()->name }}
            </span>
        
            <div id="adminDropdown" style="display: none; position: absolute; top: 50px; right: 20px; background: white; padding: 10px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); z-index: 100;">
                <a href="javascript:void(0)" onclick="confirmLogout()" style="text-decoration: none; color: #ef4444; font-weight: bold;">
                    Logout
                </a>
            </div>
        </div>
        
        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
            @csrf
        </form>
    </header>

    <div class="breadcrumb-banner">Dashboard</div>

    <main class="container-center">
            <div id="main-content-area" style="transition: opacity 0.3s ease;">
                <h3 style="margin-bottom: 1.5rem;">Statistik</h3>
            
            <div class="stats-container">
                <div class="card-stats bg-purple">
                    <div style="font-size: 1.1rem; opacity: 0.9;">Total Kunjungan</div>
                    <div style="font-size: 3.5rem; font-weight: bold; margin-top: 10px;">50</div>
                </div>
                
            
            </div>

            <div class="chart-box">
                <canvas id="myChart" height="120"></canvas>
            </div>

            <h3 style="margin-top: 2rem; margin-bottom: 1.5rem;">Menu Aplikasi</h3>
            
            <div class="menu-grid">
                <a href="{{ route('karyawan.index') }}" class="menu-item">
                    <i class="fa-solid fa-users"></i>
                    <span>Data Karyawan</span>
                </a>

                <a href="javascript:void(0)" onclick="loadAdminPage('adm-kunjungan', this)" class="menu-item">
                    <i class="fas fa-clipboard-list"></i>
                    <span>Data Kunjungan</span>
                </a>

                <a href="javascript:void(0)" onclick="loadAdminPage('nasabah', this)" class="menu-item">
                    <i class="fas fa-file-invoice"></i>
                    <span>Data Nasabah</span>
                </a>

                <a href="javascript:void(0)" onclick="loadAdminPage('pelaporan', this)" class="menu-item">
                    <i class="fas fa-edit"></i>
                    <span>Pelaporan</span>
                </a>

                <a href="javascript:void(0)" onclick="loadAdminPage('dokumen', this)" class="menu-item">
                    <i class="fas fa-file-alt"></i>
                    <span>Dokumen</span>
                </a>

                <a href="javascript:void(0)" onclick="loadAdminPage('input-data-kunjungan', this)" class="menu-item">
                    <i class="fas fa-folder-plus"></i>
                    <span>Input Data Kunjungan</span>
                </a>

                <a href="javascript:void(0)" onclick="loadAdminPage('jadwal-kunjungan', this)" class="menu-item">
                    <i class="fas fa-calendar-alt"></i>
                    <span>Jadwal Kunjungan</span>
                </a>
            </div>
        </div>
    </main>

    <footer class="footer-admin">
        Sistem Aplikasi P2K
    </footer>

    <script>
    function loadAdminPage(pageName, element) {
        // 1. Tentukan target container (sesuaikan ID dengan div di HTML Anda)
        const contentArea = document.getElementById('main-content-area');
        
        if (!contentArea) {
            console.error("Target ID 'main-content-area' tidak ditemukan!");
            return;
        }

        // 2. Beri efek visual sedang memuat
        contentArea.style.opacity = '0.3';
        contentArea.style.transition = 'opacity 0.3s ease';

        // 3. Tentukan URL (mengarah ke controller yang hanya return view partial/tabel)
        const url = `/admin/${pageName}-content`;

        // 4. Lakukan Fetch data
        fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            if (!response.ok) throw new Error('Halaman gagal dimuat (Error: ' + response.status + ')');
            return response.text();
        })
        .then(html => {
            // 5. Masukkan konten ke dalam area putih
            contentArea.innerHTML = html;
            contentArea.style.opacity = '1';

            // 6. Atur class 'active' agar menu yang diklik berwarna ungu
            updateActiveClass(element);
        })
        .catch(error => {
            console.error('Fetch Error:', error);
            contentArea.innerHTML = `
                <div style="text-align:center; padding:50px;">
                    <i class="fas fa-exclamation-triangle" style="font-size:48px; color:#e74c3c;"></i>
                    <p style="margin-top:15px; font-weight:bold;">Gagal memuat konten.</p>
                    <small style="color:#666;">${error.message}</small>
                </div>
            `;
            contentArea.style.opacity = '1';
        });
    }

    /**
     * Fungsi pembantu untuk mengelola status 'active' di Sidebar & Grid
     */
    function updateActiveClass(element) {
        // Hapus class active dari semua menu (sidebar & grid)
        const allMenus = document.querySelectorAll('.nav-item, .menu-item, .sub-nav-item');
        allMenus.forEach(menu => menu.classList.remove('active'));

        // Jika elemen diklik ada, tambahkan class active
        if (element) {
            element.classList.add('active');
        }
    }
</script>

    <script>
        const ctx = document.getElementById('myChart').getContext('2d');
        
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['2025/10/15', '2025/10/16', '2025/10/17', '2025/10/18', '2025/10/19', '2025/10/20', '2025/10/21'],
                datasets: [
                    { 
                        label: 'nasabah ada', 
                        data: [5, 6, 7, 8, 9, 10, 11], 
                        backgroundColor: '#1e3a8a',
                        borderRadius: 5
                    },
                    { 
                        label: 'nasabah tidak ada', 
                        data: [2, 3, 4, 5, 6, 7, 8], 
                        backgroundColor: '#ea580c',
                        borderRadius: 5
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                animation: {
                    duration: 2000,        // Durasi animasi 2 detik
                    easing: 'easeOutQuart' // Efek smooth/tumbuh ke atas
                },
                plugins: {
                    legend: {
                        position: 'top',
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            display: true,
                            color: '#f0f0f0'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    </script>

    <script>
        function toggleAdminDropdown() {
            const dropdown = document.getElementById('adminDropdown');
            dropdown.style.display = dropdown.style.display === 'none' ? 'block' : 'none';
        }

        function confirmLogout() {
            Swal.fire({
                title: 'Konfirmasi Logout',
                text: "Sesi admin akan diakhiri",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3f36b1',
                confirmButtonText: 'Ya, Keluar!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Aktifkan loader jika ada
                    const loader = document.getElementById('loginLoading');
                    if (loader) {
                        loader.style.display = 'flex';
                    }

                    // Kirim form POST ke Laravel
                    setTimeout(() => {
                        document.getElementById('logout-form').submit();
                    }, 800);
                }
            });
        }
    </script>

</body>
</html>