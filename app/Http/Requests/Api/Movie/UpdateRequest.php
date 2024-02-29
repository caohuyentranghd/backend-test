<?php

namespace App\Http\Requests\Api\Movie;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
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
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string|max:500',
            'release_date' => 'sometimes|required|date',
            'duration' => 'sometimes|required|integer',
            'genre' => 'sometimes|required|string|max:255',
            'director' => 'sometimes|required|string|max:255',
            'cast' => 'sometimes|required|string|max:255',
            'rating' => 'sometimes|required|numeric|min:0|max:10',
            'poster' => 'nullable|url|max:255',
            'trailer' => 'nullable|url|max:255',
            'country' => 'sometimes|required|string|max:255',
            'language' => 'sometimes|required|string|max:255',
        ];
    }
}
