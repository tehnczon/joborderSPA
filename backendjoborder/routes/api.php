<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

// ✅ Protect this route using Sanctum authentication
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return response()->json($request->user());

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

// ✅ Logout Route (Properly Clears Session)
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
