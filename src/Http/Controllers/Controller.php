<?php

namespace GTerrusa\LaravelGoogleCalendar\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Support\Facades\View;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    protected $site_settings;

    public function __construct()
    {
        // $domain=request()->getHost();

        //$menu = nova_get_menu($domain, 'en');
        //  View::share(['navigation'=> $menu, 'footer' =>'sparks' ]);
    }
}
