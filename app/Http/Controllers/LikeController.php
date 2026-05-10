<?php

namespace App\Http\Controllers;

use App\Models\GlitchLike;
use App\Models\GlitchDislike;
use App\Models\UserLike;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LikeController extends Controller
{
    private function getUser()
    {
        return Auth::user();
    }

    public function like($id, Request $request)
    {
        $user = $this->getUser();
        if (!$user) return $this->deny($request);

        GlitchLike::firstOrCreate([
            'glitchID' => $id,
            'userID'   => $user->user_id,
        ]);

        GlitchDislike::where('glitchID', $id)
            ->where('userID', $user->user_id)
            ->delete();

        return redirect($request->input('returnURL', '/'));
    }

    public function removeLike($id, Request $request)
    {
        $user = $this->getUser();
        if (!$user) return $this->deny($request);

        GlitchLike::where('glitchID', $id)
            ->where('userID', $user->user_id)
            ->delete();

        return redirect($request->input('returnURL', '/'));
    }

    public function dislike($id, Request $request)
    {
        $user = $this->getUser();
        if (!$user) return $this->deny($request);

        GlitchDislike::firstOrCreate([
            'glitchID' => $id,
            'userID'   => $user->user_id,
        ]);

        GlitchLike::where('glitchID', $id)
            ->where('userID', $user->user_id)
            ->delete();

        return redirect($request->input('returnURL', '/'));
    }

    public function removeDislike($id, Request $request)
    {
        $user = $this->getUser();
        if (!$user) return $this->deny($request);

        GlitchDislike::where('glitchID', $id)
            ->where('userID', $user->user_id)
            ->delete();

        return redirect($request->input('returnURL', '/'));
    }

    public function likeUser($id, Request $request)
    {
        $user = $this->getUser();
        if (!$user) return $this->deny($request);

        UserLike::firstOrCreate([
            'creatorID' => $id,
            'userID'    => $user->user_id,
        ]);

        return redirect($request->input('returnURL', '/'));
    }

    public function removeUserLike($id, Request $request)
    {
        $user = $this->getUser();
        if (!$user) return $this->deny($request);

        UserLike::where('creatorID', $id)
            ->where('userID', $user->user_id)
            ->delete();

        return redirect($request->input('returnURL', '/'));
    }

    private function deny(Request $request)
    {
        return redirect($request->input('returnURL', '/'));
    }
}
