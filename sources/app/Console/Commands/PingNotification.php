<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Notifications\TsuRealtimeNotification;

// Class sementara khusus untuk Command Ping ini
class ManualPingNotification extends TsuRealtimeNotification {}

class PingNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tsu:ping-notification';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Kirim notifikasi real-time secara manual ke semua user untuk keperluan testing';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $users = User::all();
        $timestamp = date('H:i:s');
        
        $this->info("Menembakkan notifikasi real-time ke " . $users->count() . " user...");

        foreach ($users as $user) {
            $user->notify(new ManualPingNotification(
                "Ping Manual! [$timestamp] Anda menekan tombol pelatuk dari Terminal!",
                'ping-module',
                '/ping-manual',
                'Lihat Hasil',
                'Pesan Uji Coba Manual',
                'fas fa-rocket text-primary', // icon
                false, // is_silent
                ['keterangan' => 'Uji coba sukses'] // options
            ));
            $this->line("-> Terkirim ke: {$user->email}");
        }

        $this->info("\nSukses! Silakan buka browser Anda dan lihat pojok kanan atas.");
    }
}
