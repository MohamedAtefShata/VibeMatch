<?php

namespace App\Http\Requests\Recommendation;

use Illuminate\Foundation\Http\FormRequest;

class RateRecommendationRequest extends FormRequest
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
            'rating' => ['required', 'integer', 'in:-1,1'], // -1 for dislike, 1 for like
        ];
    }
}
