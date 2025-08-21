<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use App\Http\Controllers\JobOrderController;

// Public routes
Route::post('/login', function (Request $request) {
    $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

    if (!Auth::attempt($request->only('email', 'password'), true)) {
        throw ValidationException::withMessages([
            'email' => ['Invalid login credentials.'],
        ]);
    }

    $request->session()->regenerate();

    return response()->json([
        'message' => 'Login successful',
        'user' => Auth::user(),
    ]);
});

Route::post('/logout', function (Request $request) {
    Auth::guard('web')->logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return response()->json(['message' => 'Logged out successfully']);
});

Route::get('/sanctum/csrf-cookie', function (Request $request) {
    return response()->noContent();
});

// Protected routes - Require authentication
Route::middleware('auth:sanctum')->group(function () {
    // User endpoint
    Route::get('/user', function (Request $request) {
        return response()->json($request->user());
    });

    // Job Orders CRUD
    Route::get('/job-orders', [JobOrderController::class, 'index']); // Read all with pagination & search
    Route::post('/job-orders', [JobOrderController::class, 'store']); // Create
    Route::get('/job-orders/{id}', [JobOrderController::class, 'show']); // Read single
    Route::put('/job-orders/{id}', [JobOrderController::class, 'update']); // Update
    Route::delete('/job-orders/{id}', [JobOrderController::class, 'destroy']); // Delete
    
    // Image management
    Route::post('job-orders/{id}/upload-images', [JobOrderController::class, 'uploadImages']);
    Route::get('job-orders/{id}/images', [JobOrderController::class, 'getImages']);
    Route::delete('job-orders/{id}/delete-image', [JobOrderController::class, 'deleteImage']);
    
    // Repair History endpoints
    Route::post('job-orders/{id}/repair-history', [JobOrderController::class, 'addRepairHistory']);
    Route::get('job-orders/{id}/repair-history', [JobOrderController::class, 'getRepairHistory']);
    
    // Statistics endpoint
    Route::get('/statistics', [JobOrderController::class, 'getStatistics']);
});