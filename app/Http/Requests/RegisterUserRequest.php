<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Allow anyone to make a registration request
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'birthday' => 'required|date',
            'gender' => 'required|string',
            
            // "Must be a @gmail.com address"
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users', 'ends_with:gmail.com'],
            
            // "Min 8 chars, 1 uppercase, alphanumeric combo"
            'password' => [
                'required',
                'confirmed',
                'min:8',
                'regex:/[A-Z]/',      // Must contain an uppercase letter
                'regex:/[0-9]/',      // Must contain a number
            ],
        ];
    }
}