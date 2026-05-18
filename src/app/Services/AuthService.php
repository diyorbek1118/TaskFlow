<?php

namespace App\Services;

use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthService
{

    public function register(array $data)
    {
        $team = Team::create([
            'name' => $data['team_name'],
        ]);

        $user = User::create([
            'team_id' => $team->id,
            'name' => $data['name'],
            'username' => $data['username'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => 'admin',
        ]);

        $token = $user->createToken('auth-token')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token
        ];
    }
    public function login(array $data)
    {
        $team = Team::where('name', $data['team_name'])->first();
        if (!$team) {
            return null;
        }
        $user = User::where('team_id', $team->id)
            ->where('username', $data['username'])
            ->first();
        if (!$user || !Hash::check($data['password'], $user->password)) {
            return null;
        }

        $token = $user->createToken('auth-token')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token
        ];
    }
}