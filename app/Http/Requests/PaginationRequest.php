<?php

namespace App\Http\Requests;

class PaginationRequest extends BaseApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'page' => ['sometimes', 'integer', 'min:1'],
            'limit' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'sort_by' => ['sometimes', 'string', 'in:id,title,created_at,updated_at'],
            'order' => ['sometimes', 'string', 'in:asc,desc,ASC,DESC'],
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
            'page.min' => 'The page parameter must be at least 1.',
            'limit.min' => 'The limit parameter must be at least 1.',
            'limit.max' => 'The limit parameter cannot exceed 100 to prevent server overload.',
            'sort_by.in' => 'Allowed sort fields are id, title, created_at, updated_at.',
            'order.in' => 'Sort order must be either asc or desc.',
        ];
    }
}
