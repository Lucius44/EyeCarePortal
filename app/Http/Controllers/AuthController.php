<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    // Show the Login Form
    public function showLogin()
    {
        return view('auth.login');
    }

    // Process Login
// Process Login
    public function authenticate(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            // --- FIX: Check Role and Redirect Accordingly ---
            if (Auth::user()->role === 'admin') {
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
    public function store(Request $request)
    {
        // 1. Validate the Data (Based on your requirements)
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
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
        ]);

        // 2. Create the User
        $user = User::create([
            'first_name' => $request->first_name,
            'middle_name' => $request->middle_name, // Optional
            'last_name' => $request->last_name,
            'birthday' => $request->birthday,
            'gender' => $request->gender,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'patient', // Default role
            'is_verified' => false, // Default unverified
        ]);

        // 3. Login automatically and redirect
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