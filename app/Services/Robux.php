<?php

namespace App\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class Robux
{
    public static function getGroupSettingsResponse(string $cookie, int $groupId): Response
    {
        return Http::withHeaders([
            'cookie' => '.ROBLOSECURITY='.$cookie,
        ])->get("https://groups.roblox.com/v1/groups/{$groupId}/settings");
    }

    public static function getCurrency(): int
    {
        $robux = Cache::get('robux');

        if (! $robux) {
            return 0;
        }

        $response = Http::withHeaders([
            'cookie' => '.ROBLOSECURITY='.$robux['cookie'],
        ])->get("https://economy.roblox.com/v1/groups/{$robux['group_id']}/currency");

        if ($response->failed()) {
            return 0;
        }

        return $response['robux'];
    }

    public static function payout(array $group, string $username, int $amount): bool
    {
        $user = self::getUserByUsername($username);

        $authResponse = Http::withHeaders([
            'cookie' => '.ROBLOSECURITY='.$group['cookie'],
        ])->post('https://auth.roblox.com/v2/login');

        $response = Http::withHeaders([
            'X-CSRF-TOKEN' => $authResponse->headers()['x-csrf-token'],
            'cookie' => '.ROBLOSECURITY='.$group['cookie'],
        ])->post("https://groups.roblox.com/v1/groups/{$group['group_id']}/payouts", [
            'PayoutType' => 'FixedAmount',
            'Recipients' => [
                [
                    'recipientId' => $user['Id'],
                    'recipientType' => 'User',
                    'amount' => $amount,
                ],
            ],
        ]);

        if ($response->failed() && $response['errors'][0]['code'] === 27) {
            return false;
        }

        abort_if($response->status() === 400, 422, 'Group is invalid or does not exist');
        abort_if($response->failed(), 422, 'Payout has been failed, please try again later');

        return true;
    }

    public static function getUserByUsername(string $username): array
    {
        $response = Http::get("https://api.roblox.com/users/get-by-username?username={$username}");

        abort_if(isset($response['success']) && ! $response['success'], 422, 'Incorrect username!');

        return $response->json();
    }
}