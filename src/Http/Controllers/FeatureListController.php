<?php

namespace Spork\Core\Http\Controllers;

use Spatie\QueryBuilder\QueryBuilder;
use Spork\Core\Events\FeatureCreated;
use Spork\Core\Events\FeatureDeleted;
use Spork\Core\Events\FeatureUpdated;
use Spork\Core\Http\Requests\ShareRequest;
use Spork\Core\Http\Requests\StoreRequest;
use Spork\Core\Http\Requests\UpdateRequest;
use Spork\Core\Models\FeatureList;
use Spork\Core\Spork;
use Illuminate\Http\Request;

class FeatureListController
{
    public function share(ShareRequest $shareRequest, FeatureList $featureList)
    {
        $featureListId = $shareRequest->get('feature_list_id');

        $featureListModel = config('spork.core.models.feature_list');

        /** @var FeatureList $listInQuestion */
        $listInQuestion = $featureListModel::findOrFail($featureListId);

        $userModel = config('spork.core.models.user');

        $user = $userModel::firstWhere('email', $shareRequest->get('email'));

        if (empty($user)) {
            // Send an invite to that email address/??
            return response()->json([
                'message' => 'That user doesnt exist',
            ], 412);
        }

        $listInQuestion->users()->attach($user);

        return response('', 200);
    }

    public function index()
    {
        return QueryBuilder::for(FeatureList::class)
            ->allowedIncludes(Spork::$loadWith)
            ->allowedFilters([])
            ->paginate();
    }

    public function store(StoreRequest $request)
    {
        $createdFeature = $request->user()->features()->create($request->validated());

        event(new FeatureCreated($createdFeature));

        return response()->json($createdFeature, 201);
    }

    public function update(UpdateRequest $request, $featureList)
    {
        $featureList = FeatureList::findOrFail($featureList);

        abort_unless($featureList->user_id === $request->user()->id, 401);

        $featureList->update($request->validated());

        event(new FeatureUpdated($featureList));

        return response()->json($featureList, 200);
    }

    public function destroy(Request $request, $featureList)
    {
        $featureList = FeatureList::findOrFail($featureList);
       
        abort_unless($featureList->user_id === $request->user()->id, 401);

        $featureList->delete();
        event(new FeatureDeleted($featureList));

        return response()->json('', 204);
    }
}
