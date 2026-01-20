<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(RegisterRequest $request)
    {
        $data = $request->validated();

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        $token = $user->createToken('api')->plainTextToken;

        return ApiResponse::success('Registered successfully.', [
            'token' => $token,
            'user' => $user,
        ], 201);
    }

    public function login(LoginRequest $request)
    {
        $data = $request->validated();

        if (!Auth::attempt($data)) {
            return ApiResponse::error('Invalid credentials.', 401);
        }

        $user = $request->user();

        $token = $user->createToken('api')->plainTextToken;

        return ApiResponse::success('Logged in successfully.', [
            'token' => $token,
            'user' => $user,
        ]);
    }

    public function me(Request $request)
    {
        return ApiResponse::success('User fetched successfully.', $request->user());
    }

    public function logout(Request $request)
    {
        $user = $request->user();

        $user->currentAccessToken()?->delete();

        return ApiResponse::success('Logged out successfully.');
    }
}
