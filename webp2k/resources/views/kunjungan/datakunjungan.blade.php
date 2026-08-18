<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIPANTAU P2K</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/kunjunganuser.css') }}">

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <style>
        .btn-excel { cursor: pointer; transition: 0.3s; }
        .btn-excel:hover { background-color: #16a34a !important; }
        .nav-item { cursor: pointer; }
        .status-circle { width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto; }
        .bg-success { background-color: #22c55e; color: white; }
        .bg-pending { border: 2px solid #1e293b; color: #1e293b; }
    </style>
</head>
<body>

    <div class="wrapper">
        <div class="sidebar">
            <h1>Menu</h1>

            {{-- TOMBOL KEMBALI KE DASHBOARD USER --}}
         <a href="javascript:void(0)" 
            onclick="window.location.href='/user/dashboard'" 
            class="nav-item {{ request()->is('user/dashboard*') ? 'active' : '' }}" 
            id="menu-dashboard">
                <i class="fa-solid fa-gauge-high"></i> Dashboard Utama
         </a>

            {{-- Menu yang sudah ada sebelumnya --}}
            <a href="javascript:void(0)" onclick="loadPage('data-kunjungan')" class="nav-item {{ request()->is('data-kunjungan*') ? 'active' : '' }}" id="menu-data">
                <i class="fa-solid fa-user-plus"></i> Data Kunjungan
            </a>
            
            <a href="javascript:void(0)" onclick="loadPage('laporan-kunjungan')" class="nav-item {{ request()->is('laporan-kunjungan*') ? 'active' : '' }}" id="menu-laporan">
                <i class="fa-solid fa-file-lines"></i> Laporan Kunjungan
            </a>
            
            <a href="javascript:void(0)" onclick="loadPage('dokumen')" class="nav-item {{ request()->is('dokumen*') ? 'active' : '' }}" id="menu-dokumen">
                <i class="fa-solid fa-file-invoice"></i> Dokumen
            </a>
            
            <a href="javascript:void(0)" onclick="loadPage('pengaturan')" class="nav-item {{ request()->is('pengaturan*') ? 'active' : '' }}" id="menu-pengaturan">
                <i class="fa-solid fa-gear"></i> Pengaturan
            </a>
        </div>

        <div class="main-container" style="flex: 1; display: flex; flex-direction: column;">
            <div class="header">
                <div class="logo-section">
                    <img src="{{ asset('assets/logo.png') }}" alt="Logo">
                    <span>SIPANTAU<br>P2K</span>
                </div>
               <div class="user-profile" style="position: relative; cursor: pointer;">
                    <span style="font-weight: 700;">{{ Auth::guard('karyawan')->user()->nama }}</span>
                    <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::guard('karyawan')->user()->nama) }}&background=4e4bc1&color=fff" alt="User">
                    <i class="fa-solid fa-chevron-down" style="font-size: 12px; margin-left: 5px; color: #666;"></i>

                    <div id="userDropdown" style="display: none; position: absolute; right: 0; top: 50px; width: 150px; background: white; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); z-index: 1000; border: 1px solid #eee;">
                        <ul style="list-style: none; margin: 0; padding: 0;">
                            <li style="padding: 10px 15px; border-bottom: 1px solid #eee; font-size: 14px; color: #333;">
                                <i class="fa-solid fa-user" style="margin-right: 8px;"></i> Profil
                            </li>
                            <li style="padding: 0;">
                                <form action="{{ route('logout') }}" method="POST" id="logout-form">
                                    @csrf
                                    <button type="submit" style="width: 100%; text-align: left; padding: 10px 15px; background: none; border: none; color: #e74c3c; cursor: pointer; font-family: inherit; font-size: 14px; font-weight: 600;">
                                        <i class="fa-solid fa-right-from-bracket" style="margin-right: 8px;"></i> Logout
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="main-content">
                <div id="konten-utama">
                    @include('kunjungan.partials.data_table')
                </div>
            </div>
        </div>
    </div>

    <div class="footer">
        Sistem Aplikasi P2K
    </div>

    @include('kunjungan.partials.modals')

  <script>
    // --- State Global ---
    let fileSiapUpload = null;
    let lastKnownCoordinate = null;
    let gpsWatchId = null;
    let koordinatDariFotoExif = null;
    const formatRp = new Intl.NumberFormat('id-ID');

    /**
     * Parser EXIF GPS murni JavaScript (tanpa library).
     * Membaca koordinat langsung dari file foto (JPG/JPEG) yang dipilih user,
     * sehingga tetap berfungsi di HP yang API geolocation-nya gagal (mis. VIVO).
     */
    function bacaExifGpsDariFile(file) {
        return new Promise((resolve) => {
            if (!file) { resolve(null); return; }
            const reader = new FileReader();
            reader.onload = (e) => {
                try {
                    resolve(ekstrakGpsExif(e.target.result));
                } catch (err) {
                    resolve(null);
                }
            };
            reader.onerror = () => resolve(null);
            reader.readAsArrayBuffer(file);
        });
    }

    function ekstrakGpsExif(buffer) {
        const dv = new DataView(buffer);
        if (dv.getUint8(0) !== 0xFF || dv.getUint8(1) !== 0xD8) return null;

        // Cari segmen APP1 yang berisi "Exif\0\0"
        let offset = 2;
        let tiffStart = -1;
        while (offset + 4 < buffer.byteLength) {
            if (dv.getUint8(offset) !== 0xFF) { offset++; continue; }
            const marker = dv.getUint8(offset + 1);
            if (marker === 0xE1) {
                const length = dv.getUint16(offset + 2);
                if (dv.getUint32(offset + 4) === 0x45786966 && dv.getUint16(offset + 8) === 0) {
                    tiffStart = offset + 10;
                    break;
                }
            }
            if (marker >= 0xD0 && marker <= 0xD7) { offset += 2; continue; }
            if (marker === 0xD8 || marker === 0xD9) { offset += 1; continue; }
            offset += 2 + dv.getUint16(offset + 2);
        }
        if (tiffStart < 0) return null;

        const le = dv.getUint16(tiffStart) === 0x4949; // II = little endian
        const u16 = (off) => dv.getUint16(off, le);
        const u32 = (off) => dv.getUint32(off, le);
        const b = (off) => dv.getUint8(off);

        if (u16(tiffStart + 2) !== 42) return null;

        const ifd0 = tiffStart + u32(tiffStart + 4);
        const nEntries = u16(ifd0);
        let gpsIfdOffset = -1;
        for (let i = 0; i < nEntries; i++) {
            const entry = ifd0 + 2 + i * 12;
            if (u16(entry) === 0x8825) gpsIfdOffset = u32(entry + 8); // GPSInfo
        }
        if (gpsIfdOffset < 0) return null;

        const gpsIfd = tiffStart + gpsIfdOffset;
        const gpsNum = u16(gpsIfd);

        const readRational = (off) => {
            const num = u32(off);
            const den = u32(off + 4);
            return den !== 0 ? num / den : 0;
        };

        let latRef = 'N', lonRef = 'E', latArr = null, lonArr = null;
        for (let i = 0; i < gpsNum; i++) {
            const entry = gpsIfd + 2 + i * 12;
            const tag = u16(entry);
            const type = u16(entry + 2);
            const valueOffset = entry + 8;
            switch (tag) {
                case 0x0001: latRef = String.fromCharCode(b(valueOffset)); break;
                case 0x0002: {
                    const off = type === 5 ? tiffStart + u32(valueOffset) : valueOffset;
                    latArr = [readRational(off), readRational(off + 8), readRational(off + 16)];
                    break;
                }
                case 0x0003: lonRef = String.fromCharCode(b(valueOffset)); break;
                case 0x0004: {
                    const off = type === 5 ? tiffStart + u32(valueOffset) : valueOffset;
                    lonArr = [readRational(off), readRational(off + 8), readRational(off + 16)];
                    break;
                }
            }
        }
        if (!latArr || !lonArr) return null;

        let lat = latArr[0] + latArr[1] / 60 + latArr[2] / 3600;
        let lon = lonArr[0] + lonArr[1] / 60 + lonArr[2] / 3600;
        if (latRef === 'S') lat = -lat;
        if (lonRef === 'W') lon = -lon;
        if (lat === 0 && lon === 0) return null; // GPS tidak terkunci
        return { lat, lon };
    }

    /**
     * Kunci koordinat dari EXIF GPS foto yang dipilih user.
     * Prioritas utama (mengalahkan geolocation) karena koordinat EXIF = lokasi asli foto.
     */
    async function kunciKoordinatDariFoto(fileInput, koordinatId, statusId) {
        if (!fileInput || !fileInput.files || fileInput.files.length === 0) return false;
        const input = document.getElementById(koordinatId);
        const status = document.getElementById(statusId);

        for (let i = 0; i < fileInput.files.length; i++) {
            const gps = await bacaExifGpsDariFile(fileInput.files[i]);
            if (!gps) continue;
            const loc = gps.lat.toFixed(6) + ', ' + gps.lon.toFixed(6);
            koordinatDariFotoExif = loc;
            lastKnownCoordinate = loc;
            if (input) input.value = loc;
            if (status) {
                status.innerHTML = '<span style="color: #27ae60;"><i class="fas fa-check-circle"></i> Koordinat dari foto (EXIF) terkunci.</span>';
            }
            return true;
        }
        return false;
    }

    /**
     * Cek apakah string koordinat valid (bukan kosong, bukan 0,0 dalam format apapun).
     */
    function isKoordValid(val) {
        if (!val) return false;
        const parts = String(val).split(',');
        if (parts.length < 2) return false;
        const lat = parseFloat(parts[0]);
        const lon = parseFloat(parts[1]);
        if (isNaN(lat) || isNaN(lon)) return false;
        return !(lat === 0 && lon === 0);
    }

    function bacaGpsDariFotoDipilih(fileInput) {
        const form = fileInput.closest('form');
        const isManual = form && form.id === 'formKunjunganMandiri';
        const koordinatId = isManual ? 'manual-koordinat' : 'form-koordinat';
        const statusId = isManual ? 'manual-location-status' : 'location-status';
        kunciKoordinatDariFoto(fileInput, koordinatId, statusId);
    }

    /**
     * Isi field waktu_laporan dengan jam REAL dari HP user (bukan jam server).
     * Format: YYYY-MM-DD HH:MM:SS (zona waktu lokal perangkat).
     */
    function setWaktuLaporanHp(inputId) {
        const el = document.getElementById(inputId);
        if (!el) return;
        const d = new Date();
        const pad = (n) => String(n).padStart(2, '0');
        el.value = `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())} ` +
                   `${pad(d.getHours())}:${pad(d.getMinutes())}:${pad(d.getSeconds())}`;
    }

    // --- Fungsi Navigasi & Load Halaman ---
    function loadPage(pageName) {
        const contentArea = document.getElementById('konten-utama');
        contentArea.style.opacity = '0.3';

        fetch(`/user/${pageName}-content`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => {
            if (!response.ok) throw new Error('Gagal memuat halaman');
            return response.text();
        })
        .then(html => {
            contentArea.innerHTML = html;
            contentArea.style.opacity = '1';
            updateSidebarActive(pageName);
        })
        .catch(error => {
            console.error('Fetch error:', error);
            contentArea.style.opacity = '1';
        });
    }

    function loadContent(url) {
        const contentArea = document.getElementById('konten-utama');
        contentArea.style.opacity = '0.3';

        $.ajax({
            url: url,
            type: 'GET',
            success: function(response) {
                $('#konten-utama').html(response);
                contentArea.style.opacity = '1';
            },
            error: function(xhr) {
                contentArea.style.opacity = '1';
                alert('Gagal memuat konten.');
            }
        });
    }

    function updateSidebarActive(pageName) {
        document.querySelectorAll('.nav-item').forEach(el => el.classList.remove('active'));
        const menuMap = {
            'laporan-kunjungan': 'menu-laporan',
            'dokumen': 'menu-dokumen',
            'pengaturan': 'menu-pengaturan'
        };
        const targetId = menuMap[pageName] || 'menu-data';
        const activeEl = document.getElementById(targetId);
        if (activeEl) activeEl.classList.add('active');
    }

    // --- Fungsi Modal (General) ---
   function openModal(jadwalId, nama, kodeAO, noAngsuran) {
        document.getElementById('form-jadwal-id').value = jadwalId;

        document.getElementById('form-no-nasabah').value = noAngsuran;
        document.getElementById('form-nama-nasabah').value = nama;
        document.getElementById('display-kode-ao').value = kodeAO;
        document.getElementById('display-nama').value = nama;

        document.getElementById('visitModal').style.display = 'flex';

        updateGPSLocation('form-koordinat', 'location-status');
    }

    function openDetailModal(kode, angsuran, nama, alamat, nominal, sisa, kol, kodeAo, namaAo) {
        document.getElementById('detail-kode').innerText = kode || '-';
        document.getElementById('detail-angsuran').innerText = angsuran || '-';
        document.getElementById('detail-nama').innerText = nama || '-';
        document.getElementById('detail-alamat').innerText = alamat || '-';

        const parseNominal = (val) => val ? 'Rp ' + formatRp.format(Number(val.toString().replace(/[^0-9.-]+/g,""))) : 'Rp 0';
        document.getElementById('detail-nominal').innerText = parseNominal(nominal);
        document.getElementById('detail-sisa').innerText = parseNominal(sisa);

        document.getElementById('detail-kol').innerText = kol || '-';
        document.getElementById('detail-kode-ao').innerText = kodeAo || '-';
        document.getElementById('detail-nama-ao').innerText = namaAo || '-';
        document.getElementById('detailModal').style.display = 'flex';
    }

    function closeModal() {
        const modals = ['visitModal', 'detailModal', 'modalManual'];
        modals.forEach(id => {
            const el = document.getElementById(id);
            if (el) {
                el.style.display = 'none';
                const form = el.querySelector('form');
                if (form) form.reset();
            }
        });
        // Hentikan watchPosition agar tidak membuang baterai & tidak menimpa sesi
        if (gpsWatchId !== null) {
            navigator.geolocation.clearWatch(gpsWatchId);
            gpsWatchId = null;
        }
        const statusText = document.getElementById('location-status');
        if (statusText) statusText.innerHTML = '';
        fileSiapUpload = null;
    }

   function updateGPSLocation(inputId, statusId) {
        const input = document.getElementById(inputId);
        const status = document.getElementById(statusId);

        if (status) status.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Mencari lokasi (Pastikan GPS HP Aktif)...';

        // Prefill cepat dari cache sesi jika ada (field tidak pernah kosong),
        // TAPI tetap jalankan watchPosition agar koordinat disegarkan ke lokasi terkini.
        // (Jangan return di sini: bila hanya pakai cache, koordinat bisa stale atau kosong
        //  jika kunjungan sebelumnya gagal terkunci.)
        if (typeof lastKnownCoordinate === 'string' && lastKnownCoordinate) {
            if (input) input.value = lastKnownCoordinate;
        }

        if (!navigator.geolocation) {
            if (status) status.innerHTML = "Browser tidak mendukung GPS";
            return;
        }

        // Hentikan watch lama (jika masih berjalan) agar tidak dobel
        if (gpsWatchId !== null) {
            navigator.geolocation.clearWatch(gpsWatchId);
            gpsWatchId = null;
        }

        // METODE UTAMA: watchPosition (terus-menerus mencari fix hingga dapat lokasi).
        // getCurrentPosition hanya request sekali dan sering gagal di HP non-Xiaomi
        // karena sinyal GPS butuh waktu > timeout. watchPosition jauh lebih andal.
        gpsWatchId = navigator.geolocation.watchPosition(
            (pos) => {
                // Jangan kunci 0,0 (GPS belum fix)
                if (pos.coords.latitude === 0 && pos.coords.longitude === 0) return;
                const loc = `${pos.coords.latitude}, ${pos.coords.longitude}`;
                lastKnownCoordinate = loc;
                if (input) input.value = loc;
                if (status) {
                    const akurasi = Math.round(pos.coords.accuracy);
                    // Stop watch jika akurasi sudah cukup bagus (< 100m)
                    if (pos.coords.accuracy <= 100 && gpsWatchId !== null) {
                        navigator.geolocation.clearWatch(gpsWatchId);
                        gpsWatchId = null;
                    }
                    status.innerHTML = `<span style="color: #28a745;"><i class="fas fa-check-circle"></i> Lokasi Terkunci (Akurasi: ${akurasi}m)</span>`;
                }
            },
            (error) => {
                // Watch gagal sesaat: jangan langsung menyerah, coba sekali lagi
                if (status && error.code !== error.PERMISSION_DENIED) {
                    status.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sinyal GPS dicari...';
                }
            },
            {
                enableHighAccuracy: true,
                timeout: 30000,      // lebih panjang karena watch menunggu fix satelit
                maximumAge: 0
            }
        );

        // FALLBACK: coba sekali getCurrentPosition dgn akurasi rendah (WiFi/seluler) agar cepat
        navigator.geolocation.getCurrentPosition(
            (pos) => {
                if (pos.coords.latitude === 0 && pos.coords.longitude === 0) return;
                const loc = `${pos.coords.latitude}, ${pos.coords.longitude}`;
                lastKnownCoordinate = loc;
                if (input) input.value = loc;
                if (status) {
                    const akurasi = Math.round(pos.coords.accuracy);
                    status.innerHTML = `<span style="color: #28a745;"><i class="fas fa-check-circle"></i> Lokasi Terkunci (Akurasi: ${akurasi}m)</span>`;
                }
            },
            (error) => {
                // Tidak menyerah: biarkan watchPosition terus mencoba
                if (status) status.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memperkuat sinyal GPS...';
            },
            {
                enableHighAccuracy: false,  // akurasi rendah = fix cepat lewat WiFi/seluler
                timeout: 8000,
                maximumAge: 0
            }
        );
    }

    function showGeoError(error, status) {
        let pesanError = "GPS Error";
        switch(error.code) {
            case error.PERMISSION_DENIED:
                pesanError = "Izin Lokasi Ditolak Browser!";
                break;
            case error.POSITION_UNAVAILABLE:
                pesanError = "Sinyal GPS Tidak Tersedia";
                break;
            case error.TIMEOUT:
                pesanError = "Waktu Tunggu Habis (Sinyal Lemah)";
                break;
        }

        Swal.fire({
            title: 'GPS Tidak Terkunci',
            html: `<p>${pesanError}</p>
                <hr>
                <p style="font-size: 14px; text-align: left;">
                    <b>Petunjuk:</b><br>
                    1. Pastikan GPS HP Aktif dan izin lokasi diberikan.<br>
                    2. Jika masih gagal, <b>mohon cantumkan link Share Location</b> dari Google Maps pada kolom <b>Hasil Kunjungan</b>.<br>
                    3. Foto yang diupload tetap akan diverifikasi via data GPS (EXIF) foto.
                </p>`,
            icon: 'warning',
            confirmButtonText: 'Saya Mengerti'
        });

        if (status) status.innerHTML = `<span style="color: #dc3545;"><i class="fas fa-times-circle"></i> ${pesanError}</span>`;
    }

    function confirmDeleteJadwal(id, nama, noAngsuran) { 
    Swal.fire({
        title: 'Hapus Jadwal?',
        html: `Apakah Anda yakin ingin menghapus jadwal?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="fa fa-trash"></i> Ya, Hapus!',
        cancelButtonText: 'Batal',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Mohon Tunggu',
                text: 'Sedang menghapus data...',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });

            // GUNAKAN ID UNTUK URL
            let url = "{{ route('hapus.jadwal', ':id') }}"; 
            url = url.replace(':id', id); 

            fetch(url, {
                method: 'DELETE', // Gunakan DELETE sesuai route:list Mas tadi
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}' // Tambahkan CSRF Token agar tidak error 419
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Terhapus!',
                        text: data.success,
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire('Gagal!', data.error || 'Terjadi kesalahan.', 'error');
                }
            })
            .catch(error => {
                Swal.fire('Error!', 'Koneksi ke server terputus.', 'error');
            });
        }
    });
}
    // --- Manajemen Akun & Settings ---
    function switchSettingsTab(tab) {
        const isAkun = tab === 'akun';
        document.getElementById('section-akun').style.display = isAkun ? 'block' : 'none';
        document.getElementById('section-sandi').style.display = isAkun ? 'none' : 'block';
        
        document.getElementById('tab-btn-akun').classList.toggle('tab-active', isAkun);
        document.getElementById('tab-btn-sandi').classList.toggle('tab-active', !isAkun);
    }

    async function handleAjaxSettings(route, bodyData, successMsg) {
        const token = document.querySelector('meta[name="csrf-token"]')?.content || document.querySelector('input[name="_token"]')?.value;
        try {
            const response = await fetch(route, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token },
                body: JSON.stringify(bodyData)
            });
            const res = await response.json();
            if (response.ok) {
                Swal.fire('Berhasil!', successMsg || res.success, 'success');
                return res;
            }
            throw new Error(res.error || 'Terjadi kesalahan');
        } catch (e) {
            Swal.fire('Gagal', e.message, 'error');
        }
    }

    // --- Avatar Management ---
    function previewAvatar(input) {
        if (input.files && input.files[0]) {
            fileSiapUpload = input.files[0];
            const reader = new FileReader();
            reader.onload = (e) => document.getElementById('display-avatar').src = e.target.result;
            reader.readAsDataURL(fileSiapUpload);
        }
    }

    async function simpanAvatarKeServer() {
        if (!fileSiapUpload) return Swal.fire('Info', 'Pilih foto terlebih dahulu!', 'info');
        
        const formData = new FormData();
        formData.append('avatar', fileSiapUpload);
        const token = document.querySelector('meta[name="csrf-token"]').content;

        try {
            const response = await fetch("{{ route('settings.avatar') }}", {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': token }
            });
            const res = await response.json();
            if (response.ok) {
                Swal.fire('Berhasil!', 'Avatar diperbarui', 'success');
                const url = res.url + '?t=' + new Date().getTime();
                document.querySelectorAll('.profile-avatar-img, .user-profile img, #display-avatar').forEach(img => img.src = url);
                fileSiapUpload = null;
            }
        } catch (e) { Swal.fire('Error', 'Gagal upload', 'error'); }
    }

    // --- Event Listeners ---
    document.addEventListener('DOMContentLoaded', function() {
        // Deep linking page
        const targetPage = new URLSearchParams(window.location.search).get('page');
        if (targetPage) loadPage(targetPage);

        // Alert Session
        @if(session('success'))
            Swal.fire({ icon: 'success', title: 'Berhasil!', text: "{{ session('success') }}", timer: 2500, showConfirmButton: false });
        @endif

        @if($errors->any())
            Swal.fire({ icon: 'error', title: 'Oops...', text: 'Periksa kembali inputan Anda' });
        @endif
    });

    window.addEventListener('keydown', (e) => { if (e.key === 'Escape') closeModal(); });

    document.addEventListener('click', function (e) {
        // Dropdown Toggle
        if (e.target.closest('.user-profile')) {
            const dd = document.getElementById("userDropdown");
            dd.style.display = (dd.style.display === "block") ? "none" : "block";
        } else if (!e.target.closest('.user-profile')) {
            if(document.getElementById("userDropdown")) document.getElementById("userDropdown").style.display = "none";
        }

        // Close Modal on Overlay Click
        if (e.target.classList.contains('modal-overlay') || e.target.id === 'visitModal' || e.target.id === 'detailModal') {
            closeModal();
        }
    });

    document.getElementById('btn-save-kunjungan').addEventListener('click', function(e) {
    e.preventDefault(); // Mencegah submit default browser

    const btn = this; // Simpan referensi tombol
    const form = btn.closest('form');

    // 1. Cek jika sedang loading (mencegah double click)
    if (btn.disabled) return;

    // 2. Konfirmasi awal sebelum simpan
    Swal.fire({
        title: 'Simpan Laporan?',
        text: "Pastikan foto yang diunggah memiliki data GPS (EXIF).",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3f36b1',
        cancelButtonColor: '#cbd5e1',
        confirmButtonText: 'Ya, Simpan!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            
            // LOCK: Matikan tombol dan ganti teksnya
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';

            // 3. Tampilkan Loading (Sangat penting untuk proses upload foto)
            Swal.fire({
                title: 'Sedang Memproses...',
                html: 'Menyimpan data dan memvalidasi lokasi foto.',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // 3b. KUNCI ULANG KOORDINAT saat menyimpan: pastikan field koordinat terisi
            // (kalau tadi modal cepat dibuka sebelum GPS fix, ini kesempatan terakhir)
            lockCoordinateBeforeSave('form-koordinat').then(() => {
                // Kunci juga jam REAL dari HP user
                setWaktuLaporanHp('form-waktu-laporan');
                // Bangun ulang FormData setelah koordinat terakhir diperbarui
                const formData = new FormData(form);
                submitKunjungan(form, formData, btn);
            });
        }
    });
    });

    function lockCoordinateBeforeSave(inputId) {
        return new Promise((resolve) => {
            const input = document.getElementById(inputId);
            // Sudah ada & valid (bukan 0,0 dalam format apapun)? langsung selesai
            if (input && isKoordValid(input.value)) {
                resolve();
                return;
            }

            // Prioritas 1: baca koordinat dari EXIF foto yang dipilih
            // (solusi HP VIVO / foto galeri yang API geolocation-nya gagal)
            const isManual = inputId === 'manual-koordinat';
            const fileInput = isManual
                ? document.querySelector('#modalManual input[name="foto_kunjungan[]"]')
                : document.querySelector('#visitModal input[name="foto_kunjungan[]"]');

            if (fileInput && fileInput.files && fileInput.files.length > 0) {
                kunciKoordinatDariFoto(fileInput, inputId, isManual ? 'manual-location-status' : 'location-status')
                    .then((ok) => {
                        if (ok) { resolve(); return; }
                        tungguFixGps(input, resolve);
                    });
                return;
            }

            tungguFixGps(input, resolve);
        });
    }

    // Menunggu fix GPS dengan sabar (hingga ~20 detik) sebelum menyimpan.
    // Memakai watchPosition (lebih andal daripada getCurrentPosition sekali jalan)
    // yang gagal di banyak HP karena sinyal GPS butuh waktu lebih lama dari timeout.
    function tungguFixGps(input, resolve) {
        if (!navigator.geolocation) { resolve(); return; }

        let watchId = null;
        let settled = false;

        const selesai = (loc) => {
            if (settled) return;
            settled = true;
            if (watchId !== null) {
                navigator.geolocation.clearWatch(watchId);
                watchId = null;
            }
            if (loc) {
                lastKnownCoordinate = loc;
                if (input) input.value = loc;
            }
            resolve();
        };

        const timeoutId = setTimeout(() => selesai(null), 20000);

        watchId = navigator.geolocation.watchPosition(
            (pos) => {
                if (pos.coords.latitude === 0 && pos.coords.longitude === 0) return;
                clearTimeout(timeoutId);
                const loc = `${pos.coords.latitude}, ${pos.coords.longitude}`;
                selesai(loc);
            },
            () => { /* tetap menunggu */ },
            { enableHighAccuracy: true, timeout: 30000, maximumAge: 0 }
        );

        // Juga coba jalur cepat via WiFi/seluler (akurasi rendah)
        navigator.geolocation.getCurrentPosition(
            (pos) => {
                if (pos.coords.latitude === 0 && pos.coords.longitude === 0) return;
                clearTimeout(timeoutId);
                const loc = `${pos.coords.latitude}, ${pos.coords.longitude}`;
                selesai(loc);
            },
            () => { /* biarkan watchPosition lanjut */ },
            { enableHighAccuracy: false, timeout: 8000, maximumAge: 0 }
        );
    }

    function submitKunjungan(form, formData, btn) {
            // 4. Kirim data menggunakan Fetch API
            fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // 5. Notifikasi Berhasil
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: data.success,
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        closeModal();
                        location.reload(); 
                    });
                } else {
                    // UNLOCK: Aktifkan kembali jika gagal
                    btn.disabled = false;
                    btn.innerHTML = 'Ya, Simpan!';

                    // 6. Notifikasi Gagal (Tampilkan pesan validasi jika ada)
                    let msg = data.error || 'Terjadi kesalahan saat menyimpan.';
                    if (data.errors) {
                        msg = Object.values(data.errors).flat().join('<br>');
                    }
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        html: msg,
                    });
                }
            })
            .catch(error => {
                // UNLOCK: Aktifkan kembali jika error koneksi
                btn.disabled = false;
                btn.innerHTML = 'Ya, Simpan!';
                
                console.error('Error:', error);
                // Coba baca isi respons asli server (bisa berupa HTML error 500 / 419) 
                // agar pesan error tidak generik "Gagal terhubung ke server"
                let detailMsg = 'Gagal terhubung ke server. Coba periksa koneksi internet atau muat ulang halaman.';
                if (error instanceof TypeError && error.message === 'Failed to fetch') {
                    // Koneksi benar-benar gagal
                } else if (error.message) {
                    detailMsg = 'Respons server tidak valid: ' + error.message;
                }
                Swal.fire('Error', detailMsg, 'error');
            });
    }

    // --- Handler Pagination AJAX ---
$(document).on('click', '.pagination a', function(e) {
    e.preventDefault();
    
    // AMBIL URL ASLI (Contoh: https://sipantau.../user/data-kunjungan?page=3)
    let url = $(this).attr('href'); 
    
    if (url) {
        const contentArea = document.getElementById('konten-utama');
        contentArea.style.opacity = '0.3';

        $.ajax({
            url: url, // Pakai URL utuh, jangan dipotong-potong
            type: 'GET',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            success: function(response) {
                $('#konten-utama').html(response);
                contentArea.style.opacity = '1';
                window.scrollTo({ top: 0, behavior: 'smooth' });
            },
            error: function(xhr) {
                contentArea.style.opacity = '1';
                console.error("Error pagination detail:", xhr.responseText);
            }
        });
    }
});

function openManualModal() {
    const modal = document.getElementById('modalManual');
    if (modal) {
        modal.style.display = 'flex';
        // Jalankan deteksi GPS untuk modal manual
        updateGPSLocation('manual-koordinat', 'manual-location-status');
    } else {
        Swal.fire('Error', 'Modal mandiri tidak ditemukan di DOM.', 'error');
    }
}

document.getElementById('formKunjunganMandiri').addEventListener('submit', function(e) {
    e.preventDefault(); // Mencegah browser pindah halaman

    const formManual = this;

    // Kunci ulang koordinat sebelum simpan (pastikan manual-koordinat terisi)
    lockCoordinateBeforeSave('manual-koordinat').then(() => {

    // Kunci jam REAL dari HP user
    setWaktuLaporanHp('manual-waktu-laporan');

    // 1. Tampilkan Loading (Penting karena proses validasi GPS foto cukup berat)
    Swal.fire({
        title: 'Sedang Menyimpan...',
        text: 'Memvalidasi data dan lokasi GPS foto.',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    // 2. Ambil data dari form (setelah koordinat terakhir diperbarui)
    const formData = new FormData(formManual);

    // 3. Kirim via Fetch API (AJAX)
    fetch(formManual.action, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // 4. Jika Berhasil
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: data.success,
                timer: 2000,
                showConfirmButton: false
            }).then(() => {
                closeModal(); // Tutup modal
                location.reload(); // Refresh halaman untuk melihat data baru
            });
        } else {
            // 5. Jika Gagal (Error Validasi EXIF, dsb)
            let msg = data.error || 'Terjadi kesalahan sistem.';
            if (data.errors) {
                msg = Object.values(data.errors).flat().join('<br>');
            }
            Swal.fire({
                icon: 'error',
                title: 'Gagal Simpan',
                html: msg
            });
        }
    })
    .catch(error => {
        console.error('Error:', error);
        let detailMsg = 'Gagal terhubung ke server. Coba periksa koneksi internet atau muat ulang halaman.';
        if (error instanceof TypeError && error.message === 'Failed to fetch') {
            // Koneksi benar-benar gagal
        } else if (error.message) {
            detailMsg = 'Respons server tidak valid: ' + error.message;
        }
        Swal.fire('Error', detailMsg, 'error');
    });
    });
});

function simpanJadwalMandiri() {
    // Ambil data dari form
    let formData = {
        _token: $('meta[name="csrf-token"]').attr('content'),
        no_angsuran: $('#no_angsuran').val(),
        nama_nasabah: $('#nama_nasabah').val(),
        alamat_nasabah: $('#alamat_nasabah').val(),
        kol: $('#kol_nasabah').val(),
        bulan: $('#bulan_input').val(),
        tanggal: $('#tanggal_kunjungan').val()
    };

    // Validasi sederhana sebelum kirim
    if (!formData.no_angsuran || !formData.tanggal) {
        Swal.fire('Peringatan', 'Mohon pilih nasabah dan tanggal kunjungan!', 'warning');
        return;
    }

    $.ajax({
        url: "{{ route('ao.kunjungan.store') }}", // Arahkan ke KunjunganController
        type: "POST",
        data: formData,
        beforeSend: function() {
            Swal.showLoading();
        },
        success: function(response) {
            if (response.success) {
                Swal.fire('Berhasil!', response.message, 'success').then(() => {
                    // Refresh halaman atau reset form
                    location.reload(); 
                });
            }
        },
        error: function(xhr) {
            let errorMsg = xhr.responseJSON ? xhr.responseJSON.message : 'Terjadi kesalahan server';
            Swal.fire('Gagal!', errorMsg, 'error');
        }
    });
}

function openModalAO() {
    document.getElementById('modalTambahJadwalAO').style.display = 'block';
}

function closeModalAO() {
    document.getElementById('modalTambahJadwalAO').style.display = 'none';
    $('#formTambahJadwalAO')[0].reset();
    $('#txt_alamat, #txt_kol').text('-');
}

function openModalUbahJadwal() {
    const modal = document.getElementById('modalEditJadwalGlobal');
    if (modal) {
        modal.classList.add('show');
        document.body.style.overflow = 'hidden';
    }
}

function closeModalUbahJadwal() {
    const modal = document.getElementById('modalEditJadwalGlobal');
    if (modal) {
        modal.classList.remove('show');
        document.body.style.overflow = 'auto';
    }
}

$('#select_nasabah').on('change', function() {
    let selected = $(this).find(':selected');
    let no_angsuran = $(this).val();
    let nama = selected.data('nama');
    let alamat = selected.data('alamat');
    let kol = selected.data('kol');
    let kode = selected.data('kode'); // Tambahan baru
    let rekening = selected.data('rekening'); // Tambahan baru

    // Update Tampilan Info (Sesuai permintaan kamu: Kode, Rekening, Alamat)
    $('#txt_kode').text(kode || '-');
    $('#txt_rekening').text(rekening || '-');
    $('#txt_alamat').text(alamat || '-');

    // Update Hidden Input untuk dikirim via AJAX
    $('#no_angsuran').val(no_angsuran);
    $('#nama_nasabah').val(nama);
    $('#alamat_nasabah').val(alamat);
    $('#kol_nasabah').val(kol);
});

$(document).ready(function() {
        

        $(document).on('click', '#toggleCurrentPasswordAO', function() {
            const inputField = $('#current_password');
            const currentType = inputField.attr('type');

            const newType = (currentType === 'password') ? 'text' : 'password';
            inputField.attr('type', newType);
            
      
            $(this).toggleClass('fa-eye fa-eye-slash');
        });


        $(document).on('click', '#toggleNewPasswordAO', function() {
            const inputField = $('#new_password');
            const currentType = inputField.attr('type');
            
            const newType = (currentType === 'password') ? 'text' : 'password';
            inputField.attr('type', newType);
            
            $(this).toggleClass('fa-eye fa-eye-slash');
        });
    });

    

function updateSandiAO() {
    const current_password = $('#current_password').val();
    const new_password = $('#new_password').val();

    if (!current_password || !new_password) {
        Swal.fire('Peringatan', 'Harap isi semua kolom password!', 'warning');
        return;
    }

    handleAjaxSettings("{{ route('settings.sandi') }}", {
        current_password: current_password,
        new_password: new_password
    }, 'Kata sandi berhasil diperbarui!')
    .then(res => {
        if (res && res.success) {
            $('#current_password').val('');
            $('#new_password').val('');
        }
    });
}
</script>


</body>
</html>