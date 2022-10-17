<?php

namespace Spork\Core\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Spork\Core\Spork;

class StoreRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|string',
            'feature' => [
                'required',
                'string',
                Rule::in(Spork::provides()),
            ],
            'settings' => 'nullable|array',
        ];
    }
}
