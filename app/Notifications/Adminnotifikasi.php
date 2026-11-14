<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

/**
 * Satu kelas notifikasi generik untuk semua kejadian di panel admin.
 * Isi (judul, pesan, url, ikon, dst.) dibuat oleh App\Support\NotifikasiAdmin.
 */
class AdminNotifikasi extends Notification
{
    public function __construct(public array $payload)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return $this->payload;
    }
}