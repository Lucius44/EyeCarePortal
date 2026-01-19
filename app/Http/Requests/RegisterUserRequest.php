<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Http; // <--- IMPORT THIS
use Closure; // <--- IMPORT THIS

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
            'birthday' => 'required|date',
            'gender' => 'required|string',
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users', 'ends_with:gmail.com'],
            'password' => [
                'required', 'confirmed', 'min:8', 'regex:/[A-Z]/', 'regex:/[0-9]/',
            ],

            // --- NEW: Backend Verification Logic ---
            'g-recaptcha-response' => ['required', function (string $attribute, mixed $value, Closure $fail) {
                // 1. Send the token to Google
                $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
                    'secret' => config('services.recaptcha.secret'),
                    'response' => $value,
                    'remoteip' => request()->ip(),
                ]);

                // 2. Check if Google said "False" (Failed)
                // We use array access ['$key'] instead of ->json('key') to prevent IDE errors
                if (! $response['success']) {
                    $fail('The reCAPTCHA verification failed. Are you a robot?');
                }
            }],
        ];
    }
}