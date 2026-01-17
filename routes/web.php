<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\AppointmentController; // <--- ADD THIS LINE
use App\Http\Controllers\AdminController;

// -- Public Routes --
Route::get('/', function () {
    return view('welcome');
})->name('home');

// Login
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'authenticate'])->name('login.post');

// Signup
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'store'])->name('register.post');

// Logout
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// -- Protected Patient Routes --
Route::middleware(['auth'])->group(function () {
    
    Route::get('/dashboard', [PatientController::class, 'dashboard'])->name('dashboard');
    Route::get('/profile', [PatientController::class, 'profile'])->name('profile');
    
    // Appointment Route
    Route::get('/appointments', [AppointmentController::class, 'index'])->name('appointments.index');
    // Save Appointment
    Route::post('/appointments', [AppointmentController::class, 'store'])->name('appointments.store');
});

// -- Admin Routes (Protected) --
Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    
    // We will add Accept/Reject routes here later
});