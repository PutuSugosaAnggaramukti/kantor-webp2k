<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    /**
     * Halaman Viewlog (aktivitas user) untuk Admin.
     * Satu method melayani 2 URL: /admin/viewlog (halaman penuh)
     * dan /admin/viewlog-content (partial AJAX via loadAdminPage).
     */
    public function index(Request $request)
    {
        ActivityLog::createTable();

        $logs = ActivityLog::query()
            ->when($request->filled('cari'), function ($q) use ($request) {
                $cari = $request->cari;
                $q->where(function ($w) use ($cari) {
                    $w->where('name', 'like', "%{$cari}%")
                        ->orWhere('action', 'like', "%{$cari}%")
                        ->orWhere('description', 'like', "%{$cari}%");
                });
            })
            ->when($request->filled('tipe'), function ($q) use ($request) {
                $q->where('user_type', $request->tipe);
            })
            ->when($request->filled('tanggal'), function ($q) use ($request) {
                $q->whereDate('created_at', $request->tanggal);
            })
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        $content = view('admin.partials.viewlog', ['logs' => $logs])->render();

        if ($request->ajax()) {
            return response($content);
        }

        return view('admin.datakaryawan', [
            'content' => $content,
        ]);
    }
}
