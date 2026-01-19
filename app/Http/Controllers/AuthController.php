<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Enums\UserRole;
use App\Http\Requests\RegisterUserRequest; // <--- Import the new Request

class AuthController extends Controller
{
    // Show the Login Form
    public function showLogin()
    {
        return view('auth.login');
    }

    // Process Login
    public function authenticate(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            if (Auth::user()->role === UserRole::Admin) {
                return redirect()->route('admin.dashboard');
            }

            return redirect()->route('dashboard');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ]);
    }

    // Show the Signup Form
    public function showRegister()
    {
        return view('auth.register');
    }

    // Process Signup
    public function store(RegisterUserRequest $request) // <--- Use the new Request class
    {
        // Note: Validation is now handled automatically by RegisterUserRequest.
        // We can access the validated data directly or use the request object.

        // Create the User
        $user = User::create([
            'first_name' => $request->first_name,
            'middle_name' => $request->middle_name, // Optional
            'last_name' => $request->last_name,
            'birthday' => $request->birthday,
            'gender' => $request->gender,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => UserRole::Patient, // Using the Enum
            'is_verified' => false, // Default unverified
        ]);

        // Login automatically and redirect
        Auth::login($user);

        return redirect()->route('dashboard');
    }

    // Logout
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}