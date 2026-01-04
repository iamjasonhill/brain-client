<?php

/**
 * Example Integration: User Signup Event
 * 
 * This shows how to integrate Brain events into a typical Laravel UserController.
 * Copy the relevant parts into your application.
 */

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\BrainEventClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function __construct(
        private BrainEventClient $brain
    ) {
    }

    /**
     * Store a newly created user and send event to Brain
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        // Send event to Brain
        $this->brain->send('user.signup', [
            'email' => $user->email,
            'name' => $user->name,
            'user_id' => $user->id,
            'signup_method' => 'email',
            'signup_at' => now()->toIso8601String(),
        ]);

        return response()->json([
            'user' => $user,
            'message' => 'User created successfully',
        ], 201);
    }
}

