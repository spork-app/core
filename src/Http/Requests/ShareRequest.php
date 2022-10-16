<?php

namespace Spork\Core\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Spork\Core\Models\FeatureList;

class ShareRequest extends FormRequest
{
    public function authorize()
    {
        // Maybe verify the person making the request owns the feature list?
        $featureListModel = config('spork.core.models.feature_list', FeatureList::class);

        $featureList = $featureListModel::findOrFail($this->get('feature_list_id'));

        return $featureList->user_id === auth()->id();
    }

    public function rules()
    {
        return [
            'email' => 'required',
            'feature_list_id' => [
                'required',
                'exists:'.config('spork.core.models.feature_list', FeatureList::class).',id',
            ],
        ];
    }
}
