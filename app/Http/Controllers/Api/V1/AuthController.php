<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'nrp' => ['required', 'string'],
            'password' => ['required', 'string'],
            'device_name' => ['required', 'string', 'max:255'],
        ]);

        $user = User::where('nrp', $credentials['nrp'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            return response()->json(['message' => 'NRP atau kata sandi tidak valid.'], 422);
        }

        return response()->json([
            'message' => 'Login berhasil.',
            'token' => $user->createToken($credentials['device_name'])->plainTextToken,
            'token_type' => 'Bearer',
            'user' => $user->only(['id', 'name', 'nrp']),
        ]);
    }

    public function user(Request $request): JsonResponse
    {
        return response()->json(['data' => $request->user()->only(['id', 'name', 'nrp'])]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->json(['message' => 'Token berhasil dihapus.']);
    }
}
