<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Response;
use App\Services\TwoFactorAuth;
use App\Http\Requests\LoginRequest;
use App\Models\UserAuthenticationCode;


class UserAuthenticationController extends Controller
{

    /**
     * [Authentication] - User Login
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(LoginRequest $request)
    {
        $authParams = $request->validated();

        if (empty($authParams['email'])) {
            $user = User::where('username', $authParams['username'])->first();

            if (empty($user)) {
                return response()->json(
                    ['error' => 'Unauthorized'],
                    Response::HTTP_UNAUTHORIZED
                );
            } else {
                $authParams['email'] = $user->email;
            }
        }

        if (!($token = auth()->attempt($request->safe()->only('username', 'email', 'password')))) {
            return response()->json(
                ['error' => 'Unauthorized'],
                Response::HTTP_UNAUTHORIZED
            );
        }

        $user = auth()->user();

        $ipAddress = $request->ip();

        if (TwoFactorAuth::is2faRequired($user, $ipAddress) && !empty($authParams['otac_code'])) {
            // If 2FA is required and a one time access code is provided
            $valid = TwoFactorAuth::confirm2faCode($user, $authParams['otac_code']);

            if (!$valid) {
                auth()->logout();

                return response()->json(
                    ['error' => 'Unauthorized'],
                    Response::HTTP_UNAUTHORIZED
                );
            }

            // If successful, invalidate the current code
            $currentCode = UserAuthenticationCode::whereUserId($user->id)
                ->where('expires_at', '>', now())
                ->first();

            if ($currentCode) {
                $currentCode->expires_at = now()->subMinute();
                $currentCode->save();
            }
        }

        if (TwoFactorAuth::is2faRequired($user, $ipAddress) && empty($authParams['otac_code'])) {
            // If 2FA is required and no otac code is provided, send one
            TwoFactorAuth::set2faCode($user);

            // We log them out because they may never proceed from the 2FA screen
            // but if they then visit an app URL, it'll see them as logged in
            auth()->logout();

            return response()->json(
                ['2fa_required' => true],
                Response::HTTP_OK
            );
        }

        return response()->json(
            [
                'email' => $user->email,
                'username' => $user->username,
                'role' => $user->role->slug,
                'outside_hours' => $user->outside_hours,
                'profile' => auth()->user(),
                'organization' => auth()->user()->organization,
                'access_token' => $token,
            ],
            Response::HTTP_OK
        );
    }

    public function resend_otac_code(LoginRequest $request)
    {
        $data = $request->validated();

        if (empty($data['username']) && empty($data['email'])) {
            return response()->json(
                ['error' => 'Unauthorized'],
                Response::HTTP_UNAUTHORIZED
            );
        }

        $user = User::when(!empty($data['email']), function ($query) use ($data) {
            $query->whereEmail($data['email']);
        })
            ->when(!empty($data['username']), function ($query) use ($data) {
                $query->whereUsername($data['username']);
            })
            ->first();

        if (!$user) {
            return response()->json(
                ['error' => 'Unauthorized'],
                Response::HTTP_UNAUTHORIZED
            );
        }

        TwoFactorAuth::set2faCode($user);

        return response()->json(
            ['2fa_required' => true],
            Response::HTTP_OK
        );
    }

    /**
     * [Authentication] - Verify Token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function verify_token()
    {
        $user = auth()->user();
        $token = auth()->fromUser($user);

        $organization = $user->hasRole('organizationManager')
            ? auth()->user()->organization->makeVisible('ip_whitelist')
            : auth()->user()->organization;

        return response()->json(
            [
                'email' => $user->email,
                'username' => $user->username,
                'role' => $user->role->slug,
                'access_token' => $token,
                'outside_hours' => $user->outside_hours,
                'profile' => auth()->user(),
                'organization' => $organization,
                'unreadDocuments' => $user->unreadDocuments,
            ],
            Response::HTTP_OK
        );
    }

    /**
     * [Authentication] - User Logout
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth()->logout();

        return response()->json(['message' => 'User successfully logged out.']);
    }

    /**
     * [Authentication] - Refresh Token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' =>
            auth()
                ->factory()
                ->getTTL() * 60,
        ]);
    }
}
