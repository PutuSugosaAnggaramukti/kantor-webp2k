<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

class UpdateStatusNotification extends Notification
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function via($notifiable)
    {
        return ['database']; // Notifikasi disimpan di database
    }

    public function toArray($notifiable)
    {
        return [
            'message' => 'Admin telah memperbarui status nasabah ' . $this->data['nama_nasabah'],
            'status' => $this->data['status'],
            'id_kunjungan' => $this->data['id_kunjungan'],
        ];
    }
}
