<?php

namespace Modules\System\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use App\Exports\NotificationBackupExport;
use Maatwebsite\Excel\Facades\Excel;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $filter = $request->query('filter', 'unread');
        
        if ($filter == 'read') {
            $notifications = $user->readNotifications()->paginate(15);
        } elseif ($filter == 'all') {
            $notifications = $user->notifications()->paginate(15);
        } else {
            // Default: unread
            $notifications = $user->unreadNotifications()->paginate(15);
        }
        
        $notifications->appends(['filter' => $filter]);
        
        $unreadCount = $user->unreadNotifications()->count();
        $readCount = $user->readNotifications()->count();
        $title = 'Kotak Masuk Notifikasi';
        
        return view('system::notifications.index', compact('notifications', 'filter', 'unreadCount', 'readCount', 'title'));
    }

    public function read($id)
    {
        $notification = Auth::user()->notifications()->find($id);

        if ($notification) {
            $notification->markAsRead();
            
            // Check if there is an action_url or download_url
            if (isset($notification->data['action_url'])) {
                return redirect($notification->data['action_url']);
            }
            if (isset($notification->data['download_url'])) {
                // Return redirect back with session download_url (Ghost download fix)
                return redirect()->back()->with('download_url', $notification->data['download_url']);
            }
        }

        return redirect()->back();
    }

    public function markAllAsRead()
    {
        $user = Auth::user();
        $user->unreadNotifications->markAsRead();
        
        return redirect()->back()->with('success', 'Semua notifikasi ditandai sudah dibaca.');
    }

    public function clear()
    {
        Auth::user()->readNotifications()->delete();
        
        return redirect()->back()->with('success', 'Riwayat notifikasi berhasil dibersihkan.');
    }

    public function backupAndClear()
    {
        $user = Auth::user();
        $readNotifs = $user->readNotifications()->get();
        
        if ($readNotifs->count() == 0) {
            return redirect()->back()->with('error', 'Tidak ada riwayat notifikasi yang bisa di-backup.');
        }

        // Export to Excel
        $filename = 'Backup_Notifikasi_' . $user->name . '_' . date('Ymd_His') . '.xlsx';
        $export = new NotificationBackupExport($readNotifs);
        // Save to disk first
        Excel::store($export, 'public/exports/' . $filename);
        
        $user->readNotifications()->delete();
        
        // Redirect back with download_url so the browser refreshes and UI updates
        $downloadUrl = url('/storage/exports/' . $filename);
        return redirect()->back()
            ->with('success', 'Riwayat berhasil dibackup dan dihapus. File sedang diunduh...')
            ->with('download_url', $downloadUrl);
    }
}
