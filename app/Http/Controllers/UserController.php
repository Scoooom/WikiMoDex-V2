<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserLike;
use App\Services\PrsvService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    private function isOwner(User $user): bool
    {
        if (!Auth::check()) return false;
        $authUser = Auth::user();
        return $authUser->id === $user->id || $authUser->isAdmin();
    }

    public function profile($username)
    {
        $user = User::where('username', $username)->firstOrFail();
        $isOwner = $this->isOwner($user);
        $likes = UserLike::where('creatorID', $user->id)->count();
        $glitches = $user->glitches;

        return view('profile', compact('user', 'isOwner', 'likes', 'glitches'));
    }

    public function uploadSave(Request $request, $username)
    {
        $user = User::where('username', $username)->firstOrFail();

        if (!$this->isOwner($user)) {
            return redirect('/');
        }

        $file = $request->file('saveFile');

        try {
            $decrypt = PrsvService::decrypt($file->getRealPath());
        } catch (\Throwable $e) {
            return redirect('/u:' . $username . '.html')->with('error', 'Invalid save file — could not decrypt.');
        }

        if (empty($decrypt->systemData)) {
            return redirect('/u:' . $username . '.html')->with('error', 'Invalid save file — this looks like a session save, not a system save. Please upload your system save file.');
        }

        $user->raw_prsv = file_get_contents($file->getRealPath());
        $user->b64_prsv = base64_encode(json_encode($decrypt));
        $user->save();

        \Illuminate\Support\Facades\Artisan::call('cf:purge', [
            '--url' => [rtrim(config('services.cloudflare.base_url'), '/') . '/trainercard:' . $username . '.html'],
        ]);

        return redirect('/u:' . $username . '.html')->with('success', 'Save File Uploaded');
    }

    public function deleteSave($username)
    {
        $user = User::where('username', $username)->firstOrFail();

        if (!$this->isOwner($user)) {
            return redirect('/');
        }

        $user->raw_prsv = null;
        $user->b64_prsv = null;
        $user->save();

        \Illuminate\Support\Facades\Artisan::call('cf:purge', [
            '--url' => [rtrim(config('services.cloudflare.base_url'), '/') . '/trainercard:' . $username . '.html'],
        ]);

        return redirect('/u:' . $username . '.html')->with('success', 'Save File Deleted');
    }

    public function downloadSave($username)
    {
        $user = User::where('username', $username)->firstOrFail();

        if (!$this->isOwner($user)) {
            return redirect('/');
        }

        return response()->streamDownload(function () use ($user) {
            echo $user->raw_prsv;
        }, $user->username . '.prsv', [
            'Content-Type' => 'application/octet-stream',
        ]);
    }

    public function trainerCard($username)
    {
        $user = User::where('username', $username)->firstOrFail();
        return view('trainercard', compact('user'));
    }

    public function handleProfilePost(Request $request, $username)
    {
        $action = $request->input('action');

        switch ($action) {
            case 'uploadNew':
                return $this->uploadSave($request, $username);
            case 'delSave':
                return $this->deleteSave($username);
            case 'dlSave':
                return $this->downloadSave($username);
            default:
                return redirect('/u:' . $username . '.html');
        }
    }
}
