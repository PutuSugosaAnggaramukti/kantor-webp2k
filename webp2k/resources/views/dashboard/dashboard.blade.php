<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIPANTAU P2K</title>
    
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
                        <span>SIPANTAU</span>
                        <strong>P2K</strong>
                    </div>
                </div>
            </div>

            <div class="user-area">
                <button class="user-badge user-trigger" id="userTrigger">
                    <span class="user-name">{{ Auth::guard('karyawan')->user()->nama }}</span>
                    <img src="{{ asset('assets/avatar.png') }}" class="user-avatar">
                </button>
            
                <div class="user-dropdown" id="userDropdown">
                    <a href="javascript:void(0)" onclick="transitionToPage('pengaturan')" class="dropdown-item">Edit Profil</a>
                    <a href="javascript:void(0)" class="dropdown-item logout" onclick="confirmLogout()">Logout</a>
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
            <div class="stat-grid mb-8" style="grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); display: grid; gap: 1.5rem;">
                <div class="card-stat bg-blue-p2k cursor-pointer transition transform hover:scale-105" onclick="openModal('modalRencana')">
                    <p class="text-lg font-semibold text-white">Total Rencana</p>
                    <p class="text-4xl font-bold mt-2 text-white">{{ $total_rencana }}</p>
                    <p class="text-xs opacity-75 mt-2 text-white"><i class="fa-solid fa-magnifying-glass"></i> Klik detail rencana</p>
                </div>

                <div class="card-stat bg-green-p2k cursor-pointer transition transform hover:scale-105" onclick="openModal('modalSudah')">
                    <p class="text-lg font-semibold text-white">Sudah Dikunjungi</p>
                    <p class="text-4xl font-bold mt-2 text-white">{{ $total_kunjungan }}</p>
                    <p class="text-xs opacity-75 mt-2 text-white"><i class="fa-solid fa-magnifying-glass"></i> Klik detail kunjungan</p>
                </div>

                <div class="card-stat cursor-pointer transition transform hover:scale-105" 
                    style="background-color: #e74c3c; color: white; padding: 1.5rem; border-radius: 1rem;" 
                    onclick="openModal('modalBelum')"> 
                    <p class="text-lg font-semibold">Belum Dikunjungi</p>
                    <p class="text-4xl font-bold mt-2">{{ $belum_dikunjungi }}</p>
                    <p class="text-xs opacity-75 mt-2"><i class="fa-solid fa-triangle-exclamation"></i> Sisa target hari ini</p>
                </div>
            </div>
            <div class="card-stat cursor-pointer transition transform hover:scale-105" 
                style="background-color: #8e44ad; color: white; padding: 1.5rem; border-radius: 1rem;" 
                onclick="openModalGagal()"> <p class="text-lg font-semibold text-white">Gagal Kunjungan</p>
                <p class="text-4xl font-bold mt-2 text-white">{{ $total_gagal_kunjungan }}</p>
                <p class="text-xs opacity-75 mt-2 text-white">
                    <i class="fa-solid fa-magnifying-glass"></i> Klik detail gagal
                </p>
            </div>

           <h2 class="text-2xl font-bold mb-6 italic text-gray-700">Persentase Performa</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8 text-center">
                
                <div class="bg-white p-4 rounded-xl shadow-sm border-b-4 border-blue-500 cursor-pointer hover:shadow-md transition" onclick="openModal('modalRencana')">
                    <p class="text-[10px] text-gray-500 font-bold uppercase mb-2">Target Harian</p>
                    <div class="relative inline-flex items-center justify-center">
                        <svg class="w-16 h-16 transform -rotate-90">
                            <circle class="text-gray-200" stroke-width="6" stroke="currentColor" fill="transparent" r="28" cx="32" cy="32" />
                            <circle class="text-blue-600" stroke-width="6" stroke-dasharray="175.9" 
                                stroke-dashoffset="{{ 175.9 - (175.9 * ($kpi['target'] ?? 0)) / 100 }}" 
                                stroke-linecap="round" stroke="currentColor" fill="transparent" r="28" cx="32" cy="32" />
                        </svg>
                        <span class="absolute text-xs font-bold">{{ $kpi['target'] ?? 0 }}%</span>
                    </div>
                </div>

                <div class="bg-white p-4 rounded-xl shadow-sm border-b-4 border-green-500 cursor-pointer hover:shadow-md transition" onclick="openModal('modalSuccessRate')">
                    <p class="text-[10px] text-gray-500 font-bold uppercase mb-2">Success Rate</p>
                    <div class="relative inline-flex items-center justify-center">
                        <svg class="w-16 h-16 transform -rotate-90">
                            <circle class="text-gray-200" stroke-width="6" stroke="currentColor" fill="transparent" r="28" cx="32" cy="32" />
                            <circle class="text-green-500" stroke-width="6" stroke-dasharray="175.9" 
                                stroke-dashoffset="{{ 175.9 - (175.9 * ($kpi['success'] ?? 0)) / 100 }}" 
                                stroke-linecap="round" stroke="currentColor" fill="transparent" r="28" cx="32" cy="32" />
                        </svg>
                        <span class="absolute text-xs font-bold">{{ $kpi['success'] ?? 0 }}%</span>
                    </div>
                </div>

                <div class="bg-white p-4 rounded-xl shadow-sm border-b-4 border-yellow-500 cursor-pointer hover:shadow-md transition" onclick="openModal('modalJanjiBayar')">
                    <p class="text-[10px] text-gray-500 font-bold uppercase mb-2">Janji Bayar</p>
                    <div class="relative inline-flex items-center justify-center">
                        <svg class="w-16 h-16 transform -rotate-90">
                            <circle class="text-gray-200" stroke-width="6" stroke="currentColor" fill="transparent" r="28" cx="32" cy="32" />
                            <circle class="text-yellow-500" stroke-width="6" stroke-dasharray="175.9" 
                                stroke-dashoffset="{{ 175.9 - (175.9 * ($kpi['janji'] ?? 0)) / 100 }}" 
                                stroke-linecap="round" stroke="currentColor" fill="transparent" r="28" cx="32" cy="32" />
                        </svg>
                        <span class="absolute text-xs font-bold">{{ $kpi['janji'] ?? 0 }}%</span>
                    </div>
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
                <a href="javascript:void(0)" onclick="openModalIjin()" class="btn-menu">
                    <i class="fa-solid fa-user-clock text-4xl mb-3"></i>
                    <span class="font-semibold">Ijin Kunjungan</span>
                </a>
            </div>
        </main>
    </div> 
    
    <footer class="p2k-footer mt-auto text-center py-4 text-white-400 text-[10px] tracking-widest uppercase"> 
    SIPANTAU P2K 2026 
    </footer>

    <div id="modalSuccessRate" class="fixed inset-0 z-[999] hidden overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 py-6">
            <div class="fixed inset-0 bg-gray-900 bg-opacity-50" onclick="closeModal('modalSuccessRate')"></div>
            <div class="relative bg-white rounded-lg shadow-xl max-w-4xl w-full overflow-hidden">
                <div class="px-6 py-4 border-b bg-green-600 text-white flex justify-between items-center">
                    <h3 class="text-lg font-bold">Nasabah Berhasil Ditemui (Success)</h3>
                    <button onclick="closeModal('modalSuccessRate')" class="text-2xl font-bold">&times;</button>
                </div>
                <div class="p-6 max-h-[70vh] overflow-y-auto">
                    <table class="w-full text-sm text-left border">
                        <thead class="bg-gray-100 uppercase text-xs">
                            <tr>
                                <th class="px-4 py-2 border">Nasabah</th>
                                <th class="px-4 py-2 border text-center">Tanggal</th>
                                <th class="px-4 py-2 border">Hasil/Catatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($detail_sudah_dikunjungi->where('ada_di_lokasi', 'Ada') as $item)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2 border font-bold">{{ $item->nama_nasabah }}</td>
                                <td class="px-4 py-2 border text-center">
                                    {{ \Carbon\Carbon::parse($item->created_at)->format('d/m/Y') }}
                                </td>
                                <td class="px-4 py-2 border">{{$item->catatan ?? '-' }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="3" class="text-center py-4 italic">Belum ada kunjungan sukses</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div id="modalJanjiBayar" class="fixed inset-0 z-[999] hidden overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 py-6">
            <div class="fixed inset-0 bg-gray-900 bg-opacity-50" onclick="closeModal('modalJanjiBayar')"></div>
            <div class="relative bg-white rounded-lg shadow-xl max-w-4xl w-full overflow-hidden">
                <div class="px-6 py-4 border-b bg-yellow-500 text-white flex justify-between items-center">
                    <h3 class="text-lg font-bold">Nasabah Janji Bayar</h3>
                    <button onclick="closeModal('modalJanjiBayar')" class="text-2xl font-bold">&times;</button>
                </div>
                <div class="p-6 max-h-[70vh] overflow-y-auto">
                    <table class="w-full text-sm text-left border">
                        <thead class="bg-gray-100 uppercase text-xs">
                            <tr>
                                <th class="px-4 py-2 border">Nama Nasabah</th>
                                <th class="px-4 py-2 border text-center">Tgl Janji Bayar</th>
                                <th class="px-4 py-2 border">Catatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($detail_sudah_dikunjungi->whereNotNull('tgl_janji_bayar') as $janji)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2 border font-bold">{{ $janji->nama_nasabah }}</td>
                                <td class="px-4 py-2 border text-center text-blue-600 font-bold">
                                    {{ \Carbon\Carbon::parse($janji->tgl_janji_bayar)->format('d/m/Y') }}
                                </td>
                                <td class="px-4 py-2 border text-xs">{{ $janji->catatan ?? '-' }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="3" class="text-center py-4 italic">Belum ada janji bayar terinput</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div id="modalRencana" class="fixed inset-0 z-[999] hidden overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 py-6">
            <div class="fixed inset-0 bg-gray-900 bg-opacity-50" onclick="closeModal('modalRencana')"></div>
            <div class="relative bg-white rounded-lg shadow-xl max-w-4xl w-full overflow-hidden">
                <div class="px-6 py-4 border-b bg-blue-600 text-white flex justify-between items-center">
                    <h3 class="text-lg font-bold">Daftar Rencana Admin</h3>
                    <button onclick="closeModal('modalRencana')" class="text-2xl font-bold">&times;</button>
                </div>
                <div class="p-6 max-h-[70vh] overflow-y-auto">
                    <table class="w-full text-sm text-left border">
                        <thead class="bg-gray-100 uppercase text-xs">
                            <tr>
                                <th class="px-4 py-2 border">Nama Nasabah</th>
                                <th class="px-4 py-2 border text-center">Tgl Rencana</th> <th class="px-4 py-2 border text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                        @forelse($detail_rencana as $rencana)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-2 border font-bold">{{ $rencana->nama_nasabah }}</td>
                                    <td class="px-4 py-2 border text-center text-gray-600">
                                        {{ \Carbon\Carbon::parse($rencana->created_at)->format('d/m/Y') }}
                                    </td>
                                    <td class="px-4 py-2 border text-center">
                                        @php
                                            $isSudah = in_array($rencana->nama_nasabah, $nama_sudah_visit);
                                        @endphp
                                        <span class="px-2 py-1 rounded-full text-[10px] {{ $isSudah ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                                            {{ $isSudah ? 'Sudah' : 'Belum' }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-center py-4 italic">Tidak ada rencana kunjungan</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

   <div id="modalSudah" class="fixed inset-0 z-[999] hidden overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 py-6">
            <div class="fixed inset-0 bg-gray-900 bg-opacity-50" onclick="closeModal('modalSudah')"></div>
            <div class="relative bg-white rounded-lg shadow-xl max-w-4xl w-full overflow-hidden">
                <div class="px-6 py-4 border-b bg-green-600 text-white flex justify-between items-center">
                    <h3 class="text-lg font-bold">Detail Realisasi Kunjungan</h3>
                    <button onclick="closeModal('modalSudah')" class="text-2xl font-bold">&times;</button>
                </div>
                <div class="p-6 max-h-[70vh] overflow-y-auto">
                    <table class="w-full text-sm text-left border">
                        <thead class="bg-gray-100 uppercase text-xs">
                            <tr>
                                <th class="px-4 py-2 border">Nasabah</th>
                                <th class="px-4 py-2 border text-center">Tanggal Kunjung</th>
                                <th class="px-4 py-2 border">Catatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($detail_sudah_dikunjungi as $item)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2 border font-bold">{{ $item->nama_nasabah }}</td>
                                <td class="px-4 py-2 border text-center text-gray-600">
                                    {{ \Carbon\Carbon::parse($item->created_at)->format('d/m/Y') }}
                                </td>
                                <td class="px-4 py-2 border text-xs">{{ $item->catatan ?? '-' }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="3" class="text-center py-4 italic">Belum ada data kunjungan</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

   <div id="modalBelum" class="fixed inset-0 z-[999] hidden overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 py-6">
            <div class="fixed inset-0 bg-gray-900 bg-opacity-50" onclick="closeModal('modalBelum')"></div>
            <div class="relative bg-white rounded-lg shadow-xl max-w-4xl w-full overflow-hidden">
                <div class="px-6 py-4 border-b bg-red-600 text-white flex justify-between items-center">
                    <h3 class="text-lg font-bold">Sisa Target (Belum Dikunjungi)</h3>
                    <button onclick="closeModal('modalBelum')" class="text-2xl font-bold">&times;</button>
                </div>
                <div class="p-6 max-h-[70vh] overflow-y-auto">
                    <table class="w-full text-sm text-left border">
                        <thead class="bg-gray-100 uppercase text-xs">
                            <tr>
                                <th class="px-4 py-2 border">Nama Nasabah</th>
                                <th class="px-4 py-2 border text-center">Tgl Rencana</th> <th class="px-4 py-2 border text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($detail_belum_dikunjungi as $belum)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2 border font-bold">{{ $belum->nama_nasabah }}</td>
                                <td class="px-4 py-2 border text-center text-gray-600">
                                    {{ \Carbon\Carbon::parse($belum->created_at)->format('d/m/Y') }}
                                </td>
                                <td class="px-4 py-2 border text-center">
                                    <a href="{{ route('data-kunjungan') }}" class="text-blue-600 hover:underline text-xs font-bold">Kunjungi Sekarang</a>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="3" class="text-center py-4 italic">Semua target sudah dikunjungi!</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div id="modalKOL5" class="fixed inset-0 z-[999] hidden overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 py-6">
            <div class="fixed inset-0 bg-gray-900 bg-opacity-50" onclick="closeModal('modalKOL5')"></div>
            <div class="relative bg-white rounded-lg shadow-xl max-w-4xl w-full overflow-hidden">
                <div class="px-6 py-4 border-b bg-red-600 text-white flex justify-between items-center">
                    <h3 class="text-lg font-bold">Nasabah HB Done (KOL 5)</h3>
                    <button onclick="closeModal('modalKOL5')" class="text-2xl font-bold">&times;</button>
                </div>
                <div class="p-6 max-h-[70vh] overflow-y-auto">
                    <table class="w-full text-sm text-left border">
                        <thead class="bg-gray-100 uppercase text-xs">
                            <tr>
                                <th class="px-4 py-2 border">Nama Nasabah</th>
                                <th class="px-4 py-2 border text-center">Status</th>
                                <th class="px-4 py-2 border">Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($detail_kol5 as $kol)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2 border font-bold">{{ $kol->nama_nasabah }}</td>
                                <td class="px-4 py-2 border text-center">
                                    <span class="px-2 py-1 rounded text-[10px] {{ $kol->status_label == 'Sudah Dikunjungi' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                        {{ $kol->status_label }}
                                    </span>
                                </td>
                                <td class="px-4 py-2 border text-xs">{{ $kol->alamat ?? '-' }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="3" class="text-center py-4 italic">Data tidak ditemukan</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

   <div id="modalIjin" class="fixed inset-0 z-[1000] hidden overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 py-6">
            <div class="fixed inset-0 bg-gray-900 bg-opacity-60 transition-opacity" onclick="closeModalIjin()"></div>
            
            <div class="relative bg-white rounded-2xl shadow-2xl max-w-lg w-full overflow-hidden border-2 border-black transform transition-all shadow-[8px_8px_0px_0px_rgba(0,0,0,1)]">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-2xl font-black text-gray-800">Pengajuan Ijin</h2>
                        <button onclick="closeModalIjin()" class="text-gray-500 hover:text-black text-2xl font-bold">&times;</button>
                    </div>
                    
                    <form id="formIjinKunjungan">
                        @csrf
                        <div class="mb-4 text-left">
                            <label class="block text-sm font-black text-gray-700 mb-2">Tanggal Ijin</label>
                            <input type="date" name="tanggal" class="w-full p-3 border-2 border-black rounded-xl font-bold" value="{{ date('Y-m-d') }}" required>
                        </div>

                        <div class="mb-4 text-left">
                            <label class="block text-sm font-black text-gray-700 mb-2">Jenis Ijin</label>
                            <select name="jenis_ijin" class="w-full p-3 border-2 border-black rounded-xl font-bold" required>
                                <option value="Sakit">Sakit</option>
                                <option value="Ijin">Ijin Keperluan Pribadi</option>
                                <option value="Tugas Kantor">Tugas Kantor Luar</option>
                                <option value="Lainnya">Lainnya</option>
                            </select>
                        </div>

                        <div class="mb-6 text-left">
                            <label class="block text-sm font-black text-gray-700 mb-2">Alasan / Keterangan</label>
                            <textarea name="alasan" rows="3" class="w-full p-3 border-2 border-black rounded-xl font-bold" placeholder="Berikan alasan singkat..." required></textarea>
                        </div>

                        <button type="button" onclick="submitIjin()" class="w-full bg-orange-500 text-white font-black py-4 rounded-xl border-2 border-black shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] active:shadow-none active:translate-x-[2px] active:translate-y-[2px] transition-all">
                            KIRIM PENGAJUAN
                        </button>
                    </form>

                    <div class="mt-8 pt-6 border-t-2 border-dashed border-gray-300">
                        <h3 class="text-lg font-black text-gray-800 mb-4 flex items-center">
                            <i class="fas fa-history mr-2 text-orange-500"></i> Riwayat & Status
                        </h3>
                        
                        <div class="max-h-60 overflow-y-auto">
                            <table class="w-full text-xs text-left border-collapse">
                                <thead class="sticky top-0 bg-gray-100">
                                    <tr>
                                        <th class="p-2 border border-black font-black">Tgl</th>
                                        <th class="p-2 border border-black font-black">Jenis</th>
                                        <th class="p-2 border border-black font-black text-center">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($list_pengajuan as $ijin)
                                    <tr class="hover:bg-gray-50 font-bold text-gray-700">
                                        <td class="p-2 border border-gray-200">{{ date('d/m/y', strtotime($ijin->tanggal)) }}</td>
                                        <td class="p-2 border border-gray-200">{{ $ijin->jenis_ijin }}</td>
                                        <td class="p-2 border border-gray-200 text-center">
                                            @if($ijin->status == 'pending')
                                                <span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded border border-yellow-400 text-[10px]">PENDING</span>
                                            @elseif($ijin->status == 'disetujui')
                                                <span class="bg-green-100 text-green-800 px-2 py-1 rounded border border-green-400 text-[10px]">DISETUJUI</span>
                                            @else
                                                <span class="bg-red-100 text-red-800 px-2 py-1 rounded border border-red-400 text-[10px]">DITOLAK</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="3" class="p-4 text-center text-gray-400 font-bold italic">Belum ada pengajuan ijin.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    </div>
            </div>
        </div>
    </div>

    <div id="modalDetailGagal" class="fixed inset-0 z-[1100] hidden overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-black bg-opacity-60 transition-opacity" onclick="closeModalGagal()"></div>
            
            <div class="relative bg-white rounded-2xl shadow-2xl max-w-md w-full overflow-hidden border-2 border-black transform transition-all shadow-[8px_8px_0px_0px_rgba(142,68,173,1)]">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-xl font-black text-gray-800 uppercase italic tracking-tight">Riwayat Gagal Kunjungan</h2>
                        <button onclick="closeModalGagal()" class="text-gray-500 hover:text-black text-2xl font-bold">&times;</button>
                    </div>

                    <div class="overflow-hidden rounded-xl border-2 border-black">
                        <table class="w-full text-sm text-left">
                            <thead class="bg-purple-600 text-white border-b-2 border-black">
                                <tr>
                                    <th class="p-3 font-bold uppercase text-[11px]">Tanggal</th>
                                    <th class="p-3 font-bold uppercase text-[11px]">Alasan / Ket</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white">
                                @forelse($list_gagal_kunjungan as $gagal)
                                <tr class="border-b border-gray-200 hover:bg-purple-50 transition-colors">
                                    <td class="p-3 font-black text-gray-700 whitespace-nowrap">{{ date('d/m/Y', strtotime($gagal->tanggal)) }}</td>
                                    <td class="p-3 text-gray-600 italic leading-tight">"{{ $gagal->alasan }}"</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="2" class="p-8 text-center text-gray-400 font-bold italic">
                                        <i class="fa-solid fa-circle-check text-3xl mb-2 block text-gray-200"></i>
                                        Tidak ada riwayat gagal.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <button onclick="closeModalGagal()" class="w-full mt-6 bg-gray-900 text-white font-black py-4 rounded-xl border-2 border-black shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] active:shadow-none active:translate-x-[2px] active:translate-y-[2px] transition-all uppercase text-xs">
                        Tutup Riwayat
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        const trigger = document.getElementById('userTrigger');
        const dropdown = document.getElementById('userDropdown');

        if(trigger) {
            trigger.addEventListener('click', () => {
                dropdown.classList.toggle('active');
            });
        }

        document.addEventListener('click', (e) => {
            if (trigger && !trigger.contains(e.target) && !dropdown.contains(e.target)) {
                dropdown.classList.remove('active');
            }
        });

        // Transition Functions
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

        // Modal Functions
        function openModal(modalId) {
            // Tutup semua modal yang mungkin masih terbuka agar tidak numpuk
            document.querySelectorAll('.fixed.inset-0.z-\\[999\\]').forEach(m => {
                m.classList.add('hidden');
            });

            const modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            } else {
                console.error("Modal '" + modalId + "' tidak ditemukan!");
            }
        }

        function closeModal(modalId) {
            const modal = document.getElementById(modalId);
            if(modal) {
                modal.classList.add('hidden');
                document.body.style.overflow = 'auto';
            }
        }

        function openModalIjin() {
            document.getElementById('modalIjin').classList.remove('hidden');
        }

        function closeModalIjin() {
            document.getElementById('modalIjin').classList.add('hidden');
        }

        function submitIjin() {
            const form = document.getElementById('formIjinKunjungan');
            const formData = new FormData(form);

            // Tampilkan Loading (Opsional jika pakai SweetAlert)
            Swal.fire({ title: 'Mengirim...', allowOutsideClick: false, didOpen: () => { Swal.showLoading() } });

            fetch("{{ route('user.ijin.store') }}", {
                method: "POST",
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': "{{ csrf_token() }}",
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                // Ubah data.success menjadi data.message
                if (data.message) { 
                    Swal.fire('Berhasil!', data.message, 'success').then(() => {
                        closeModalIjin();
                        form.reset(); 
                        location.reload(); // Tambahkan ini agar halaman refresh dan data muncul
                    });
                } else if (data.error) {
                    // Menangani error yang kita kirim dari catch di Controller
                    Swal.fire('Gagal', data.error, 'error');
                }
            })
        }

        function openModalGagal() {
            document.getElementById('modalDetailGagal').classList.remove('hidden');
            document.body.style.overflow = 'hidden'; // Stop scroll background
        }

        function closeModalGagal() {
            document.getElementById('modalDetailGagal').classList.add('hidden');
            document.body.style.overflow = 'auto'; // Re-enable scroll
        }
    </script>
</body>
</html>