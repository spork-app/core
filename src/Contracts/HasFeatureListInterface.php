<?php

namespace Spork\Core\Contracts;

use Spork\Core\Models\FeatureList;

interface HasFeatureListInterface
{
    public function getFeatureList(): FeatureList;
}