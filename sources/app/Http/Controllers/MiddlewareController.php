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
    protected function getActionButtons(object $row, string $permissionKey, string $editClass = 'btn_edit', string $deleteClass = 'btn_hapus')
    {
        // Cek Permission User
        $canEdit   = auth()->user()->can($permissionKey . ':edit');
        $canDelete = auth()->user()->can($permissionKey . ':delete');

        if (!$canEdit && !$canDelete) {
            return '<div class="text-center">
                        <span class="badge badge-secondary p-1 shadow-sm" style="cursor: not-allowed; opacity:0.7" title="Akses Dibatasi">
                            <i class="fas fa-lock mr-1"></i> Locked
                        </span>
                    </div>';
        }

        $btn = '<div class="text-center">';

        // Edit
        if ($canEdit) {
            $btn .= '<button type="button" data-id="' . $row->id . '" class="btn btn-warning btn-sm ' . $editClass . ' mr-1" title="Edit">
                        <i class="fas fa-pencil-alt"></i>
                     </button>';
        } else {
            $btn .= '<button type="button" class="btn btn-secondary btn-sm mr-1" disabled style="cursor:not-allowed; opacity:0.6" title="No Access">
                        <i class="fas fa-lock"></i>
                     </button>';
        }

        // Delete
        if ($canDelete) {
            $btn .= '<button type="button" data-id="' . $row->id . '" class="btn btn-danger btn-sm ' . $deleteClass . '" title="Hapus">
                        <i class="fas fa-trash"></i>
                     </button>';
        } else {
            $btn .= '<button type="button" class="btn btn-secondary btn-sm" disabled style="cursor:not-allowed; opacity:0.6" title="No Access">
                        <i class="fas fa-lock"></i>
                     </button>';
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
