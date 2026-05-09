<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class SignupRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email'=>'required|email|unique:users|email',
            'password'=>'required|min:5',
            'role' => 'required|in:member,admin',
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'date_of_birth' => 'required|date|before:today',
            'profile_image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'id_card_image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'gender'=>'required|in:man,woman',
        ];
    }
}
