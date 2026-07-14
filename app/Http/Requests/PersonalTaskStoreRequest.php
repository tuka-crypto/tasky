<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PersonalTaskStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [

            'title' => 'required|string|max:255',

            'description' => 'nullable|string',

            'priority' => 'required|in:low,medium,high',

            'start_date' => 'nullable|date',

            'end_date' => 'nullable|date|after_or_equal:start_date',
        ];
    }
}