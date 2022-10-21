<?php

use App\Models\User;
use Spork\Core\Models\FeatureList;

return [
    'enabled' => true,
    'middleware' => ['api'],
    'models' => [
        'feature_list' => FeatureList::class,
        'user' => User::class,
    ],
];
