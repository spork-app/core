<?php

namespace Spork\Core\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use Spork\Core\Events\FeatureCreated;
use Spork\Core\Events\FeatureDeleted;
use Spork\Core\Events\FeatureUpdated;
use Spork\Core\Http\Requests\ShareRequest;
use Spork\Core\Http\Requests\StoreRequest;
use Spork\Core\Http\Requests\UpdateRequest;
use Spork\Core\Models\FeatureList;
use Spork\Core\Spork;

class FeatureListController
{
    public function share(ShareRequest $shareRequest, FeatureList $featureList)
    {
        $featureListId = $shareRequest->get('feature_list_id', null);

        $featureListModel = config('spork.core.models.feature_list', null);

        /** @var FeatureList $listInQuestion */
        $listInQuestion = $featureListModel::findOrFail($featureListId);

        abort_unless($listInQuestion->user_id === $shareRequest->user()->id, 401);


        $userModel = config('spork.core.models.user', null);

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
            ->allowedFields('id', 'feature', 'name', 'settings', 'user_id', 'users', 'user')
            ->allowedFilters([
                AllowedFilter::exact('feature')
            ])
            ->allowedIncludes(...array_merge(Spork::loadWith(), ['users', 'user']))
            ->where(function ($query) {
                $query->whereHas('users', function ($query) {
                    // If the user exists for this feature's relation, include it.
                    $query->where('user_id', auth()->id());
                    // thought: If we want to restrict viewership to roles that might be done here.
                })
                ->orWhere('user_id', auth()->id());
            })
            ->paginate(
                request('limit', 15),
                ['*'],
                'page',
                request('page', 1)
            );
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

        // Only the owner of the resource can do anything with it.
        abort_unless($featureList->user_id === $request->user()->id, 401);

        $featureList->update($request->validated());

        event(new FeatureUpdated($featureList));

        return response()->json($featureList, 200);
    }

    public function destroy(Request $request, $featureList)
    {
        $featureList = FeatureList::findOrFail($featureList);
        // Only the owner of the resource can do anything with it.
        abort_unless($featureList->user_id === $request->user()->id, 401);

        $featureList->delete();
        event(new FeatureDeleted($featureList));

        return response()->json('', 204);
    }
}
