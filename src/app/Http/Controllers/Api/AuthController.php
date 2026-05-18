<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\AuthService;
use App\Http\Resources\UserResource;

class AuthController extends Controller
{
    public function __construct(
        private AuthService $authService
    ) {
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'team_name' => ['required', 'string', 'unique:teams,name'],
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'min:6'],
        ]);

        try {
            $result = $this->authService->register($data);
        } catch (\Illuminate\Database\QueryException $e) {
            \Log::error($e->getMessage());
            return response()->json([
                'message' => 'Xatolik yuz berdi.'
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage() 
            ], 400);
        }

        return response()->json([
            'user' => new UserResource($result['user']),
            'token' => $result['token']
        ], 201);
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'team_name' => ['required', 'string'],
            'username' => ['required', 'string'],
            'password' => ['required'],
        ]);

        $result = $this->authService->login($data);

        if (!$result) {
            return response()->json([
                'message' => 'Invalid credentials'
            ], 401);
        }

        return response()->json([
            'user' => new UserResource($result['user']),
            'token' => $result['token']
        ]);
    }

    public function me(Request $request)
    {
        return new UserResource($request->user());
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out'
        ]);
    }
}