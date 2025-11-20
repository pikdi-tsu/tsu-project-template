<?php

namespace App\Http\Middleware;

use Closure, Session;
use App\Models\OnboardingParticipant_model;

class checklogin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next){

        if(!Session::has('session')){
            // Session::flash('alert', 'sweetAlert("warning", "Please login to access")');
            return redirect()->route('indexing')->with('alert',['title' => 'Information !','message' => 'Silahkan Login Terlebih Dahulu !','status' => 'warning']);
        }

        return $next($request);
    }
}
