<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\RegisterUserRequest;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use Illuminate\Auth\Events\Registered; 
use Illuminate\Auth\Events\Verified;

class AuthController extends Controller
{
    public function showLogin() {
        return view('auth.login');
    }

    public function showRegister() {
        return view('auth.register');
    }

    public function authenticate(Request $request) {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // --- NEW: Fast Ban Lookup BEFORE checking password ---
        $user = User::where('email', $credentials['email'])->first();

        if ($user && $user->account_status === UserStatus::Banned) {
            return back()->withErrors([
                'email' => 'Your account has been permanently deactivated due to a policy violation. Please contact the clinic for more information.',
            ])->onlyInput('email');
        }
        // -----------------------------------------------------

        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();
            
            if(Auth::user()->role === UserRole::Admin) {
                return redirect()->route('admin.dashboard');
            }

            return redirect()->route('dashboard');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function store(RegisterUserRequest $request) {
        $validated = $request->validated();

        $user = User::create([
            'first_name' => $validated['first_name'],
            'middle_name' => $validated['middle_name'],
            'last_name' => $validated['last_name'],
            'suffix' => $validated['suffix'], 
            'birthday' => $validated['birthday'],
            'gender' => $validated['gender'],
            'email' => $validated['email'],
            'phone_number' => $validated['phone_number'],
            'password' => Hash::make($validated['password']),
            'role' => UserRole::Patient,
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect()->route('dashboard');
    }

    public function logout(Request $request) {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }

    public function checkEmail(Request $request)
    {
        $email = $request->query('email');
        $exists = User::where('email', $email)->exists();
        return response()->json(['exists' => $exists]);
    }

    public function checkVerificationStatus(Request $request)
    {
        return response()->json([
            'verified' => $request->user()->hasVerifiedEmail()
        ]);
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'otp' => ['required', 'string', 'size:6'],
        ]);

        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return redirect()->route('dashboard');
        }

        if (!$user->email_otp || 
            $user->email_otp !== $request->otp || 
            now()->greaterThan($user->email_otp_expires_at)) {
            
            return back()->withErrors([
                'otp' => 'The verification code is invalid or has expired.'
            ]);
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        $user->clearEmailOTP();

        return redirect()->route('dashboard')->with('status', 'Email verified successfully!');
    }
}