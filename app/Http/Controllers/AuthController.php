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

        $raw = $discordUser->getRaw();
        $avatarHash = $raw['avatar'] ?? 'default';

        $user = User::where('username', $discordUser->getNickname() ?? $discordUser->getName())->first();

        if ($user) {
            $user->last_login = time();
            $user->avatar_id = $avatarHash;
            $user->save();
        } else {
            $user = User::create([
                'username'   => $discordUser->getNickname() ?? $discordUser->getName(),
                'user_id'    => $discordUser->getId(),
                'avatar_id'  => $avatarHash,
                'join_date'  => time(),
                'last_login' => time(),
            ]);
        }
        Auth::login($user);
        return redirect()->intended('/');
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
