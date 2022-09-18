<?php

namespace Spork\Core;

use Spork\Core\Spork;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Route;

class SporkServiceProvider extends RouteServiceProvider
{
    public function boot()
    {
      //
    }

    public function register()
    {
        Spork::addFeature('core', 'ViewBoardsIcon', '/core');

        if (config('spork.core.enabled')) {
            Route::middleware($this->app->make('config')->get('spork.core.middleware', ['auth:sanctum']))
                ->prefix('api/core')
                ->group(__DIR__.'/../routes/web.php');
        }
    }
}
