<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    public function register(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|min:6',
            ]);

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            return $this->sendResponse($user, 'Registration successful');

        } catch (\Exception $e) {
            return response(['message' => 'Registration failed', 'error' => $e->getMessage()], 500);
        }
    }

    public function login(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email',
                'password' => 'required',
            ]);

            $credentials = request(['email', 'password']);

            if (!Auth::attempt($credentials)) {
                throw ValidationException::withMessages(['message' => 'Invalid credentials']);
            }

            $user = $request->user();
            $token = $user->createToken('authToken')->accessToken;
            $user['access_token'] = $token;

            return $this->sendResponse($user, 'Login successful');

        } catch (\Exception $e) {
            return response(['message' => 'Login failed', 'error' => $e->getMessage()], 500);
        }
    }

}
