<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Issue API token
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'device_name' => 'string|max:255',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if (!$user->is_active) {
            return response()->json([
                'message' => 'Your account is disabled. Please contact support.',
            ], 403);
        }

        // Create token with abilities based on role
        $abilities = $this->getAbilitiesForUser($user);

        $token = $user->createToken(
            $request->device_name ?? 'api-token',
            $abilities
        )->plainTextToken;

        return response()->json([
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role->name,
            ],
        ]);
    }

    /**
     * Revoke current token
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Token revoked successfully',
        ]);
    }

    /**
     * Revoke all tokens
     */
    public function logoutAll(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'message' => 'All tokens revoked successfully',
        ]);
    }

    /**
     * Get current user info
     */
    public function me(Request $request)
    {
        return response()->json([
            'user' => [
                'id' => $request->user()->id,
                'name' => $request->user()->name,
                'email' => $request->user()->email,
                'role' => $request->user()->role->name,
                'language' => $request->user()->language,
                'created_at' => $request->user()->created_at,
            ],
        ]);
    }

    /**
     * Get abilities for user based on role
     */
    protected function getAbilitiesForUser(User $user): array
    {
        if ($user->isAdmin()) {
            return [
                '*', // Admin gets all abilities
            ];
        }

        // Client abilities
        return [
            'services:read',
            'services:update',
            'invoices:read',
            'tickets:read',
            'tickets:create',
            'tickets:update',
            'profile:read',
            'profile:update',
        ];
    }
}
