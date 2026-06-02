<?php

namespace Modules\System\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class HomeController extends Controller
{
    public function index()
    {
        $projectName = strtoupper(config('app.module.name'));

        $data = array(
            'project_name' => $projectName,
        );

        return view('system::halamanutama/index', $data);
    }
}
