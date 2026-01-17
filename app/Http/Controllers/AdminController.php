<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Appointment;
use App\Models\User;

class AdminController extends Controller
{
    public function dashboard()
    {
        // Get all appointments with their associated User
        $appointments = Appointment::with('user')->get();
        
        return view('admin.dashboard', compact('appointments'));
    }
}