<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\UrlToken;
use App\Models\User;
use App\Notifications\TwoFactorCodeNotification;
use App\Notifications\VerifyUserNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function register(RegisterRequest $request): JsonResponse
    {
        $payload = $request->validated();
        $payload['ip'] = get_ip();

        abort_if(User::where('ip', $payload['ip'])->exists(), 403, 'Sorry, there’s only one account allowed per household.');

        if (Arr::get($payload, 'referral_token')) {
            $referredBy = User::where('referral_token', $payload['referral_token'])->first();
            $payload['referred_by'] = optional($referredBy)->id;
        }

        do {
            $referralToken = Str::random(5);
            $isReferralTokenExists = User::where('referral_token', $referralToken)->exists();
        } while ($isReferralTokenExists);

        $payload['referral_token'] = $referralToken;

        $user = User::create($payload)->loadAvailablePoints();
        $user->refresh();

        /** @var UrlToken $urlToken */
        $urlToken = $user->urlTokens()->create([
            'type' => UrlToken::TYPE_VERIFICATION,
            'token' => UrlToken::getRandomToken(),
            'expires_at' => now()->addDay(),
        ]);

        $user->notify(new VerifyUserNotification($urlToken));
        $user->loginLog()->create(['ip' => $user->ip]);

        $token = auth()->login($user);

        return response()->json([
            'token' => $token,
            'user' => new UserResource($user),
        ], 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $payload = $request->validated();
        $faCode = $payload['two_factor_code'];
        unset($payload['two_factor_code']);

        $token = auth()->attempt($payload);

        abort_if(! $token, 422, 'Incorrect username or password');

        /** @var User $user */
        $user = auth()->user();

        if ($user->banned_at) {
            auth()->logout();
            abort(403, 'Your user is banned for the reason: '.$user->ban_reason);
        }

        if (! $user->two_factor_enabled_at) {
            $user->storeLoginLog();

            return response()->json([
                'token' => $token,
                'user' => new UserResource($user->loadAvailablePoints()),
            ]);
        }

        if (! $user->two_factor_expires_at || $user->two_factor_expires_at->isPast()) {
            $user->generateTwoFactorCode();
            $user->notify(new TwoFactorCodeNotification);
            abort(403, '2FA');
        }

        abort_if(is_null($faCode) || $user->two_factor_code !== $faCode, 422, '2FA-CODE');

        $user->resetTwoFactorCode();
        $user->storeLoginLog();

        return response()->json([
            'token' => $token,
            'user' => new UserResource($user->loadAvailablePoints()),
        ]);
    }

    public function getAuthUser(): JsonResponse
    {
        /** @var User $user */
        $user = auth()->user();

        return response()->json([
            'user' => new UserResource($user->loadAvailablePoints()),
        ]);
    }
}
