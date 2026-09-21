<?php

namespace App\Http\Requests;

class UpdateNoteRequest extends BaseApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'content' => ['sometimes', 'required', 'string'],
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
            'title.required' => 'The note title cannot be empty if provided.',
            'title.max' => 'The note title must not exceed 255 characters.',
            'content.required' => 'The note content cannot be empty if provided.',
        ];
    }
}
