<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard SIPANTAU - P2K</title>
    <link rel="stylesheet" href="{{ asset('css/admin-dashboard.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
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
            <div style="font-weight: bold; line-height: 1.2;">SIPANTAU <br><span style="color: #3b82f6;">P2K</span></div>
        </div>
        <div class="user-profile-tag">
            <span style="cursor: pointer;" onclick="toggleAdminDropdown()">
                {{ Auth::user()->name }}
            </span>
        
            <div id="adminDropdown" style="display: none; position: absolute; top: 50px; right: 20px; background: white; padding: 10px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); z-index: 100;">
                <a href="javascript:void(0)" onclick="confirmLogout()" style="display: flex; align-items: center; padding: 12px 15px; text-decoration: none; color: #ef4444; font-weight: bold;">
                    <i class="fa-solid fa-right-from-bracket" style="margin-right: 10px;"></i>Logout
                </a>
                <a href="javascript:void(0)" onclick="transitionToAdminPage('pengaturan')" style="display: flex; align-items: center; padding: 12px 15px; text-decoration: none; color: #333; font-weight: 600; font-size: 14px; border-bottom: 1px solid #f5f5f5; transition: background 0.2s;">
                    <i class="fa-solid fa-user-gear" style="margin-right: 10px; color: #3b82f6;"></i> Pengaturan Akun
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
               <h3 style="margin-bottom: 1.5rem;">Statistik Kinerja</h3>
                
                <div class="stats-grid">
                    <div class="stat-card bg-rencana" onclick="showDetail('rencana')">
                        <div class="stat-label">Total Rencana</div>
                        <div class="stat-value">{{ $totalKunjungan ?? 0 }}</div>
                        <i class="fa-solid fa-calendar-days"></i>
                    </div>

                    <div class="stat-card bg-selesai" onclick="showDetail('selesai')">
                        <div class="stat-label">Sudah Dikunjungi</div>
                        <div class="stat-value">{{ $totalSelesai ?? 0 }}</div>
                        <i class="fa-solid fa-circle-check"></i>
                    </div>

                    <div class="stat-card bg-belum" onclick="showDetail('belum')">
                        <div class="stat-label">Belum Dikunjungi</div>
                        <div class="stat-value">{{ $totalBelum ?? 0 }}</div>
                        <i class="fa-solid fa-clock"></i>
                    </div>

                   <div class="stat-card" style="background-color: #e74c3c; color: white;" onclick="showDetail('gagal')">
                        <div class="stat-label">Total Gagal Kunjungan</div>
                        <div id="total-gagal-count" class="stat-value">{{ $total_gagal_global ?? 0 }}</div>
                        <div class="kpi-note">*Berdasarkan ijin AO yang disetujui</div>
                        <i class="fa-solid fa-user-slash"></i>
                    </div>
                </div>

                <h3 style="margin-top: 2rem; margin-bottom: 1.5rem;">Persentase Performa  <small style="font-size: 0.6rem; color: #999;">(Klik untuk detail AO)</small></h3>

                <div class="stats-grid">
                    <div class="stat-card" onclick="openModalAO('target')" style="background: white; color: #333; border-left: 8px solid #3f36b1; cursor: pointer; display: flex; justify-content: space-between; align-items: center; padding: 20px;">
                        <div style="text-align: left;">
                            <div class="stat-label" style="color: #666;">Penyelesaian Target</div>
                            <div style="font-size: 0.75rem; color: #999;">Agregat Seluruh AO</div>
                        </div>
                        <div style="position: relative; width: 70px; height: 70px;">
                            <svg viewBox="0 0 36 36" style="transform: rotate(-90deg); width: 100%; height: 100%;">
                                <circle cx="18" cy="18" r="16" fill="none" stroke="#eee" stroke-width="4"></circle>
                                <circle cx="18" cy="18" r="16" fill="none" stroke="#3f36b1" stroke-width="4" stroke-dasharray="{{ $kpi_target_nasional }}, 100" stroke-linecap="round"></circle>
                            </svg>
                            <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); font-weight: bold;">{{ $kpi_target_nasional }}%</div>
                        </div>
                    </div>
                </div>

                <div style="margin-top: 2rem; background: white; padding: 20px; border-radius: 12px; height: 300px; display:none;">
                    <canvas id="myChart"></canvas>
                </div>

                <h3 style="margin-top: 2rem; margin-bottom: 1.5rem;">Menu Aplikasi</h3>
                
                <div class="menu-grid">
                    <a href="javascript:void(0)" onclick="transitionToAdminPage('data-karyawan')" class="menu-item">
                        <i class="fa-solid fa-users"></i>
                        <span>Data Tim P2K</span>
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
                    <a href="javascript:void(0)" onclick="openModalHistoryIjin()" class="menu-item relative inline-flex items-center">
                        <i class="fa-solid fa-user-clock"></i>
                        <span>Konfirmasi Ijin</span>
                        
                        @if($pengajuan_ijin_count > 0)
                            <span class="badge-notif-ijin absolute -top-1 -right-1 bg-red-600 text-white text-[10px] px-1.5 py-0.5 rounded-full border-2 border-white shadow-sm flex items-center justify-center min-w-[18px] h-[18px]">
                                {{ $pengajuan_ijin_count }}
                            </span>
                        @endif
                    </a>
                    <a href="javascript:void(0)" onclick="transitionToAdminPage('pengaturan')" class="menu-item">
                        <i class="fa-solid fa-gear"></i>
                        <span>Pengaturan</span>
                    </a>
                </div>
            </div>
        </div>
</main>


<div id="statsModal" class="modal-overlay" style="display:none;">
    <div class="modal-content" style="width: 80%; max-width: 900px;">
        <div class="modal-header">
            <h2 id="modalTitle">Detail Data</h2>
            <span class="close-modal" onclick="closeModal()">&times;</span>
        </div>
        <div class="modal-body">
            <div id="modalLoading" style="display:none; text-align:center; padding: 20px;">
                <div class="spinner"></div>
                <p>Mengambil data...</p>
            </div>
            <div id="modalTableContainer" style="overflow-x: auto;">
                </div>
        </div>
    </div>
</div>

<div id="modalAO" style="display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.6); backdrop-filter: blur(5px);">
    <div style="background-color: #fff; margin: 10% auto; padding: 25px; border-radius: 20px; width: 90%; max-width: 450px; box-shadow: 0 5px 15px rgba(0,0,0,0.3); position: relative;">
        <span onclick="closeModalAO()" style="position: absolute; right: 20px; top: 15px; font-size: 28px; cursor: pointer; color: #aaa;">&times;</span>
        
        <h4 id="aoModalTitle" style="font-weight: bold; margin-bottom: 20px; color: #333;">Detail Performa AO</h4>
        
        <div id="aoListContent">
            @foreach($detailPerformaAO as $ao)
            <div style="margin-bottom: 20px;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                    <span style="font-weight: 600; color: #444;">{{ $ao->nama }}</span>
                    <span class="label-persen" data-target="{{ $ao->persen_target }}" data-kol5="{{ $ao->persen_kol5 }}" style="font-weight: bold;">0%</span>
                </div>
                <div style="width: 100%; background-color: #eee; height: 12px; border-radius: 10px; overflow: hidden;">
                    <div class="bar-progres" data-target="{{ $ao->persen_target }}" data-kol5="{{ $ao->persen_kol5 }}" style="width: 0%; height: 100%; border-radius: 10px; transition: width 0.8s ease-in-out;"></div>
                </div>
            </div>
            @endforeach
        </div>
        
        <button onclick="closeModalAO()" style="width: 100%; padding: 10px; border: none; background: #f0f0f0; border-radius: 10px; font-weight: bold; cursor: pointer; margin-top: 10px;">Tutup</button>
    </div>
</div>

<div id="modalHistoryIjin" style="display: none; position: fixed; z-index: 1100; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.7); backdrop-filter: blur(5px);">
    <div style="background-color: #fff; margin: 5% auto; border-radius: 15px; width: 95%; max-width: 900px; box-shadow: 0 5px 15px rgba(0,0,0,0.5); overflow: hidden; border: 2px solid #000;">
        <div style="background: #f97316; color: white; padding: 15px 20px; display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #000;">
            <h3 style="margin: 0; font-weight: 900; text-transform: uppercase; font-style: italic;"><i class="fas fa-history mr-2"></i> Log Ijin Kunjungan AO</h3>
            <span onclick="closeModalHistoryIjin()" style="font-size: 28px; cursor: pointer; font-weight: bold;">&times;</span>
        </div>
        
        <div style="padding: 20px; max-height: 70vh; overflow-y: auto;">
            <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
                <thead>
                    <tr style="background: #f1f5f9; text-align: left; border-bottom: 2px solid #000;">
                        <th style="padding: 12px; border: 1px solid #ddd;">Tgl Ijin</th>
                        <th style="padding: 12px; border: 1px solid #ddd;">AO</th>
                        <th style="padding: 12px; border: 1px solid #ddd;">Jenis</th>
                        <th style="padding: 12px; border: 1px solid #ddd;">Alasan</th>
                        <th style="padding: 12px; border: 1px solid #ddd; text-align: center;">Status & Aksi</th> </tr>
                </thead>
                <tbody style="font-weight: bold;">
                    @forelse($list_pengajuan as $ijin)
                    <tr style="border-bottom: 1px solid #eee;">
                        <td style="padding: 12px; border: 1px solid #eee;">{{ date('d/m/Y', strtotime($ijin->tanggal)) }}</td>
                        <td style="padding: 12px; border: 1px solid #eee;">
                            {{ $ijin->karyawan->nama ?? 'N/A' }} <br>
                            <small style="color: #666;">{{ $ijin->kode_ao }}</small>
                        </td>
                        <td style="padding: 12px; border: 1px solid #eee;">
                            <span style="background: #ffedd5; color: #9a3412; padding: 2px 8px; border-radius: 5px; font-size: 10px; border: 1px solid #fb923c;">
                                {{ $ijin->jenis_ijin }}
                            </span>
                        </td>
                        <td style="padding: 12px; border: 1px solid #eee; font-style: italic; color: #444;">{{ $ijin->alasan }}</td>
                        
                       <td style="padding: 12px; border: 1px solid #eee; text-align: center;">
                            @if($ijin->status == 'pending')
                                <div style="display: flex; gap: 5px; justify-content: center;">
                                    <button onclick="updateIjinStatus({{ $ijin->id }}, 'disetujui')" style="background: #22c55e; color: white; border: 1px solid #000; padding: 4px 8px; border-radius: 5px; cursor: pointer; font-size: 11px; font-weight: bold;">
                                        <i class="fas fa-check"></i> ACC
                                    </button>
                                    <button onclick="updateIjinStatus({{ $ijin->id }}, 'ditolak')" style="background: #ef4444; color: white; border: 1px solid #000; padding: 4px 8px; border-radius: 5px; cursor: pointer; font-size: 11px; font-weight: bold;">
                                        <i class="fas fa-times"></i> Tolak
                                    </button>
                                </div>
                            @elseif($ijin->status == 'disetujui')
                                <div style="display: flex; flex-direction: column; align-items: center; gap: 5px;">
                                    <span style="color: #16a34a; background: #dcfce7; padding: 4px 10px; border-radius: 20px; border: 1px solid #16a34a; font-size: 11px;">
                                        <i class="fas fa-check-circle"></i> Disetujui
                                    </span>
                                    <button onclick="openReassignModal({{ $ijin->id }})" style="background: #4f46e5; color: white; border: none; padding: 2px 8px; border-radius: 4px; font-size: 10px; cursor: pointer;">
                                        <i class="fas fa-share"></i> Oper Jadwal
                                    </button>
                                </div>
                            @else
                                <span style="color: #dc2626; background: #fee2e2; padding: 4px 10px; border-radius: 20px; border: 1px solid #dc2626; font-size: 11px;">
                                    <i class="fas fa-times-circle"></i> Ditolak
                                </span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 30px; color: #999;">Belum ada data pengajuan ijin.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div style="padding: 15px; text-align: right; border-top: 1px solid #eee;">
            <button onclick="closeModalHistoryIjin()" style="padding: 8px 20px; background: #eee; border: 2px solid #000; border-radius: 8px; font-weight: bold; cursor: pointer;">Tutup</button>
        </div>
    </div>
</div>

    <footer class="footer-admin">
        SIPANTAU P2K 2026
    </footer>


    <script>
    function transitionToAdminPage(targetPage) {
        const loader = document.getElementById('loginLoading');
        const loadingText = loader.querySelector('p');
        
        const labels = {
            'data-karyawan': 'Memuat Data Karyawan...',
            'data-kunjungan': 'Memuat Data Kunjungan...', // Ini mengarah ke rute index kunjungan
            'nasabah': 'Memuat Data Nasabah...',
            'pelaporan': 'Memuat Laporan...',
            'dokumen': 'Memuat Dokumen...',
            'adm-kunjungan': 'Memuat Input Jadwal...',
            'pengaturan': 'Memuat Pengaturan...'
        };

        // Peta Route (Sesuaikan dengan name route di web.php Anda)
        const routeMap = {
            'data-karyawan': "{{ route('karyawan.index') }}",
            'data-kunjungan': "{{ route('admin.kunjungan.index') }}", // PENTING: Gunakan route yang benar
            'nasabah': "{{ route('admin.nasabah.index') }}",
            'pelaporan': "{{ route('pelaporan.index') }}",
            'dokumen': "{{ route('admin.dokumen.index') }}",
            'adm-kunjungan': "{{ route('admin.adm-kunjungan.index') }}",
            'pengaturan': "{{ route('admin.pengaturan') }}"
        };

        if (loadingText) {
            loadingText.innerText = labels[targetPage] || 'Memuat Halaman...';
        }

        loader.classList.add('active');
        loader.style.display = 'flex';

        setTimeout(() => {
            // Redirect ke route yang spesifik, bukan cuma karyawan.index
            window.location.href = routeMap[targetPage] + "?page=" + targetPage;
        }, 1000); 
    }

    // FUNGSI CHART DENGAN PENGAMAN
    window.onload = function() {
        const canvasElement = document.getElementById('myChart');
        if (canvasElement) {
            const ctx = canvasElement.getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: {!! json_encode($labels) !!}, 
                    datasets: [{
                        label: 'Jumlah Kunjungan Selesai',
                        data: {!! json_encode($counts) !!},
                        backgroundColor: 'rgba(54, 162, 235, 0.7)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1,
                        borderRadius: 5
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: { beginAtZero: true, ticks: { stepSize: 1 } }
                    }
                }
            });
        }
    };

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
                const loader = document.getElementById('loginLoading');
                if (loader) { loader.style.display = 'flex'; }
                setTimeout(() => {
                    document.getElementById('logout-form').submit();
                }, 800);
            }
        });
    }

    function showDetail(type) {
        const titleMap = {
            'rencana': 'Detail Rencana Kunjungan',
            'selesai': 'Detail Kunjungan Selesai',
            'belum': 'Detail Kunjungan Belum Selesai',
            'target': 'Daftar Nasabah HB yang Sudah Ditangani AO',
            'gagal': 'Daftar AO Gagal Kunjungan (Ijin Disetujui)'
        };

        // --- LOGIKA DINAMIS HEADER ---
        let labelKolomKetiga = (type === 'gagal') ? 'Alasan Ijin' : 'Nama Nasabah';
        
        // Tambahkan header extra jika tipe-nya 'gagal'
        let extraHeader = (type === 'gagal') ? '<th style="padding:12px; border:1px solid #ddd;">Keterangan</th>' : '';

        document.getElementById('modalTitle').innerText = titleMap[type];
        document.getElementById('modalTableContainer').innerHTML = '<div style="text-align:center; padding:20px;"><div class="spinner"></div><p>Sedang memuat...</p></div>';
        document.getElementById('statsModal').style.display = 'flex';

        fetch(`/admin/dashboard-detail/${type}`)
            .then(response => response.json())
            .then(data => {
                let table = `<table style="width:100%; border-collapse: collapse; margin-top:10px; font-size:13px;">
                <thead>
                    <tr style="background:#f1f5f9; text-align:left;">
                        <th style="padding:12px; border:1px solid #ddd;">No</th>
                        <th style="padding:12px; border:1px solid #ddd;">AO (Kode - Nama)</th>
                        <th style="padding:12px; border:1px solid #ddd;">${labelKolomKetiga}</th>
                        <th style="padding:12px; border:1px solid #ddd;">Tanggal</th>
                        <th style="padding:12px; border:1px solid #ddd; text-align:center;">Status</th>
                        ${extraHeader} 
                    </tr>
                </thead>
                <tbody>`;

                if (data.length > 0) {
                    data.forEach((item, index) => {
                        let badgeColor = '#fee2e2'; 
                        let textColor = '#991b1b';
                        
                        if(item.status === 'Sudah Dikunjungi') {
                            badgeColor = '#dcfce7'; 
                            textColor = '#166534';
                        } else if(item.status === 'Rencana') {
                            badgeColor = '#e0f2fe'; 
                            textColor = '#0369a1';
                        } else if(item.status === 'Gagal Kunjungan') {
                            badgeColor = '#ffedd5'; 
                            textColor = '#9a3412';
                        }

                        // --- LOGIKA ISI KOLOM EXTRA ---
                        let extraCell = '';
                        if (type === 'gagal') {
                            // Menampilkan info AO Pengganti jika ada datanya dari backend
                            let ket = item.info_ao_baru 
                                ? `<div style="color: #4f46e5; font-weight: bold;">
                                    <i class="fas fa-exchange-alt"></i> Dioper ke: ${item.info_ao_baru}
                                </div>` 
                                : '<span style="color: #999; font-style: italic;">Belum dioper</span>';
                            
                            extraCell = `<td style="padding:10px; border:1px solid #eee;">${ket}</td>`;
                        }

                        table += `<tr>
                            <td style="padding:10px; border:1px solid #eee; text-align:center;">${index + 1}</td>
                            <td style="padding:10px; border:1px solid #eee; font-weight:bold;">${item.info_1}</td>
                            <td style="padding:10px; border:1px solid #eee;">${item.info_2}</td>
                            <td style="padding:10px; border:1px solid #eee; text-align:center;">${item.info_3}</td>
                            <td style="padding:10px; border:1px solid #eee; text-align:center;">
                                <span style="background:${badgeColor}; color:${textColor}; padding:4px 10px; border-radius:15px; font-size:11px; font-weight:bold;">
                                    ${item.status}
                                </span>
                            </td>
                            ${extraCell}
                        </tr>`;
                    });
                } else {
                    // Sesuaikan colspan jika data kosong (6 kolom jika gagal, 5 jika lainnya)
                    let colSpanCount = (type === 'gagal') ? 6 : 5;
                    table += `<tr><td colspan="${colSpanCount}" style="text-align:center; padding:20px;">Tidak ada data ditemukan.</td></tr>`;
                }

                table += `</tbody></table>`;
                document.getElementById('modalTableContainer').innerHTML = table;
            })
            .catch(error => {
                document.getElementById('modalTableContainer').innerHTML = '<p style="color:red; text-align:center;">Terjadi kesalahan saat mengambil data.</p>';
                console.error('Error:', error);
            });
    }

    function closeModal() {
        document.getElementById('statsModal').style.display = 'none';
    }

    function openModalAO(type) {
        const modal = document.getElementById('modalAO');
        const title = document.getElementById('aoModalTitle');
        const bars = document.querySelectorAll('.bar-progres');
        const labels = document.querySelectorAll('.label-persen');
        
        modal.style.display = 'block';
        
        if (type === 'target') {
            title.innerText = 'Rincian Penyelesaian Target';
            title.style.color = '#3f36b1';
        } else {
            title.innerText = 'Rincian Penyelesaian KOL 5';
            title.style.color = '#e74c3c';
        }

        setTimeout(() => {
            bars.forEach((bar, index) => {
                const value = type === 'target' ? bar.dataset.target : bar.dataset.kol5;
                const color = type === 'target' ? '#3f36b1' : '#e74c3c';
                bar.style.width = value + '%';
                bar.style.backgroundColor = color;
                labels[index].innerText = value + '%';
                labels[index].style.color = color;
            });
        }, 100);
    }

    function closeModalAO() {
        const modal = document.getElementById('modalAO');
        const bars = document.querySelectorAll('.bar-progres');
        bars.forEach(bar => bar.style.width = '0%');
        modal.style.display = 'none';
    }

    window.onclick = function(event) {
        const modal = document.getElementById('modalAO');
        const statsModal = document.getElementById('statsModal');
        if (event.target == modal) closeModalAO();
        if (event.target == statsModal) closeModal();
    }

    // Fungsi untuk Modal History Ijin
   function openModalHistoryIjin() {
        // 1. Tampilkan modalnya
        document.getElementById('modalHistoryIjin').style.display = 'block';

        // 2. HAPUS PAKSA angka merahnya secara visual (Tanpa tunggu server)
        // Ini yang bikin kamu nggak perlu refresh untuk melihat hasilnya
        $('.bg-red-600').remove(); 

        // 3. Update status di database (Background Process)
        $.ajax({
            url: "{{ route('admin.ijin.markAsRead') }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}"
            },
            success: function(response) {
                console.log("Database updated: Notif marked as read.");
            },
            error: function(xhr) {
                console.error("Gagal update database, tapi visual sudah dihapus.");
            }
        });
    }

    function closeModalHistoryIjin() {
        document.getElementById('modalHistoryIjin').style.display = 'none';
    }

    // Tambahkan pengaman klik di luar modal (update window.onclick yang sudah ada)
    const originalOnClick = window.onclick;
    window.onclick = function(event) {
        if (originalOnClick) originalOnClick(event); // Jalankan fungsi asli jika ada
        
        const historyModal = document.getElementById('modalHistoryIjin');
        if (event.target == historyModal) {
            closeModalHistoryIjin();
        }
    }

   function updateIjinStatus(id, status) {
    const warna = status === 'disetujui' ? '#10b981' : '#ef4444';
    
    Swal.fire({
        title: 'Konfirmasi Persetujuan',
        text: `Apakah Anda yakin ingin mengubah status menjadi ${status}?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: warna,
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Ya, Update!',
        cancelButtonText: 'Batal',
        didOpen: () => { Swal.getContainer().style.zIndex = "2000"; }
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({ 
                title: 'Memproses...', 
                allowOutsideClick: false, 
                didOpen: () => { Swal.showLoading(); Swal.getContainer().style.zIndex = "2000"; } 
            });

            fetch(`/admin/ijin-kunjungan/update-status/${id}`, {
                method: "POST",
                headers: {
                    'X-CSRF-TOKEN': "{{ csrf_token() }}",
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ status: status })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (status === 'disetujui') {
                        // JIKA DI-ACC: Tawarkan Oper Jadwal
                        Swal.fire({
                            title: 'Ijin Disetujui!',
                            text: "Ijin berhasil di-ACC. Ingin langsung mengoper jadwal hari ini ke AO lain?",
                            icon: 'success',
                            showCancelButton: true,
                            confirmButtonColor: '#4f46e5',
                            cancelButtonColor: '#6b7280',
                            confirmButtonText: 'Ya, Oper Jadwal',
                            cancelButtonText: 'Nanti Saja',
                            didOpen: () => { Swal.getContainer().style.zIndex = "2000"; }
                        }).then((choice) => {
                            if (choice.isConfirmed) {
                                openReassignModal(id); // Buka modal pilih AO
                            } else {
                                location.reload();
                            }
                        });
                    } else {
                        // JIKA DITOLAK: Langsung reload
                        location.reload();
                    }
                }
            });
        }
    });
}

function openReassignModal(idIjin) {
    Swal.fire({
        title: 'Pilih AO Pengganti',
        html: `
            <select id="swal-select-ao" class="swal2-input" style="width: 85%; font-size: 14px;">
                <option value="">-- Pilih AO Pengganti --</option>
                @foreach($detailPerformaAO as $ao)
                    <option value="{{ $ao->kode_ao }}">{{ $ao->kode_ao }} - {{ $ao->nama }}</option>
                @endforeach
            </select>
        `,
        confirmButtonText: 'Proses Oper',
        confirmButtonColor: '#10b981',
        showCancelButton: true,
        didOpen: () => { Swal.getContainer().style.zIndex = "2000"; },
        preConfirm: () => {
            const aoBaru = document.getElementById('swal-select-ao').value;
            if (!aoBaru) { Swal.showValidationMessage('Silakan pilih AO pengganti!'); }
            return aoBaru;
        }
    }).then((res) => {
        if (res.isConfirmed) {
            Swal.fire({ title: 'Memindahkan Jadwal...', didOpen: () => { Swal.showLoading(); Swal.getContainer().style.zIndex = "2000"; } });
            
            fetch("{{ route('admin.ijin.reassign') }}", {
                method: "POST",
                headers: {
                    'X-CSRF-TOKEN': "{{ csrf_token() }}",
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ 
                    id_ijin: idIjin, 
                    kode_ao_baru: res.value 
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire('Berhasil!', 'Jadwal telah dipindahkan.', 'success').then(() => location.reload());
                } else {
                    Swal.fire('Gagal', data.message || 'Terjadi kesalahan', 'error');
                }
            });
        }
    });
}

function executeReassign(idIjin, aoBaru) {
    // Tampilkan loading lagi
    Swal.fire({ title: 'Memindahkan Jadwal...', didOpen: () => { Swal.showLoading(); Swal.getContainer().style.zIndex = "2000"; } });

    fetch(`{{ route('admin.ijin.reassign') }}`, {
        method: "POST",
        headers: {
            'X-CSRF-TOKEN': "{{ csrf_token() }}",
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ ijin_id: idIjin, ao_baru: aoBaru })
    })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            Swal.fire({ title: 'Berhasil!', text: 'Jadwal telah dioper.', icon: 'success', didOpen: () => { Swal.getContainer().style.zIndex = "2000"; } })
            .then(() => { location.reload(); });
        }
    });

}
    </script>

</body>
</html>