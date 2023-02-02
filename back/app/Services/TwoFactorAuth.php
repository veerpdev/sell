<?php

namespace App\Services;

use App\Mail\GenericNotificationEmail;
use App\Models\User;
use App\Models\UserAuthenticationCode;
use Exception;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class TwoFactorAuth
{
    public static function confirm2faCode(User $user, $providedCode): bool
    {
        $validator = Validator::make(['code' => $providedCode], [
            'code' => 'required|digits:6|numeric',
        ]);

        if ($validator->fails()) {
            return false;
        }

        $currentCode = UserAuthenticationCode::whereUserId($user->id)
            ->where('expires_at', '>', now())
            ->first();

        if (!$currentCode || now()->gt($currentCode->expires_at)) {
            return false;
        }

        return Hash::check($providedCode, $currentCode->token);
    }

    public static function is2faRequired(User $user, $ipAddress): bool
    {
        if (env('DISABLE_2FA', null)) {
            // We should never put this env variable in, except for
            // local dev so we don't have to do it every single time
            return false;
        }

        $organization = $user->organization->makeVisible('ip_whitelist');

        $validIps = is_array($organization->ip_whitelist) ? $organization->ip_whitelist : [];

        return !in_array($ipAddress, $validIps);
    }

    public static function set2faCode(User $user): bool
    {
        try {
            // Get any current code and expire it
            $currentCode = UserAuthenticationCode::whereUserId($user->id)
                ->where('expires_at', '>', now())
                ->first();

            if ($currentCode) {
                $currentCode->expires_at = now()->subMinute();
                $currentCode->save();
            }

            $otacCode = generateRandomKey();

            $otacExpiry = now()->addMinutes(5);

            UserAuthenticationCode::create([
                'user_id' => $user->id,
                'token'   => Hash::make($otacCode),
                'expires_at' => $otacExpiry,
            ]);

            $message = "Here's your one-time login code for Aurora: {$otacCode}. This code is only valid for 5 minutes.";

            if (env('TWILIO_SID', null)) {
                // If there's no ability to send an SMS, send an email instead
                $user->sendSMS($message);
            } else {
                $user->sendEmail(new GenericNotificationEmail('Your one-time code', $message));
            }

            return true;
        } catch (Exception $e) {
            Log::error('Could not generate and store 2FA code', ['context' => $e->getMessage()]);
            return false;
        }
    }
}
