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
}
