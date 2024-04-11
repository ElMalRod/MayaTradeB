<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User; 
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
            if (!$user->approved) {
                return response()->json(['message' => 'Your account is not approved yet.'], 401);
            }

            $token = $user->createToken($request->device_name)->plainTextToken;
            return response()->json([
                'id' => $user->id,
                'token' => $token,
                'name' => $user->name,
                'role' => $user->role,
                'saldo' => $user->saldo
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

    public function getUsersByApprovalStatus()
    {
        $users = User::orderBy('approved', 'asc')->get();
        return response()->json(['users' => $users]);
    }

    // Update the approval status of a user
    public function updateApprovalStatus(Request $request, $id)
    {
        // Busca el usuario por su ID
        $user = User::findOrFail($id);
    
        $validatedData = $request->validate([
            'approved' => 'required|boolean',
        ]);
    
        // Actualiza el estado de aprobación del usuario
        $user->approved = $validatedData['approved'];
        $user->save();
        
        return response()->json(['message' => 'User approval status updated successfully.', 'user' => $user]);
    }
    

}
