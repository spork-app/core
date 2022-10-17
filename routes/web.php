<?php

use Illuminate\Support\Facades\Route;
use Spork\Core\Http\Controllers\FeatureListController;

// Route::get('/', 'Controller@method');

Route::apiResource('feature-list', FeatureListController::class);

Route::post('/share', [FeatureListController::class, 'share'])->name('feature-list.share');
