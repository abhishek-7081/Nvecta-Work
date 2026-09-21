<?php

namespace App\Http\Requests;

class SearchNotesRequest extends BaseApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'q' => ['required', 'string', 'min:1', 'max:255'],
            'limit' => ['sometimes', 'integer', 'min:1', 'max:50'],
        ];
    }

    /**
     * Custom validation messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'q.required' => 'The search query parameter (q) is required.',
            'q.min' => 'The search query must not be empty.',
            'q.max' => 'The search query cannot exceed 255 characters.',
            'limit.max' => 'The search results limit cannot exceed 50.',
        ];
    }
}
