<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Services\MemberService;
use Illuminate\Http\Request;

class MemberController extends Controller
{
    public function __construct(
        private MemberService $memberService
    ) {}

    public function index(Request $request)
    {
        $members = $this->memberService->index($request->user()->team_id);

        return UserResource::collection($members);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255'],
            'password' => ['required', 'min:6'],
        ]);

        $member = $this->memberService->store($data, $request->user()->team_id);

        return new UserResource($member);
    }

    public function show(Request $request, int $id)
    {
        $member = $this->memberService->show($request->user()->team_id, $id);

        if (!$member) {
            return response()->json(['message' => 'Member topilmadi.'], 404);
        }

        return new UserResource($member);
    }

    public function update(Request $request, int $id)
    {
        $data = $request->validate([
            'name'     => ['sometimes', 'string', 'max:255'],
            'username' => ['sometimes', 'string', 'max:255'],
            'email'    => ['sometimes', 'email', 'unique:users,email,' . $id],
            'password' => ['sometimes', 'min:6'],
            'avatar'   => ['sometimes', 'image', 'max:2048'],
        ]);

        $member = $this->memberService->update($data, $request->user()->team_id, $id);

        if (!$member) {
            return response()->json(['message' => 'Member topilmadi.'], 404);
        }

        return new UserResource($member);
    }

    public function destroy(Request $request, int $id)
    {
        $result = $this->memberService->destroy($request->user()->team_id, $id);

        if (!$result) {
            return response()->json(['message' => 'Member topilmadi.'], 404);
        }

        return response()->json(['message' => 'Member o\'chirildi.']);
    }
}