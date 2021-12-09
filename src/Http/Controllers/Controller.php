<?php

namespace GTerrusa\LaravelGoogleCalendar\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\View;

class Controller extends BaseController
{
    use AuthorizesRequests;
    use DispatchesJobs;
    use ValidatesRequests;
    protected $site_settings;

    public function __construct()
    {
        // $domain=request()->getHost();

        //$menu = nova_get_menu($domain, 'en');
        //  View::share(['navigation'=> $menu, 'footer' =>'sparks' ]);
    }
}
