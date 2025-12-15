<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

Route::middleware('auth:api')->get('/userinfo', function (Request $request) {
    $user = $request->user();

    // Split name into first/last very roughly
    $parts = preg_split('/\s+/', $user->name, 2);
    $given  = $parts[0] ?? $user->name;
    $family = $parts[1] ?? '';

    return [
        // OpenID-style fields Moodle understands
        'sub'         => (string) $user->id,
        'email'       => $user->email,
        'name'        => $user->name,
        'given_name'  => $given,
        'family_name' => $family,
    ];
});
