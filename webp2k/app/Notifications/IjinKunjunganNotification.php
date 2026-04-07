<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class IjinKunjunganNotification extends Notification
{
    use Queueable;

    private $details;

    /**
     * Kita buat constructor agar bisa menerima data dari Controller
     */
    public function __construct($details)
    {
        $this->details = $details;
    }

    /**
     * Kita ganti 'mail' menjadi 'database' agar tersimpan di tabel notifications
     */
    public function via(object $notifiable): array
    {
        return ['database']; // Pakai database saja, tidak perlu email
    }

    /**
     * Ini bagian paling penting: Data yang akan masuk ke kolom 'data' di database
     */
    public function toArray(object $notifiable): array
    {
        return [
            'id_ijin'    => $this->details['id_ijin'],
            'nama_ao'    => $this->details['nama_ao'] ?? null,
            'pesan'      => $this->details['pesan'],
            'status'     => $this->details['status'],
            'type_notif' => 'ijin_kunjungan', // Penanda agar tidak tertukar dengan notif tagihan
        ];
    }
}