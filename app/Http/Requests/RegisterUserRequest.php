<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Http; 
use Closure; 

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
            'suffix' => 'nullable|string|max:10', // <--- Added
            'birthday' => 'required|date',
            'gender' => 'required|string',
            
            'phone_number' => ['required', 'string', 'regex:/^09\d{9}$/'], // <--- Required, PH format
            
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users', 'ends_with:gmail.com'],
            'password' => [
                'required', 'confirmed', 'min:8', 'regex:/[A-Z]/', 'regex:/[0-9]/',
            ],

            'g-recaptcha-response' => ['required', function (string $attribute, mixed $value, Closure $fail) {
                $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
                    'secret' => config('services.recaptcha.secret'),
                    'response' => $value,
                    'remoteip' => request()->ip(),
                ]);

                if (! $response['success']) {
                    $fail('The reCAPTCHA verification failed. Are you a robot?');
                }
            }],
        ];
    }

    public function messages()
    {
        return [
            'phone_number.regex' => 'Please enter a valid Philippine mobile number (e.g., 09123456789).',
        ];
    }
}