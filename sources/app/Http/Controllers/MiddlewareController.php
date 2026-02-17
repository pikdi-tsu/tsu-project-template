<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class MiddlewareController extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * Fungsi Middleware permission di controller
     * @param string $prefix (Contoh: 'system:user', 'siakad:krs')
     */
    protected function registerPermissions(string $prefix): void
    {
        // Gembok View (Index, Show)
        $this->middleware("permission:{$prefix}:view")->only(['index', 'show']);

        // Gembok Create (Create, Store)
        $this->middleware("permission:{$prefix}:create")->only(['create', 'store']);

        // Gembok Edit (Edit, Update)
        $this->middleware("permission:{$prefix}:edit")->only(['edit', 'update']);

        // Gembok Delete (Destroy)
        $this->middleware("permission:{$prefix}:delete")->only(['destroy']);
    }

    /**
     * * @param object $row           Data dari Datatable ($row)
     * @param string $permissionKey Key Permission (cth: 'contact_person')
     * @param string $editClass     Class untuk selector JS Edit
     * @param string $deleteClass   Class untuk selector JS Delete
     */
    protected function getActionButtons(object $row, string $permissionKey, array $options = [], string $editClass = 'btn-edit', string $deleteClass = 'btn-delete')
    {

        $defaultOptions = [
            'edit_class'   => 'btn-edit',   // Class selector buat jQuery
            'delete_class' => 'btn-delete',
            'edit_url'     => null,         // Kalau diisi, jadi <a href="...">. Kalau null, jadi <button> Modal.
            'delete_url'   => null,         // URL action form delete
            'can_edit'     => null,         // (Bool) Override manual permission edit
            'can_delete'   => null,         // (Bool) Override manual permission delete
            'use_modal'    => true,
        ];

        $opt = array_merge($defaultOptions, $options);

        // Cek Permission User
        $canEdit   = is_null($opt['can_edit'])
            ? auth()->user()->can($permissionKey . ':edit')
            : $opt['can_edit'];
        $canDelete = is_null($opt['can_delete'])
            ? auth()->user()->can($permissionKey . ':delete')
            : $opt['can_delete'];

        if (!$canEdit && !$canDelete) {
            return '<div class="text-center">
                    <span class="badge badge-secondary p-1 shadow-sm" style="cursor: not-allowed; opacity:0.7" title="Akses Dibatasi">
                        <i class="fas fa-lock mr-1"></i> Locked
                    </span>
                </div>';
        }

        $btn = '<div class="text-center" style="white-space:nowrap">';

        // Edit
        if ($canEdit) {
            // Validasi URL: Edit butuh URL (entah buat href atau ajax modal)
            $url = $opt['edit_url'] ?? '#';

            if ($opt['use_modal'] === false) {
                // CASE A: Link Pindah Halaman (Tag <a>)
                $btn .= '<a href="' . $url . '" class="btn btn-warning btn-sm mr-1" title="Edit Data">
                        <i class="fas fa-pencil-alt"></i>
                     </a>';
            } else {
                // CASE B: Modal AJAX (Tag <button>)
                // 🔥 Perhatikan data-url="$url"
                $btn .= '<button type="button"
                            data-id="' . $row->id . '"
                            data-url="' . $url . '"
                            class="btn btn-warning btn-sm ' . $opt['edit_class'] . ' mr-1"
                            title="Edit Data">
                        <i class="fas fa-pencil-alt"></i>
                     </button>';
            }
        } else {
            $btn .= '<button type="button" class="btn btn-secondary btn-sm mr-1" disabled style="opacity:0.6"><i class="fas fa-lock"></i></button>';
        }

        // Delete
        if ($canDelete) {
            $actionUrl = $opt['delete_url'] ?? '#';
            $btn .= '<form action="' . $actionUrl . '" method="POST" style="display:inline;" class="form-delete">
                    ' . csrf_field() . ' ' . method_field('DELETE') . '
                    <button type="submit" class="btn btn-danger btn-sm ' . $opt['delete_class'] . '" title="Hapus"><i class="fas fa-trash"></i></button>
                </form>';
        } else {
            $btn .= '<button type="button" class="btn btn-secondary btn-sm" disabled style="opacity:0.6"><i class="fas fa-lock"></i></button>';
        }

        $btn .= '</div>';

        return $btn;
    }

    /**
     * CEK PERMISSION MANUAL
     * Contoh: $this->guard('edit', 'contact_person');
     */
    protected function guard($action, $permissionKey)
    {
        $permission = $permissionKey . ':' . $action;

        if (!auth()->user()->can($permission)) {
            $actualAction = ucfirst($action);
            abort(403, 'Akses Dibatasi! Anda tidak memiliki izin: ' . $actualAction);
        }
    }

    /**
     * CEK PERMISSION KHUSUS STORE/SAVE
     * Otomatis deteksi: ID -> Edit. Default -> Create.
     */
    protected function guardStore($id, $permissionKey)
    {
        $action = $id ? 'edit' : 'create';
        $this->guard($action, $permissionKey);
    }
}
