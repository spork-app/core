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
            'name' => [
                'required',
            ],
            'feature' => Rule::in(array_keys(Spork::$features) + ['core']),
            'settings' => 'array',
        ];
    }
}
