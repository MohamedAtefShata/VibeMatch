<?php

namespace App\Http\Requests\Song;

use Illuminate\Foundation\Http\FormRequest;

class RecommendationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'songIds' => 'required|array|min:1',
            'songIds.*' => 'required|integer|exists:songs,id',
            'limit' => 'sometimes|integer|min:1|max:50',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'songIds.required' => 'You must select at least one song to get recommendations.',
            'songIds.*.exists' => 'One or more selected songs do not exist in our database.',
        ];
    }
}
