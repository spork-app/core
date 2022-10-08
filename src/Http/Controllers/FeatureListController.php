<?php

namespace Spork\Core\Http\Controllers;

use Spatie\QueryBuilder\QueryBuilder;
use Spork\Core\Http\Requests\ShareRequest;
use Spork\Core\Models\FeatureList;
use Spork\Core\Spork;

class FeatureListController
{
    public function __invoke(ShareRequest $shareRequest)
    {
        $featureListId = $shareRequest->get('feature_list_id');

        $featureList = config('spork-core.models.feature_list');

        dd($featureListId, $shareRequest->validated());

        return $featureList::findOrFail($featureListId);
    }

    public function index()
    {
        return QueryBuilder::for(FeatureList::class)
            ->allowedIncludes(Spork::$loadWith)
            ->allowedFilters([])
            ->paginate();
    }
}
