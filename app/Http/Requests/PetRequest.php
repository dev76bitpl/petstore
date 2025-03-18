<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class PetRequest
 * @package App\Http\Requests
 */
class PetRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|string',
            'status' => 'required|in:available,pending,sold',
            'category_id' => 'nullable|integer',
            'category_name' => 'nullable|string',
            'photoUrls' => 'nullable|array',
            'tags' => 'nullable|array',
        ];
    }
}
