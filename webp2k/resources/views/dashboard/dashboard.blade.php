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
                        <span class="user-name">{{ Auth::guard('karyawan')->user()->nama}}</span>
                        <img src="{{ asset('assets/avatar.png') }}" class="user-avatar">
                    </button>
                
                    <div class="user-dropdown" id="userDropdown">
                        <a href="javascript:void(0)" onclick="transitionToPage('pengaturan')" class="dropdown-item">Edit Profil</a>
                        
                        <a href="javascript:void(0)" class="dropdown-item logout" onclick="confirmLogout()">
                            Logout
                        </a>
                    </div>
                </div>
                
                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                    @csrf
                </form>
        </div>

        <nav class="p2k-nav">
            <a href="#" class="hover:underline">Dashboard</a>
        </nav>

        <main class="p-6 max-w-6xl mx-auto">
            <h2 class="text-2xl font-bold mb-6 italic text-gray-700">Statistik</h2>
            <div class="stat-grid mb-8">
                <div class="card-stat bg-blue-p2k cursor-pointer transition transform hover:scale-105" onclick="openModal('modalRencana')">
                    <p class="text-lg font-semibold">Total Rencana</p>
                    <p class="text-4xl font-bold mt-2">{{ $total_rencana }}</p>
                    <p class="text-xs opacity-75 mt-2"><i class="fa-solid fa-magnifying-glass"></i> Klik detail</p>
                </div>

                <div class="card-stat bg-green-p2k cursor-pointer transition transform hover:scale-105" onclick="openModal('modalSudah')">
                    <p class="text-lg font-semibold">Sudah Dikunjungi</p>
                    <p class="text-4xl font-bold mt-2">{{ $total_kunjungan }}</p>
                    <p class="text-xs opacity-75 mt-2"><i class="fa-solid fa-magnifying-glass"></i> Klik detail</p>
                </div>

                <div class="card-stat cursor-pointer transition transform hover:scale-105" style="background-color: #e74c3c; color: white;" onclick="openModal('modalKOL5')">
                    <p class="text-lg font-semibold">Wajib (KOL 5)</p>
                    <p class="text-4xl font-bold mt-2">{{ $wajib_kol5 }}</p>
                    <p class="text-xs opacity-75 mt-2"><i class="fa-solid fa-triangle-exclamation"></i> Cek Prioritas</p>
                </div>

                <div class="card-stat" style="background-color: #f39c12; color: white;">
                    <p class="text-lg font-semibold">Hari Ini</p>
                    <p class="text-4xl font-bold mt-2">{{ $kunjungan_hari_ini }}</p>
                    <p class="text-xs opacity-75 mt-2">Update Realtime</p>
                </div>
            </div>

            <h2 class="text-2xl font-bold mb-6 italic text-gray-700">Persentase Performa</h2>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8 text-center">
                <div class="bg-white p-4 rounded-xl shadow-sm border-b-4 border-blue-500">
                    <p class="text-[10px] text-gray-500 font-bold uppercase mb-2">Target Harian</p>
                    <div class="relative inline-flex items-center justify-center">
                        <svg class="w-16 h-16 transform -rotate-90">
                            <circle class="text-gray-200" stroke-width="6" stroke="currentColor" fill="transparent" r="28" cx="32" cy="32" />
                            <circle class="text-blue-600" stroke-width="6" stroke-dasharray="175.9" stroke-dashoffset="{{ 175.9 - (175.9 * ($kunjungan_hari_ini / 10 * 100)) / 100 }}" stroke-linecap="round" stroke="currentColor" fill="transparent" r="28" cx="32" cy="32" />
                        </svg>
                        <span class="absolute text-xs font-bold">{{ round(($kunjungan_hari_ini / 10) * 100) }}%</span>
                    </div>
                </div>
                <div class="bg-white p-4 rounded-xl shadow-sm border-b-4 border-green-500">
                    <p class="text-[10px] text-gray-500 font-bold uppercase mb-2">Success Rate</p>
                    <div class="relative inline-flex items-center justify-center">
                        <svg class="w-16 h-16 transform -rotate-90">
                            <circle class="text-gray-200" stroke-width="6" stroke="currentColor" fill="transparent" r="28" cx="32" cy="32" />
                            <circle class="text-green-500" stroke-width="6" stroke-dasharray="175.9" stroke-dashoffset="{{ 175.9 - (175.9 * 85) / 100 }}" stroke-linecap="round" stroke="currentColor" fill="transparent" r="28" cx="32" cy="32" />
                        </svg>
                        <span class="absolute text-xs font-bold">85%</span>
                    </div>
                </div>
                <div class="bg-white p-4 rounded-xl shadow-sm border-b-4 border-red-500">
                    <p class="text-[10px] text-gray-500 font-bold uppercase mb-2">KOL 5 Done</p>
                    <div class="relative inline-flex items-center justify-center">
                        <svg class="w-16 h-16 transform -rotate-90">
                            <circle class="text-gray-200" stroke-width="6" stroke="currentColor" fill="transparent" r="28" cx="32" cy="32" />
                            <circle class="text-red-500" stroke-width="6" stroke-dasharray="175.9" stroke-dashoffset="{{ 175.9 - (175.9 * 60) / 100 }}" stroke-linecap="round" stroke="currentColor" fill="transparent" r="28" cx="32" cy="32" />
                        </svg>
                        <span class="absolute text-xs font-bold">60%</span>
                    </div>
                </div>
                <div class="bg-white p-4 rounded-xl shadow-sm border-b-4 border-yellow-500">
                    <p class="text-[10px] text-gray-500 font-bold uppercase mb-2">Janji Bayar</p>
                    <div class="relative inline-flex items-center justify-center">
                        <svg class="w-16 h-16 transform -rotate-90">
                            <circle class="text-gray-200" stroke-width="6" stroke="currentColor" fill="transparent" r="28" cx="32" cy="32" />
                            <circle class="text-yellow-500" stroke-width="6" stroke-dasharray="175.9" stroke-dashoffset="{{ 175.9 - (175.9 * 45) / 100 }}" stroke-linecap="round" stroke="currentColor" fill="transparent" r="28" cx="32" cy="32" />
                        </svg>
                        <span class="absolute text-xs font-bold">45%</span>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-xl shadow-sm mb-8">
                <p class="text-xs font-bold text-gray-400 uppercase mb-4 tracking-wider">Histori Kunjungan 7 Hari Terakhir</p>
                <div class="h-64">
                    <canvas id="visitChart"></canvas>
                </div>
            </div>

            <h2 class="text-2xl font-bold mb-6">Menu Aplikasi</h2>

            <div class="menu-grid">
                <a href="javascript:void(0)" onclick="transitionToKunjungan()" class="btn-menu">
                    <i class="fa-solid fa-users text-4xl mb-3"></i>
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
    </div> 
    
    <footer class="p2k-footer mt-auto"> Sistem Aplikasi P2K </footer>

    <div id="modalRencana" class="fixed inset-0 z-[999] hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" onclick="closeModal('modalRencana')"></div>
        <div class="inline-block overflow-hidden text-left align-bottom transition-all transform bg-white rounded-lg shadow-xl sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
            <div class="px-6 py-4 border-b bg-blue-p2k text-white flex justify-between">
                <h3 class="text-lg font-bold">Daftar Seluruh Rencana Kunjungan</h3>
                <button onclick="closeModal('modalRencana')" class="text-2xl">&times;</button>
            </div>
            <div class="p-6 max-h-[60vh] overflow-y-auto">
                <table class="w-full text-sm text-left border">
                    <thead class="bg-gray-100 uppercase text-xs">
                        <tr>
                            <th class="px-4 py-2 border">No Ang</th>
                            <th class="px-4 py-2 border">Nama Nasabah</th>
                            <th class="px-4 py-2 border">Alamat</th>
                            <th class="px-4 py-2 border text-center">KOL</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($detail_rencana as $res)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-2 border">{{ $res->no_angsuran }}</td>
                            <td class="px-4 py-2 border font-bold">{{ $res->nama_nasabah }}</td>
                            <td class="px-4 py-2 border">{{ $res->alamat_nasabah }}</td>
                            <td class="px-4 py-2 border text-center">
                                <span class="px-2 py-1 rounded {{ $res->kol == 5 ? 'bg-red-500 text-white' : 'bg-gray-200' }}">{{ $res->kol }}</span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div id="modalKOL5" class="fixed inset-0 z-[999] hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75" onclick="closeModal('modalKOL5')"></div>
        <div class="relative bg-white rounded-lg shadow-xl max-w-4xl w-full overflow-hidden">
            <div class="px-6 py-4 border-b bg-red-600 text-white flex justify-between">
                <h3 class="text-lg font-bold"><i class="fa-solid fa-triangle-exclamation mr-2"></i> Nasabah Prioritas (KOL 5)</h3>
                <button onclick="closeModal('modalKOL5')" class="text-2xl">&times;</button>
            </div>
            <div class="p-6">
                <table class="w-full text-sm text-left border">
                    <thead class="bg-red-50 text-red-700">
                        <tr>
                            <th class="px-4 py-2 border">Nama Nasabah</th>
                            <th class="px-4 py-2 border">Alamat</th>
                            <th class="px-4 py-2 border">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($detail_kol5 as $k5)
                        <tr class="hover:bg-red-50">
                            <td class="px-4 py-2 border font-bold">{{ $k5->nama_nasabah }}</td>
                            <td class="px-4 py-2 border">{{ $k5->alamat_nasabah }}</td>
                            <td class="px-4 py-2 border text-red-600 font-semibold italic">Wajib Dikunjungi Segera</td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="p-4 text-center">Tidak ada nasabah KOL 5</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div id="modalSudah" class="fixed inset-0 z-[999] hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75" onclick="closeModal('modalSudah')"></div>
        <div class="relative bg-white rounded-lg shadow-xl max-w-4xl w-full overflow-hidden">
            <div class="px-6 py-4 border-b bg-green-600 text-white flex justify-between">
                <h3 class="text-lg font-bold">Nasabah Telah Dikunjungi</h3>
                <button onclick="closeModal('modalSudah')" class="text-2xl">&times;</button>
            </div>
            <div class="p-6">
                <table class="w-full text-sm text-left border">
                    <thead class="bg-green-50 uppercase text-xs">
                        <tr>
                            <th class="px-4 py-2 border">Tgl Kunjungan</th>
                            <th class="px-4 py-2 border">Nama Nasabah</th>
                            <th class="px-4 py-2 border text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($detail_sudah_dikunjungi as $sdh)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-2 border text-xs">{{ \Carbon\Carbon::parse($sdh->created_at)->format('d/m/Y H:i') }}</td>
                            <td class="px-4 py-2 border font-bold">{{ $sdh->nama_nasabah }}</td>
                            <td class="px-4 py-2 border text-center">
                                <span class="bg-green-100 text-green-700 px-2 py-1 rounded text-xs font-bold">{{ $sdh->ada_di_lokasi }}</span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

    <script>
      const ctx = document.getElementById('visitChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: @json($labels), 
                datasets: [
                    {
                        label: 'Nasabah Ada',
                        data: @json($nasabahAda),
                        backgroundColor: '#0E5E7E', 
                        borderRadius: 6,
                        maxBarThickness: 30
                    },
                    {
                        label: 'Nasabah Tidak Ada',
                        data: @json($nasabahTidakAda), 
                        backgroundColor: '#F38120', 
                        borderRadius: 6,
                        maxBarThickness: 30
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true, 
                        position: 'top',
                        labels: { usePointStyle: true }
                    }
                },
                scales: {
                    x: { grid: { display: false } },
                    y: { 
                        beginAtZero: true, 
                        ticks: { stepSize: 1 }
                    }
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
            loader.style.display = 'flex';
            setTimeout(() => {
                window.location.href = "{{ route('data-kunjungan') }}";
            }, 800); 
        }

        function transitionToPage(targetPage) {
            const loader = document.getElementById('loginLoading');
            loader.style.display = 'flex';
            setTimeout(() => {
                window.location.href = "{{ route('data-kunjungan') }}?page=" + targetPage;
            }, 800); 
        }

        function loadMenuWithTransition(targetPage) {
            const loader = document.getElementById('loginLoading');
            loader.style.display = 'flex';
            setTimeout(() => {
                window.location.href = `{{ route('data-kunjungan') }}?page=${targetPage}`;
            }, 800); 
        }

        function confirmLogout() {
            Swal.fire({
                title: 'Logout?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3f36b1',
                confirmButtonText: 'Ya, Keluar'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('logout-form').submit();
                }
            });
        }

        function openModal(modalId) {
            document.getElementById(modalId).classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeModal(modalId) {
            document.getElementById(modalId).classList.add('hidden');
            document.body.style.overflow = 'auto';
        }
    </script>
</body>
</html>