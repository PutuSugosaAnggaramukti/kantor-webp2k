<!DOCTYPE html>
<html lang="id" class="h-full"> <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Informasi P2K</title>
    
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    <script src="https://cdn.tailwindcss.com"></script> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="bg-gray-100 font-sans flex flex-col min-h-full">

    <div id="loginLoading" class="login-loading">
        <div class="loading-card">
            <img src="{{ asset('assets/logo.png') }}" class="loading-logo" alt="P2K">
            <p>Memuat Data Kunjungan...</p>
            <div class="spinner"></div>
        </div>
    </div>
        
    <div class="flex-grow">
        <div class="p2k-header">
            <div class="p2k-header-left">
                <div class="p2k-brand">
                    <img src="/assets/logo.png" alt="Logo P2K" class="p2k-logo">
                    <div class="p2k-title">
                        <span>Sistem Informasi</span>
                        <strong>P2K</strong>
                    </div>
                </div>
            </div>

            <div class="user-area">
                <button class="user-badge user-trigger" id="userTrigger">
                    <span class="user-name">Username</span>
                    <img src="/assets/avatar.png" class="user-avatar">
                </button>

               <div class="user-dropdown" id="userDropdown">
                <a href="javascript:void(0)" onclick="transitionToPage('pengaturan')" class="dropdown-item">Edit Profil</a>
                
                <button type="button" onclick="confirmLogout()" class="dropdown-item logout" style="width: 100%; text-align: left; border: none; background: none; cursor: pointer;">
                    Logout
                </button>
            </div>
            </div>
        </div>

        <nav class="p2k-nav">
            <a href="#" class="hover:underline">Dashboard</a>
        </nav>

        <main class="p-6 max-w-6xl mx-auto">
            <h2 class="text-2xl font-bold mb-6">Statistik</h2>

            <div class="stat-grid">
                <div class="card-stat bg-blue-p2k">
                    <p class="text-lg font-semibold">Total Kunjungan</p>
                    <p class="text-4xl font-bold mt-2">{{ $total_kunjungan }}</p>
                </div>
                <div class="card-stat bg-green-p2k">
                    <p class="text-lg font-semibold">Sudah Dikunjungi</p>
                    <p class="text-4xl font-bold mt-2">{{ $sudah_dikunjungi }}</p>
                </div>
            </div>

            <div class="bg-white p-6 rounded-xl shadow-sm mb-8">
                <div class="h-64">
                    <canvas id="visitChart"></canvas>
                </div>
            </div>

            <h2 class="text-2xl font-bold mb-6">Menu Aplikasi</h2>

            <div class="menu-grid">
                <a href="javascript:void(0)" onclick="transitionToKunjungan()" class="btn-menu">
                    <i class="fa-solid fa-user-plus text-4xl mb-3"></i>
                    <span class="font-semibold">Data Kunjungan</span>
                </a>
              <a href="javascript:void(0)" onclick="transitionToPage('laporan-kunjungan')" class="btn-menu">
                    <i class="fa-solid fa-file-lines text-4xl mb-3"></i>
                    <span class="font-semibold">Laporan Kunjungan</span>
                </a>

                <a href="javascript:void(0)" onclick="loadMenuWithTransition('dokumen')" class="btn-menu">
                    <i class="fa-solid fa-file-invoice text-4xl mb-3"></i>
                    <span class="font-semibold">Dokumen</span>
                </a>

                <a href="javascript:void(0)" onclick="loadMenuWithTransition('pengaturan')" class="btn-menu">
                    <i class="fa-solid fa-gear text-4xl mb-3"></i>
                    <span class="font-semibold">Pengaturan</span>
                </a>
            </div>
        </main>
    </div> <footer class="p2k-footer mt-auto"> Sistem Aplikasi P2K
    </footer>

    <script>
        const ctx = document.getElementById('visitChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: {!! json_encode($labels) !!},
                datasets: [
                    {
                        label: 'nasabah ada',
                        data: {!! json_encode($nasabah_ada) !!},
                        backgroundColor: '#075985',
                    },
                    {
                        label: 'nasabah tidak ada',
                        data: {!! json_encode($nasabah_tidak_ada) !!},
                        backgroundColor: '#f97316',
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });

        const trigger = document.getElementById('userTrigger');
        const dropdown = document.getElementById('userDropdown');

        trigger.addEventListener('click', () => {
            dropdown.classList.toggle('active');
        });

        document.addEventListener('click', (e) => {
            if (!trigger.contains(e.target) && !dropdown.contains(e.target)) {
                dropdown.classList.remove('active');
            }
        });

        function transitionToKunjungan() {
            const loader = document.getElementById('loginLoading');
            loader.classList.add('active');
            loader.style.display = 'flex';
            setTimeout(() => {
                window.location.href = "{{ route('data-kunjungan') }}";
            }, 1000); 
        }
    </script>

    <script>
    function transitionToPage(targetPage) {
        const loader = document.getElementById('loginLoading');
        const loadingText = loader.querySelector('p');
        
        // Memberikan feedback teks yang berbeda sesuai menu
        const labels = {
            'data-kunjungan': 'Memuat Data Kunjungan...',
            'laporan-kunjungan': 'Memuat Laporan Kunjungan...',
            'dokumen': 'Memuat Dokumen...',
            'pengaturan': 'Memuat Pengaturan...'
        };

        if (loadingText) {
            loadingText.innerText = labels[targetPage] || 'Memuat Halaman...';
        }

        // Tampilkan animasi loading
        loader.classList.add('active');
        loader.style.display = 'flex';

        // Berikan jeda 1 detik agar transisi terasa halus sebelum pindah halaman
        setTimeout(() => {
            // Redirect ke route data-kunjungan dengan parameter 'page' agar bisa di-load dinamis
            window.location.href = "{{ route('data-kunjungan') }}?page=" + targetPage;
        }, 1000); 
    }
    </script>

    <script>
        function loadMenuWithTransition(targetPage) {
        const loader = document.getElementById('loginLoading');
        const loadingText = loader.querySelector('p');
        
        // Ubah teks loading sesuai menu yang diklik
        if(targetPage === 'pengaturan') loadingText.innerText = "Memuat Pengaturan...";
        else if(targetPage === 'dokumen') loadingText.innerText = "Memuat Dokumen...";
        else if(targetPage === 'laporan-kunjungan') loadingText.innerText = "Memuat Laporan...";
        else loadingText.innerText = "Memuat Data Kunjungan...";

        // Tampilkan overlay loading
        loader.classList.add('active');
        loader.style.display = 'flex';

        // Redirect ke halaman data-kunjungan dengan parameter 'page'
        setTimeout(() => {
            window.location.href = `{{ route('data-kunjungan') }}?page=${targetPage}`;
        }, 1000); 
    }
    </script>

    <script>
        function confirmLogout() {
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: "Anda akan keluar dari sistem P2K",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3f36b1', // Warna ungu seragam dengan UI
                cancelButtonColor: '#cbd5e1',
                confirmButtonText: 'Ya, Logout!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Panggil overlay loading yang sudah Anda miliki
                    const loader = document.getElementById('loginLoading');
                    if (loader) {
                        const loadingText = loader.querySelector('p');
                        if (loadingText) loadingText.innerText = "Mengakhiri Sesi...";
                        loader.classList.add('active');
                        loader.style.display = 'flex';
                    }

                    // Transisi ke halaman login-preview setelah 1 detik
                    setTimeout(() => {
                        window.location.href = "/login-preview";
                    }, 1000);
                }
            });
        }
    </script>

</body>
</html>