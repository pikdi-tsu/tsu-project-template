<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class NotificationBackupExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $notifications;

    public function __construct($notifications)
    {
        $this->notifications = $notifications;
    }

    public function collection()
    {
        return $this->notifications;
    }

    public function headings(): array
    {
        return [
            'ID Notifikasi',
            'Tanggal Dibuat',
            'Pesan',
            'Status',
            'Link Download / Aksi'
        ];
    }

    public function map($notification): array
    {
        $data = $notification->data;
        
        $status = 'Informasi';
        if (isset($data['statusatasan'])) {
            if ($data['statusatasan'] == 'export-ready') $status = 'Export Berhasil';
            if ($data['statusatasan'] == 'export-failed') $status = 'Export Gagal';
        }
        
        $actionUrl = $data['download_url'] ?? '-';

        return [
            $notification->id,
            $notification->created_at->format('Y-m-d H:i:s'),
            $data['message'] ?? 'Tidak ada pesan',
            $status,
            $actionUrl
        ];
    }
    
    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold text.
            1    => ['font' => ['bold' => true]],
        ];
    }
}
