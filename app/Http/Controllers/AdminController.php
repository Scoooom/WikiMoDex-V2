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
            'editors'   => User::where('is_wiki_editor', true)->where('is_admin', false)->count(),
            'glitches'  => Glitch::count(),
            'articles'  => WikiArticle::count(),
            'items'     => GameItem::count(),
            'altBuilds' => AltBuild::count(),
            'changelog' => ChangelogEntry::count(),
        ];

        $recentUsers    = User::orderByDesc('id')->take(8)->get();
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

        if ($request->input('editors_only')) {
            $query->where('is_wiki_editor', true);
        }

        $users = $query->orderByDesc('id')->paginate(30)->withQueryString();

        return view('admin.users', compact('users'));
    }

    public function toggleAdmin(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot change your own admin status.');
        }

        $user->update(['is_admin' => !$user->is_admin]);

        $action = $user->is_admin ? 'granted admin to' : 'revoked admin from';
        return back()->with('success', "Successfully {$action} {$user->username}.");
    }

    public function toggleEditor(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot change your own editor status.');
        }

        // Admins already have editor privileges — no point toggling
        if ($user->is_admin) {
            return back()->with('error', "{$user->username} is already an admin and inherits all editor privileges.");
        }

        $user->update(['is_wiki_editor' => !$user->is_wiki_editor]);

        $action = $user->is_wiki_editor ? 'granted wiki editor to' : 'revoked wiki editor from';
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
