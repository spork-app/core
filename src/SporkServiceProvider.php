<?php

namespace Spork\Core;

use Spork\Core\Models\FeatureList;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Route;

class SporkServiceProvider extends RouteServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/spork.core.php' => config_path('spork.core.php'),
            ], 'config');
        }
    }

    public function register()
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->mergeConfigFrom(__DIR__.'/../config/spork-core.php', 'spork.core');

        if ($this->app->make('config')->get('spork.core.enabled', true)) {
            Route::middleware($this->app->make('config')->get('spork.core.middleware'))
                ->prefix('api/core')
                ->group(__DIR__.'/../routes/web.php');
        }
    }
}
