<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\AppointmentController;
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

// AJAX Email Check
Route::get('/check-email', [AuthController::class, 'checkEmail'])->name('check.email');

// Logout
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// -- Protected Patient Routes --
Route::middleware(['auth'])->group(function () {
    
    Route::get('/dashboard', [PatientController::class, 'dashboard'])->name('dashboard');
    
    // Profile Routes
    Route::get('/profile', [PatientController::class, 'profile'])->name('profile');
    
    // Appointment Routes
    Route::get('/appointments', [AppointmentController::class, 'index'])->name('appointments.index');
    Route::post('/appointments', [AppointmentController::class, 'store'])->name('appointments.store');
    Route::post('/appointments/{id}/cancel', [AppointmentController::class, 'cancel'])->name('appointments.cancel');

    // Settings & Upload
    Route::get('/settings', [PatientController::class, 'settings'])->name('settings');
    Route::post('/settings/upload', [PatientController::class, 'uploadId'])->name('settings.upload');
    Route::post('/settings/phone', [PatientController::class, 'updatePhone'])->name('settings.phone');
    Route::post('/settings/password', [PatientController::class, 'updatePassword'])->name('settings.password');

    Route::get('/my-appointments', [PatientController::class, 'myAppointments'])->name('my.appointments');
});

// -- Admin Routes (Protected) --
Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/calendar', [AdminController::class, 'calendar'])->name('admin.calendar');
    
    // NEW: Route to handle Admin creating appointments (Walk-ins)
    Route::post('/calendar/store', [AdminController::class, 'storeAppointment'])->name('admin.calendar.store');
    
    Route::get('/appointments-manage', [AdminController::class, 'appointments'])->name('admin.appointments');
    Route::get('/appointments-history', [AdminController::class, 'history'])->name('admin.history');
    Route::post('/appointment/{id}/status', [AdminController::class, 'updateStatus'])->name('admin.appointment.status');
    Route::get('/users', [AdminController::class, 'users'])->name('admin.users');
    Route::post('/users/{id}/verify', [AdminController::class, 'verifyUser'])->name('admin.users.verify');
});