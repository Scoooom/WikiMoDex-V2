<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserLike;
use App\Services\PrsvService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function profile($username)
    {
        $user = User::where('username', $username)->firstOrFail();
        $isOwner = Auth::check() && (Auth::user()->username === $username || Auth::user()->username === 'scooom');
        $likes = UserLike::where('creatorID', $user->id)->count();
        $glitches = $user->glitches;

        return view('profile', compact('user', 'isOwner', 'likes', 'glitches'));
    }

    public function uploadSave(Request $request, $username)
    {
        $user = User::where('username', $username)->firstOrFail();
        $isOwner = Auth::check() && (Auth::user()->username === $username || Auth::user()->username === 'scooom');

        if (!$isOwner) {
            return redirect('/');
        }

        $file = $request->file('saveFile');
        $decrypt = PrsvService::decrypt($file->getRealPath());

        $user->raw_prsv = file_get_contents($file->getRealPath());
        $user->b64_prsv = base64_encode(json_encode($decrypt));
        $user->save();

        return redirect('/u:' . $username . '.html')->with('success', 'Save File Uploaded');
    }

    public function deleteSave($username)
    {
        $user = User::where('username', $username)->firstOrFail();
        $isOwner = Auth::check() && (Auth::user()->username === $username || Auth::user()->username === 'scooom');

        if (!$isOwner) {
            return redirect('/');
        }

        $user->raw_prsv = null;
        $user->b64_prsv = null;
        $user->save();

        return redirect('/u:' . $username . '.html')->with('success', 'Save File Deleted');
    }

    public function downloadSave($username)
    {
        $user = User::where('username', $username)->firstOrFail();
        $isOwner = Auth::check() && (Auth::user()->username === $username || Auth::user()->username === 'scooom');

        if (!$isOwner) {
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
