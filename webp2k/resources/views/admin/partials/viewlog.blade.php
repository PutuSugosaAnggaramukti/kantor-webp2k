<div class="page-title" style="margin-bottom: 25px;">
    <h2 style="font-size: 24px; font-weight: 800; color: #000; margin-bottom: 5px;">Viewlog Aktivitas</h2>
    <p style="font-size: 14px; font-weight: 600;">
        <span onclick="window.location.href='/admin/dashboard'" style="cursor:pointer; color:#4e4bc1;">Dashboard</span>
        <span style="margin: 0 5px;">></span>
        <span style="color: #007bff;">Viewlog</span>
    </p>
</div>

{{-- Filter Aktivitas --}}
<div style="display: flex; gap: 8px; flex-wrap: wrap; align-items: center; margin-bottom: 18px;">
    <input type="text" id="vl-cari" value="{{ request('cari') }}" placeholder="🔍 Cari nama / aktivitas / keterangan..."
        style="flex: 1; min-width: 220px; padding: 8px 12px; border: 1px solid #28a745; border-radius: 4px; font-size: 13px;">

    <select id="vl-tipe" style="padding: 8px 10px; border: 1px solid #28a745; border-radius: 4px; font-size: 13px; background: #fff;">
        <option value="">Semua Pengguna</option>
        <option value="Admin" {{ request('tipe') === 'Admin' ? 'selected' : '' }}>Admin</option>
        <option value="AO" {{ request('tipe') === 'AO' ? 'selected' : '' }}>AO</option>
        <option value="Tamu" {{ request('tipe') === 'Tamu' ? 'selected' : '' }}>Gagal / Tamu</option>
    </select>

    <input type="date" id="vl-tanggal" value="{{ request('tanggal') }}" title="Filter tanggal"
        style="padding: 8px 10px; border: 1px solid #28a745; border-radius: 4px; font-size: 13px;">

    <button type="button" id="vl-reset"
        style="padding: 8px 15px; border: 1px solid #6c757d; background: #fff; border-radius: 4px; font-size: 13px; cursor: pointer; font-weight: 600;">
        Reset
    </button>
</div>

<div class="table-responsive">
    <table style="width: 100%; border-collapse: collapse; border: 2px solid #000;">
        <thead>
            <tr style="background: #f0f0f0; border-bottom: 2px solid #000;">
                <th style="padding: 10px; border-right: 2px solid #000; width: 145px;">Waktu</th>
                <th style="padding: 10px; border-right: 2px solid #000; width: 170px;">Pengguna</th>
                <th style="padding: 10px; border-right: 2px solid #000; width: 190px;">Aktivitas</th>
                <th style="padding: 10px; border-right: 2px solid #000;">Keterangan</th>
                <th style="padding: 10px; width: 130px;">IP Address</th>
            </tr>
        </thead>
        <tbody>
            @forelse($logs as $log)
                <tr style="border-bottom: 2px solid #000; text-align: center;">
                    <td style="padding: 10px; border-right: 2px solid #000; font-size: 12px;">
                        {{ \Carbon\Carbon::parse($log->created_at)->format('d-m-Y H:i:s') }}
                    </td>
                    <td style="padding: 10px; border-right: 2px solid #000; font-size: 12px;">
                        <b>{{ $log->name ?? '-' }}</b><br>
                        <span style="background: {{ $log->user_type === 'Admin' ? '#4e4bc1' : ($log->user_type === 'AO' ? '#28a745' : '#6c757d') }}; color: #fff; padding: 2px 8px; border-radius: 10px; font-size: 10px; font-weight: 700; display: inline-block; margin-top: 4px;">{{ $log->user_type }}</span>
                    </td>
                    <td style="padding: 10px; border-right: 2px solid #000; font-size: 12px; font-weight: 700;">
                        {{ $log->action }}
                    </td>
                    <td style="padding: 10px; border-right: 2px solid #000; font-size: 12px; text-align: left;">
                        {{ $log->description ?? '-' }}
                    </td>
                    <td style="padding: 10px; font-size: 12px;">{{ $log->ip_address ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="padding: 25px; text-align: center; font-weight: 600; color: #666;">
                        Belum ada aktivitas tercatat.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div style="margin-top: 20px; display: flex; justify-content: center;">
        {{ $logs->links() }}
    </div>
</div>

<script>
// Handler filter dipasang sekali (delegated), aman saat konten di-inject ulang
if (!window.__viewlogInit) {
    window.__viewlogInit = true;

    window.vlLoad = function(url) {
        const area = document.getElementById('main-content-area');
        if (!area) return;
        area.style.opacity = '0.3';
        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' } })
            .then(r => r.text())
            .then(html => {
                area.innerHTML = html;
                area.style.opacity = '1';
                window.scrollTo({ top: 0, behavior: 'smooth' });
            })
            .catch(() => { area.style.opacity = '1'; });
    };

    window.vlReload = function() {
        const cari = (document.getElementById('vl-cari') || {}).value || '';
        const tipe = (document.getElementById('vl-tipe') || {}).value || '';
        const tanggal = (document.getElementById('vl-tanggal') || {}).value || '';
        const params = new URLSearchParams();
        if (cari) params.set('cari', cari);
        if (tipe) params.set('tipe', tipe);
        if (tanggal) params.set('tanggal', tanggal);
        const qs = params.toString();
        window.vlLoad('/admin/viewlog-content' + (qs ? '?' + qs : ''));
    };

    let vlTimer;
    $(document).on('input', '#vl-cari', function() {
        clearTimeout(vlTimer);
        vlTimer = setTimeout(window.vlReload, 400);
    });
    $(document).on('change', '#vl-tipe, #vl-tanggal', window.vlReload);
    $(document).on('click', '#vl-reset', function() {
        const cari = document.getElementById('vl-cari');
        const tipe = document.getElementById('vl-tipe');
        const tanggal = document.getElementById('vl-tanggal');
        if (cari) cari.value = '';
        if (tipe) tipe.value = '';
        if (tanggal) tanggal.value = '';
        window.vlLoad('/admin/viewlog-content');
    });
}
</script>
