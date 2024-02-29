<?php

namespace App\Http\Requests\Api\Movie;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:500',
            'release_date' => 'required|date',
            'duration' => 'required|integer',
            'genre' => 'required|string|max:255',
            'director' => 'required|string|max:255',
            'cast' => 'required|string|max:255',
            'rating' => 'required|numeric|min:0|max:10',
            'poster' => 'nullable|url|max:255',
            'trailer' => 'nullable|url|max:255',
            'country' => 'required|string|max:255',
            'language' => 'required|string|max:255',
        ];
    }
}
