<?php

namespace Spork\Core\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Spork\Core\Spork;

class UpdateRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'string',
            'feature' => [
                'string',
                Rule::in(Spork::provides()),
            ],
            'settings' => 'nullable|array',
        ];
    }
}
