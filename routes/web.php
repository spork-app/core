<?php

use Illuminate\Support\Facades\Route;
use Spork\Core\Http\Controllers\FeatureListController;

// Route::get('/', 'Controller@method');
Route::post('/share', FeatureListController::class);

Route::get('/feature-lists', [FeatureListController::class, 'index']);
