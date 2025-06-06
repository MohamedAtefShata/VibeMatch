<?php

namespace App\Http\Requests\Song;

use Illuminate\Foundation\Http\FormRequest;

class SearchSongRequest extends FormRequest
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
            'query' => 'required|string|min:2|max:100',
        ];
    }
}
