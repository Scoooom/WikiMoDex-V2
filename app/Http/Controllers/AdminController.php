<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Glitch;
use App\Models\WikiArticle;
use App\Models\GameItem;
use App\Models\AltBuild;
use App\Models\ChangelogEntry;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function dashboard()
    {
        $stats = [
            'users'     => User::count(),
            'admins'    => User::where('is_admin', true)->count(),
            'glitches'  => Glitch::count(),
            'articles'  => WikiArticle::count(),
            'items'     => GameItem::count(),
            'altBuilds' => AltBuild::count(),
            'changelog' => ChangelogEntry::count(),
        ];

        $recentUsers = User::orderByDesc('id')->take(8)->get();
        $recentGlitches = Glitch::withCount('likes')->orderByDesc('id')->take(6)->get();

        return view('admin.dashboard', compact('stats', 'recentUsers', 'recentGlitches'));
    }

    // ── User management ───────────────────────────────────────────

    public function users(Request $request)
    {
        $query = User::query();

        if ($search = $request->input('q')) {
            $query->where('username', 'like', "%{$search}%");
        }

        if ($request->input('admins_only')) {
            $query->where('is_admin', true);
        }

        $users = $query->orderByDesc('id')->paginate(30)->withQueryString();

        return view('admin.users', compact('users'));
    }

    public function toggleAdmin(User $user)
    {
        // Prevent self-demotion
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot change your own admin status.');
        }

        $user->update(['is_admin' => !$user->is_admin]);

        $action = $user->is_admin ? 'granted admin to' : 'revoked admin from';
        return back()->with('success', "Successfully {$action} {$user->username}.");
    }

    public function deleteUser(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        $username = $user->username;
        $user->delete();

        return back()->with('success', "Deleted user {$username}.");
    }
}
