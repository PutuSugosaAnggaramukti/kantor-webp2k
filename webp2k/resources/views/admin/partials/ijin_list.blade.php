<div class="page-title" style="margin-bottom: 25px;">
    <h2 style="font-size: 24px; font-weight: 800; color: #000; margin-bottom: 5px;">Pengajuan Ijin Kunjungan</h2>
    <p style="font-size: 14px; font-weight: 600;">
        <span onclick="window.location.href='/admin/dashboard'" style="cursor:pointer; color:#4e4bc1;">Dashboard</span>
        <span style="margin: 0 5px;">></span>
        <span style="color: #007bff;">Ijin Kunjungan</span>
    </p>
</div>

<div class="table-responsive">
    <table style="width: 100%; border-collapse: collapse; border: 2px solid #000; background-color: #fff;">
        <thead>
            <tr style="border-bottom: 2px solid #000; text-align: center; background-color: #fcfcfc;">
                <th style="padding: 15px; border-right: 2px solid #000; width: 60px;">No</th>
                <th style="padding: 15px; border-right: 2px solid #000;">Tanggal</th>
                <th style="padding: 15px; border-right: 2px solid #000;">AO</th>
                <th style="padding: 15px; border-right: 2px solid #000;">Jenis Ijin</th>
                <th style="padding: 15px; border-right: 2px solid #000;">Alasan</th>
                <th style="padding: 15px; border-right: 2px solid #000;">Status</th>
                <th style="padding: 15px;">Aksi</th>
            </tr>
        </thead>
        <tbody style="font-weight: 700; font-size: 14px; text-align: center;">
            @forelse($ijinList as $index => $ijin)
            <tr style="border-bottom: 2px solid #000;">
                <td style="padding: 12px; border-right: 2px solid #000;">{{ $loop->iteration }}</td>
                <td style="padding: 12px; border-right: 2px solid #000;">
                    {{ \Carbon\Carbon::parse($ijin->tanggal)->format('d-m-Y') }}
                </td>
                <td style="padding: 12px; border-right: 2px solid #000;">
                    {{ $ijin->karyawan->nama ?? $ijin->kode_ao }}
                </td>
                <td style="padding: 12px; border-right: 2px solid #000;">{{ $ijin->jenis_ijin }}</td>
                <td style="padding: 12px; border-right: 2px solid #000; text-align: left;">{{ $ijin->alasan }}</td>
                <td style="padding: 12px; border-right: 2px solid #000;">
                    @php
                        $badge = match($ijin->status) {
                            'disetujui' => ['color' => '#155724', 'bg' => '#d4edda'],
                            'ditolak' => ['color' => '#721c24', 'bg' => '#f8d7da'],
                            default => ['color' => '#856404', 'bg' => '#fff3cd'],
                        };
                    @endphp
                    <span style="display:inline-block; padding:4px 10px; border-radius:10px; font-size:11px; background:{{ $badge['bg'] }}; color:{{ $badge['color'] }}; border:1px solid {{ $badge['color'] }};">
                        {{ ucfirst($ijin->status) }}
                    </span>
                </td>
                <td style="padding: 12px;">
                    @if($ijin->status === 'pending')
                        <a href="{{ route('admin.ijin.index') }}?id={{ $ijin->id }}" style="color: #007bff;">Detail</a>
                    @else
                        <span style="color: #999;">-</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="7" style="padding: 20px; text-align: center;">Belum ada pengajuan ijin.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div style="margin-top: 20px; display: flex; justify-content: center;">
        {{ $ijinList->links() }}
    </div>
</div>
