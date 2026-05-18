<?php
namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class MemberService
{
    public function index(int $teamId)
    {
        return User::where('team_id', $teamId)
            ->where('role', 'member')
            ->get();
            
    }

    public function store(array $data, int $teamId): User
    {
        return User::create([
            'team_id' => $teamId,
            'name' => $data['name'],
            'username' => $data['username'],
            'password' => Hash::make($data['password']),
            'role' => 'member',
        ]);
    }

    public function show(int $teamId, int $memberId): ?User
    {
        return User::where('team_id', $teamId)
            ->where('role', 'member')
            ->find($memberId);
    }

    public function update(array $data, int $teamId, int $memberId): ?User
    {
        $member = User::where('team_id', $teamId)
            ->where('role', 'member')
            ->find($memberId);

        if (!$member)
            return null;

        if (isset($data['name']))
            $member->name = $data['name'];
        if (isset($data['username']))
            $member->username = $data['username'];
        if (isset($data['email']))
            $member->email = $data['email'];
        if (isset($data['password']))
            $member->password = Hash::make($data['password']);

        if (isset($data['avatar'])) {
            if ($member->avatar) {
                \Storage::disk('public')->delete($member->avatar);
            }
            $member->avatar = $data['avatar']->store('avatars', 'public');
        }

        $member->save();

        return $member;
    }

    public function destroy(int $teamId, int $memberId): bool
    {
        $member = User::where('team_id', $teamId)
            ->where('role', 'member')
            ->find($memberId);

        if (!$member)
            return false;

        $member->delete();

        return true;
    }
}