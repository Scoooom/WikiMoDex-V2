<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    public function redirect()
    {
        return Socialite::driver('discord')->redirect();
    }

    public function callback()
    {
        try {
            $discordUser = Socialite::driver('discord')->user();
        } catch (\Exception $e) {
            return redirect('/')->with('error', 'Internal Error. Please try again.');
        }

        $raw        = $discordUser->getRaw();
        $avatarHash = $raw['avatar'] ?? 'default';
        $discordId  = $discordUser->getId();
        $mfaEnabled = (bool) ($raw['mfa_enabled'] ?? false);

        $user = User::where('username', $discordUser->getNickname() ?? $discordUser->getName())->first();

        if ($user) {
            $user->last_login  = time();
            $user->avatar_id   = $avatarHash;
            $user->mfa_enabled = $mfaEnabled;
            $user->save();
        } else {
            $user = User::create([
                'username'    => $discordUser->getNickname() ?? $discordUser->getName(),
                'user_id'     => $discordId,
                'avatar_id'   => $avatarHash,
                'join_date'   => time(),
                'last_login'  => time(),
                'mfa_enabled' => $mfaEnabled,
            ]);
        }

        // Cache avatar locally so we're not dependent on Discord CDN
        $this->cacheAvatar($discordId, $avatarHash);

        Auth::login($user);
        return redirect()->intended('/');
    }

    private function cacheAvatar(string $discordId, string $avatarHash): void
    {
        if ($avatarHash === 'default') {
            return; // Nothing to cache — getAvatarURL() handles the fallback
        }

        $dest = public_path("avatars/{$discordId}.png");

        // Only re-download if the hash changed (compare stored hash file)
        $hashFile = public_path("avatars/{$discordId}.hash");
        if (file_exists($dest) && file_exists($hashFile) && file_get_contents($hashFile) === $avatarHash) {
            return; // Already up to date
        }

        $url = "https://cdn.discordapp.com/avatars/{$discordId}/{$avatarHash}.png?size=128";

        try {
            $ctx  = stream_context_create(['http' => ['timeout' => 5]]);
            $data = @file_get_contents($url, false, $ctx);

            if ($data !== false) {
                if (!is_dir(public_path('avatars'))) {
                    mkdir(public_path('avatars'), 0755, true);
                }
                file_put_contents($dest, $data);
                file_put_contents($hashFile, $avatarHash);
            }
        } catch (\Throwable $e) {
            // Non-fatal — will fall back to Discord CDN URL
        }
    }

    public function logout()
    {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        return redirect('/');
    }

    public function handleLogin()
    {
        if (request()->has('logoutkey')) {
            return $this->logout();
        }
        return $this->redirect();
    }
}
