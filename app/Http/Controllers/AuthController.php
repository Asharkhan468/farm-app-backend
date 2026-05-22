<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AuthController extends Controller
{
    // Login API
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        // Credentials check karein
        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid login credentials. Email ya password ghalat hai.'
            ], 401);
        }

        $user = User::where('email', $request->email)->firstOrFail();
        
        // Secure API Token generate karein
        $token = $user->createToken('auth_token')->plainTextToken;
        
        // User ke assigned roles get karein
        $roles = $user->getRoleNames(); 

        return response()->json([
            'status' => 'success',
            'message' => 'Login successful',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $roles
            ]
        ], 200);
    }

    // Logout API
    public function logout(Request $request)
    {
        // Current active token ko delete karein
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Logged out successfully'
        ], 200);
    }
}