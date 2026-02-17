<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminServiceController; 

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
    Route::post('/settings/profile', [PatientController::class, 'updateProfile'])->name('settings.profile');
    Route::post('/settings/upload', [PatientController::class, 'uploadId'])->name('settings.upload');
    Route::post('/settings/phone', [PatientController::class, 'updatePhone'])->name('settings.phone');
    Route::post('/settings/password', [PatientController::class, 'updatePassword'])->name('settings.password');
    
    // --- SECURE ROUTE FOR PATIENT VIEWING OWN ID ---
    Route::get('/settings/my-id', [PatientController::class, 'showIdPhoto'])->name('settings.view_id');

    Route::get('/my-appointments', [PatientController::class, 'myAppointments'])->name('my.appointments');
});

// -- Admin Routes (Protected) --
Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/calendar', [AdminController::class, 'calendar'])->name('admin.calendar');
    
    // Appointment Actions
    Route::post('/calendar/store', [AdminController::class, 'storeAppointment'])->name('admin.calendar.store');
    Route::post('/calendar/settings', [AdminController::class, 'updateDaySetting'])->name('admin.calendar.settings');
    
    Route::get('/appointments-manage', [AdminController::class, 'appointments'])->name('admin.appointments');
    Route::get('/appointments-history', [AdminController::class, 'history'])->name('admin.history');
    Route::post('/appointment/{id}/status', [AdminController::class, 'updateStatus'])->name('admin.appointment.status');
    
    Route::get('/users', [AdminController::class, 'users'])->name('admin.users');
    Route::post('/users/{id}/verify', [AdminController::class, 'verifyUser'])->name('admin.users.verify');
    
    // --- SECURE ROUTE FOR ADMIN VIEWING USER ID ---
    Route::get('/users/{id}/id-photo', [AdminController::class, 'showUserIdPhoto'])->name('admin.users.view_id');
    
    // --- NEW: Unrestrict User Route ---
    Route::post('/users/{id}/unrestrict', [AdminController::class, 'unrestrictUser'])->name('admin.users.unrestrict');

    // --- NEW: Admin Settings Routes ---
    Route::get('/settings', [AdminController::class, 'settings'])->name('admin.settings');
    Route::post('/settings/password', [AdminController::class, 'updatePassword'])->name('admin.settings.password');

    Route::resource('services', AdminServiceController::class)->except(['show', 'create', 'edit']);
});