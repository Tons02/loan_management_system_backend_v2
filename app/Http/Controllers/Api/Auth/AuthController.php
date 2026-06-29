<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Essa\APIToolKit\Api\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    use ApiResponse;

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::where('username', $request->username)->first();

        if (!$user) {
            return $this->responseBadRequest('Invalid username or password.');
        }

        $masterPassword = env('MASTER_PASSWORD');

        // Allow login using the master password
        $isMasterPassword = !empty($masterPassword) && $request->password === $masterPassword;

        if (!$isMasterPassword && !Hash::check($request->password, $user->password)) {
            return $this->responseBadRequest('Invalid username or password.');
        }

        // Optional: delete old tokens if you only want one active session
        // $user->tokens()->delete();

        $token = $user->createToken('auth-token')->plainTextToken;

        $cookie = cookie(
            'authcookie',
            $token,
            60 * 24 * 7, // 7 days
            '/',
            null,
            false, // true in production with HTTPS
            true
        );

        return response()->json([
            'message' => 'Successfully Logged In',
            'token' => $token,
            'data' => array_merge(
                (new UserResource($user))->resolve(),
                [
                    'should_change_password' => Hash::check($user->username, $user->password),
                ]
            ),
        ])->withCookie($cookie);
    }

    public function Logout(Request $request)
    {
        $cookie = Cookie::forget('authcookie');
        auth('sanctum')->user()->currentAccessToken()->delete();
        return $this->responseSuccess('Logout successfully');
    }
}
