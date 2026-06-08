<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required'
        ]);

        $loginField = filter_var($request->username, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        $credentials = [
            $loginField => $request->username,
            'password' => $request->password,
        ];

        if (!Auth::attempt($credentials)) {
            return response()->json([
                'message' => 'Username/Email atau password salah'
            ], 401);
        }

        $user = User::where($loginField, $request->username)->firstOrFail();

        if (!$user->is_active) {
            return response()->json([
                'message' => 'Akun tidak aktif'
            ], 403);
        }

        // Cleanup token lama (>30 hari tidak dipakai) untuk cegah akumulasi
        // tanpa mematikan sesi multi-device yang masih aktif.
        $user->tokens()
            ->where(function ($q) {
                $q->where('last_used_at', '<', now()->subDays(30))
                  ->orWhere(function ($q2) {
                      $q2->whereNull('last_used_at')
                         ->where('created_at', '<', now()->subDays(30));
                  });
            })
            ->delete();

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'role' => $user->role
            ]
        ]);
    }

    public function logout(Request $request)
    {
        $user = $request->user();
        if ($user && $user->role === 'kasir') {
            \App\Models\ProfilKasir::where('user_id', $user->id)->update(['is_active' => false]);
        }

        $user->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Berhasil logout'
        ]);
    }

    public function me(Request $request)
    {
        $user = $request->user();
        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'username' => $user->username,
            'role' => $user->role
        ]);
    }
}
