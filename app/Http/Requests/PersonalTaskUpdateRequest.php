<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PersonalTaskUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [

            'title' => 'sometimes|string|max:255',

            'description' => 'nullable|string',

            'priority' => 'sometimes|in:low,medium,high',

            'status' => 'sometimes|in:todo,in_progress,review,done',

            'start_date' => 'nullable|date',

            'end_date' => 'nullable|date|after_or_equal:start_date',
        ];
    }
}