<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User; // Añade esta línea para importar el modelo User
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    // Get the authenticated user's details
    public function profile(Request $request)
    {
        $user = $request->user();
        return response()->json([
            'name' => $user->name,
            'role' => $user->role
        ]);
    }

    // Update the authenticated user's profile
    public function update(Request $request)
    {
        $user = $request->user();

        $attributes = $request->validate([
            'name' => 'required|string|max:255',
            // Other validation rules as needed for the user profile
        ]);

        $user->update($attributes);

        return response()->json(['message' => 'User profile updated successfully.', 'user' => $user]);
    }

    // Register a new user
    public function register(Request $request)
    {
        $attributes = $request->validate([
            'name' => 'required|string|max:255',
            'role' => 'required|string',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|confirmed',
            'password_confirmation' => 'required'
        ]);

        $attributes['password'] = Hash::make($attributes['password']);

        $user = User::create($attributes);

        // Issue token or perform additional steps

        return response()->json(['message' => 'User registered successfully.', 'user' => $user]);
    }

    // Login user and issue token
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'device_name' => 'required'
        ]);
    
        if (Auth::attempt($request->only('email', 'password'))) {
            $user = Auth::user();
            $token = $user->createToken($request->device_name)->plainTextToken;
            return response()->json([
                'token' => $token,
                'name' => $user->name,
                'role' => $user->role
            ]);
        }
    
        return response()->json(['message' => 'Unauthorized'], 401);
    }

    // Logout the user (revoke the token)
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'User logged out successfully.']);
    }
}
