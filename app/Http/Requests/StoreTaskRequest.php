<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'project_id'    => 'required',
            'issue'         => 'required|string|max:255',
            'ticket_link'   => 'required|string|max:255',
            'related_links' => 'array',
            'description'   => 'nullable|string',
            'start_date'    => 'required|string',
            'due_date'      => 'string',
        ];
    }
}
