<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

/**
 * Base Abstract Notification untuk standarisasi Real-Time WebSocket.
 * 
 * Class ini secara otomatis akan menjalankan fitur Queue & Broadcast.
 * Sub-class hanya perlu memanggil parent::__construct() dengan payload wajib.
 */
abstract class TsuRealtimeNotification extends Notification implements ShouldQueue, ShouldBroadcast
{
    use Queueable;

    public $title;
    public $message;
    public $action_url;
    public $action_text;
    public $module;
    public $icon;
    public $is_silent;
    public $options = [];

    /**
     * Create a new notification instance.
     *
     * @param string $message Pesan notifikasi
     * @param string|null $module Nama modul untuk injeksi badge dinamis (contoh: 'cuti', 'lembur')
     * @param string $action_url URL untuk membuka detail (opsional, default: '#')
     * @param string $action_text Teks tombol aksi (opsional, default: 'Proses')
     * @param string $title Judul Notifikasi (opsional, default: 'Notifikasi Sistem')
     * @param string $icon Class FontAwesome Icon (opsional, default: 'fas fa-bell text-secondary')
     * @param bool $is_silent Jika true, tidak akan menambah angka pada badge notifikasi di UI
     * @param array $options Data tambahan fleksibel (opsional, contoh: ['role' => 'atasan'])
     */
    public function __construct(
        $message, 
        $module = null, 
        $action_url = '#', 
        $action_text = 'Proses', 
        $title = 'Notifikasi Sistem', 
        $icon = 'fas fa-bell text-secondary', 
        $is_silent = false,
        $options = []
    ) {
        $this->message = $message;
        $this->module = $module;
        $this->action_url = $action_url;
        $this->action_text = $action_text;
        $this->title = $title;
        $this->icon = $icon;
        $this->is_silent = $is_silent;
        $this->options = $options;
    }

    /**
     * Menentukan channel pengiriman notifikasi.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database', 'broadcast'];
    }

    /**
     * Data yang akan disimpan ke dalam database table `notifications`.
     * (Untuk riwayat dan Inbox statis).
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return array_merge([
            'title' => $this->title,
            'message' => $this->message,
            'action_url' => $this->action_url,
            'action_text' => $this->action_text,
            'module' => $this->module,
            'icon' => $this->icon,
            'is_silent' => $this->is_silent,
        ], $this->options ?: []);
    }

    /**
     * Payload spesifik yang dilempar via WebSocket (Reverb / Pusher).
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\BroadcastMessage
     */
    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage(array_merge([
            'title' => $this->title,
            'message' => $this->message,
            'action_url' => $this->action_url,
            'action_text' => $this->action_text,
            'module' => $this->module,
            'icon' => $this->icon,
            'is_silent' => $this->is_silent,
        ], $this->options ?: []));
    }
}
