<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\Recaptcha; // <--- Import the new Rule

class RegisterUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'suffix' => 'nullable|string|max:10',
            'birthday' => 'required|date',
            'gender' => 'required|string',
            
            'phone_number' => ['required', 'string', 'regex:/^09\d{9}$/'],
            
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users', 'ends_with:gmail.com'],
            'password' => [
                'required', 'confirmed', 'min:8', 'regex:/[A-Z]/', 'regex:/[0-9]/',
            ],

            // Refactored: specific logic moved to App\Rules\Recaptcha
            'g-recaptcha-response' => ['required', new Recaptcha],
        ];
    }

    public function messages()
    {
        return [
            'phone_number.regex' => 'Please enter a valid Philippine mobile number (e.g., 09123456789).',
        ];
    }
}