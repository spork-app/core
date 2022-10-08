<?php

use App\Models\User;
use Spork\Core\Models\FeatureList;

return [
    'enabled' => true,
    'middleware' => [],
    'models' => [
        'feature_list' => FeatureList::class,
        'user' => User::class,
    ]
];