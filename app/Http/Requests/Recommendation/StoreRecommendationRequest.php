<?php

namespace App\Http\Requests\Recommendation;

use Illuminate\Foundation\Http\FormRequest;

class StoreRecommendationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'song_id' => ['required', 'integer', 'exists:songs,id'],
            'source_song_ids' => ['required', 'array'],
            'source_song_ids.*' => ['integer', 'exists:songs,id'],
        ];
    }
}
