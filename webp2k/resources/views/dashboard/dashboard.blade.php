<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Informasi P2K</title>
    
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    
    <script src="https://cdn.tailwindcss.com"></script> <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gray-100 font-sans">

    <header class="p2k-header">
        <div class="flex items-center space-x-3">
            <img src="{{ asset('assets/logo.png') }}" alt="Logo" class="w-10 h-10">
            <div>
                <h1 class="font-bold text-lg leading-tight">Sistem Informasi</h1>
                <h1 class="font-bold text-lg leading-tight text-blue-900">P2K</h1>
            </div>
        </div>
        <div class="user-badge">
            <span class="font-bold text-gray-700 text-sm">Username</span>
            <div class="w-10 h-10 bg-gray-300 rounded-full overflow-hidden border-2 border-white">
                <img src="https://www.w3schools.com/howto/img_avatar.png" alt="User">
            </div>
        </div>
    </header>

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
            <button class="btn-menu">
                <i class="fa-solid fa-user-plus text-4xl mb-3"></i>
                <span class="font-semibold">Data Kunjungan</span>
            </button>
            <button class="btn-menu">
                <i class="fa-solid fa-clipboard-list text-4xl mb-3"></i>
                <span class="font-semibold">Laporan Kunjungan</span>
            </button>
            <button class="btn-menu">
                <i class="fa-solid fa-file-lines text-4xl mb-3"></i>
                <span class="font-semibold">Dokumen</span>
            </button>
            <button class="btn-menu">
                <i class="fa-solid fa-gear text-4xl mb-3"></i>
                <span class="font-semibold">Pengaturan</span>
            </button>
        </div>
    </main>

    <footer class="p2k-footer">
        2025 Sistem Aplikasi P2K
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
    </script>
</body>
</html>