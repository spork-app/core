<?php

namespace Spork\Core\Http\Requests;

use Illuminate\Http\Request;

class ShareRequest extends Request
{
    public function authorize()
    {
        return false;
    }

    public function rules()
    {
        return [
            'email' => 'required',
            'feature_list_id' => 'required,exists:'.config('spork-app.models.feature_list').',id',
        ];
    }
}