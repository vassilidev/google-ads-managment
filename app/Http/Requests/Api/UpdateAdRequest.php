<?php

namespace App\Http\Requests\Api;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateAdRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'name'       => [
                'sometimes',
                'string',
            ],
            'budget'     => [
                'sometimes',
                'numeric',
            ],
            'cpa_target' => [
                'sometimes',
                'numeric',
            ],
        ];
    }
}
