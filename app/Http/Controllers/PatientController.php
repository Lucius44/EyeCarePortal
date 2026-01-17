<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PatientController extends Controller
{
    // 1. The Main Dashboard
    public function dashboard()
    {
        return view('patient.dashboard');
    }

    // 2. The Profile Page
    public function profile()
    {
        $user = Auth::user(); // Get the currently logged-in user
        return view('patient.profile', compact('user'));
    }
}