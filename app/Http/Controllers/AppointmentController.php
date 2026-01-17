<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Appointment; // We will use this later

class AppointmentController extends Controller
{
    public function index()
    {
        return view('patient.appointments');
    }
}