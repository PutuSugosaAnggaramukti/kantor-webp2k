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
            
            <div id="dashboard-default-view">
                <h3 style="margin-bottom: 1.5rem;">Statistik</h3>
                
                <div class="stats-container">
                    <div class="card-stats bg-purple">
                        <div style="font-size: 1.1rem; opacity: 0.9;">Total Kunjungan</div>
                        <div style="font-size: 3.5rem; font-weight: bold; margin-top: 10px;">
                            {{ $totalKunjungan ?? 0 }}
                        </div>
                    </div>
                </div>

                <div class="chart-box">
                    <canvas id="myChart" height="120"></canvas>
                </div>

                <h3 style="margin-top: 2rem; margin-bottom: 1.5rem;">Menu Aplikasi</h3>
                
                <div class="menu-grid">
                    <a href="javascript:void(0)" onclick="transitionToAdminPage('data-karyawan')" class="menu-item">
                        <i class="fa-solid fa-users"></i>
                        <span>Data Karyawan</span>
                    </a>

                    <a href="javascript:void(0)" onclick="transitionToAdminPage('data-kunjungan')" class="menu-item">
                        <i class="fas fa-clipboard-list"></i>
                        <span>Data Kunjungan</span>
                    </a>

                    <a href="javascript:void(0)" onclick="transitionToAdminPage('nasabah')" class="menu-item">
                        <i class="fas fa-user-friends"></i>
                        <span>Data Nasabah</span>
                    </a>

                    <a href="javascript:void(0)" onclick="transitionToAdminPage('pelaporan')" class="menu-item">
                        <i class="fas fa-file-alt"></i>
                        <span>Pelaporan</span>
                    </a>

                    <a href="javascript:void(0)" onclick="transitionToAdminPage('dokumen')" class="menu-item">
                        <i class="fas fa-file-word"></i>
                        <span>Dokumen</span>
                    </a>

                    <a href="javascript:void(0)" onclick="transitionToAdminPage('adm-kunjungan')" class="menu-item">
                        <i class="fas fa-calendar-plus"></i>
                        <span>Input Jadwal Kunjungan</span>
                    </a>

                </div>
            </div>
        </div>
</main>

    <footer class="footer-admin">
        Sistem Aplikasi P2K
    </footer>

    <script>
    function transitionToAdminPage(targetPage) {
        const loader = document.getElementById('loginLoading');
        const loadingText = loader.querySelector('p');
        
        const labels = {
            'data-karyawan': 'Memuat Data Karyawan...',
            'adm-kunjungan': 'Memuat Data Kunjungan...',
            'pelaporan': 'Memuat Laporan...',
            'nasabah': 'Memuat Data Nasabah...',
            'dokumen': 'Memuat Dokumen...',
            'data-kunjungan': 'Memuat Input Kunjungan...'
        };

        if (loadingText) {
            loadingText.innerText = labels[targetPage] || 'Memuat Halaman...';
        }

        loader.classList.add('active');
        loader.style.display = 'flex';

        setTimeout(() => {
            window.location.href = "{{ route('karyawan.index') }}?page=" + targetPage;
        }, 1000); 
    }
    function updateActiveClass(element) {
        const allMenus = document.querySelectorAll('.nav-item, .menu-item, .sub-nav-item');
        allMenus.forEach(menu => menu.classList.remove('active'));

        if (element) {
            element.classList.add('active');
        }
    }
</script>

    <script>
      const ctx = document.getElementById('myChart').getContext('2d');
        const myChart = new Chart(ctx, {
            type: 'bar',
            data: {
                // Label diambil dari data nama AO
                labels: {!! json_encode($labels) !!}, 
                datasets: [{
                    label: 'Jumlah Kunjungan Selesai',
                    // Data diambil dari jumlah kunjungan masing-masing AO
                    data: {!! json_encode($counts) !!},
                    backgroundColor: 'rgba(54, 162, 235, 0.7)', // Warna Biru
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1,
                    borderRadius: 5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1 // Agar skala y selalu angka bulat
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
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