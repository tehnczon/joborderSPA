<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use App\Http\Controllers\JobOrderController;

// ✅ Protect this route using Sanctum authentication
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return response()->json($request->user());
    });

    // CRUD Routes for Job Orders
    Route::get('/job-orders', [JobOrderController::class, 'index']); // Read all
    Route::post('/job-orders', [JobOrderController::class, 'store']); // Create
    Route::get('/job-orders/{id}', [JobOrderController::class, 'show']); // Read single
    Route::put('/job-orders/{id}', [JobOrderController::class, 'update']); // Update
    Route::delete('/job-orders/{id}', [JobOrderController::class, 'destroy']); // Delete
});


// ✅ Login Route (Uses Cookie Authentication)
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

    // Regenerate session for security
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

// ✅ CSRF Cookie Route (No Need for Custom Response)
Route::get('/sanctum/csrf-cookie', function (Request $request) {
    return response()->noContent(); // This is handled by Sanctum
});
