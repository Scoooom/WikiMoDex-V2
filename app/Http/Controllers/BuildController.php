<?php

namespace App\Http\Controllers;

use App\Models\CommunityBuild;
use App\Models\CommunityBuildVote;
use App\Models\GameItem;
use Illuminate\Http\Request;
use App\Services\StatService;
use App\Models\CoreMove;
use Illuminate\Support\Facades\Auth;

class BuildController extends Controller
{
    // ── Public pages ─────────────────────────────────────────────

    public function index(Request $request)
    {
        $query = CommunityBuild::with('user')->withCount('buildVotes');

        if ($search = $request->input('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('team', 'like', "%{$search}%");
            });
        }

        $sort = $request->input('sort', 'top');
        match ($sort) {
            'new'   => $query->orderByDesc('created_at'),
            default => $query->orderByDesc('votes')->orderByDesc('created_at'),
        };

        $builds = $query->paginate(12)->withQueryString();

        return view('builds', compact('builds', 'sort'));
    }

    public function show(string $slug)
    {
        $build  = CommunityBuild::with('user')->where('slug', $slug)->firstOrFail();
        $voted  = $build->hasVotedBy(Auth::id());
        $items  = GameItem::orderBy('name')->get()->keyBy('key');

        // Resolve stats, typing, and alt build palette for each slot
        $slotStats = [];
        foreach ($build->team as $i => $slot) {
            $slotStats[$i] = StatService::resolveSlot($slot);
            $slotStats[$i]['types'] = StatService::resolveTypes($slot);
            // Attach alt build palette if applicable
            $altBuild = \App\Models\AltBuild::where('name', $slot['species'] ?? '')->first();
            if ($altBuild) {
                $rank = (int)($slot['alt_build_rank'] ?? 1);
                $useDark = $rank >= 6 && !empty($altBuild->dark_palette);
                $slotStats[$i]['palette'] = $useDark ? $altBuild->dark_palette : ($altBuild->target_palette ?? []);
            } else {
                $slotStats[$i]['palette'] = [];
            }
            // Attach mod glitch ID for sprite lookup
            $slotStats[$i]['glitch_id'] = null;
            if ($slotStats[$i]['source'] === 'glitch') {
                $glitch = \App\Models\Glitch::where('name', $slot['species'] ?? '')->first();
                $slotStats[$i]['glitch_id'] = $glitch?->id;
            }
            // Calculate effective stats if level is set
            $level = (int) ($slot['level'] ?? 0);
            if ($level > 0 && !empty($slotStats[$i]['stats'])) {
                $slotStats[$i]['effective'] = StatService::calcEffectiveStats(
                    $slotStats[$i]['stats'],
                    $slot['nature'] ?? 'Hardy',
                    $level,
                    $slot['items'] ?? [],
                    $slot['ability'] ?? null,
                    $slot['passive_ability'] ?? null
                );
            }
        }

        // Resolve move data for display
        $moveCache = CoreMove::whereIn('name', collect($build->team)
            ->flatMap(fn($s) => $s['moves'] ?? [])
            ->filter()->unique()->values()->all()
        )->get()->keyBy('name');

        return view('build-show', compact('build', 'voted', 'items', 'slotStats', 'moveCache'));
    }

    // ── Create / store ────────────────────────────────────────────

    public function create()
    {
        abort_unless(Auth::check(), 403, 'Login required');
        $plateDriveTypes = [
            'FIST_PLATE'=>'Fighting','SKY_PLATE'=>'Flying','TOXIC_PLATE'=>'Poison',
            'EARTH_PLATE'=>'Ground','STONE_PLATE'=>'Rock','INSECT_PLATE'=>'Bug',
            'SPOOKY_PLATE'=>'Ghost','IRON_PLATE'=>'Steel','FLAME_PLATE'=>'Fire',
            'SPLASH_PLATE'=>'Water','MEADOW_PLATE'=>'Grass','ZAP_PLATE'=>'Electric',
            'MIND_PLATE'=>'Psychic','ICICLE_PLATE'=>'Ice','DRACO_PLATE'=>'Dragon',
            'DREAD_PLATE'=>'Dark','PIXIE_PLATE'=>'Fairy','BLANK_PLATE'=>'Normal',
            'SHOCK_DRIVE'=>'Electric','BURN_DRIVE'=>'Fire',
            'CHILL_DRIVE'=>'Ice','DOUSE_DRIVE'=>'Water',
        ];
        $whitelist = [
            'STAT_SWITCHER', 'PRIMARY_TYPE_SWITCHER', 'SECONDARY_TYPE_SWITCHER',
            'TYPE_SWITCHER', 'STAT_SACRIFICE', 'TYPE_SACRIFICE', 'POKEMON_ALT_BUILD',
            'HP_UP', 'PROTEIN', 'IRON', 'CALCIUM', 'ZINC', 'CARBOS',
            'SOUL_DEW',
            ...array_keys($plateDriveTypes),
        ];
        $items     = GameItem::whereIn('key', $whitelist)->orderBy('name')->get();
        $species   = \App\Models\BuiltinForm::orderBy('name')->pluck('name');
        return view('build-create', compact('items', 'species'));
    }

    public function store(Request $request)
    {
        abort_unless(Auth::check(), 403, 'Login required');

        $data = $request->validate([
            'title'                         => 'required|string|max:80',
            'description'                   => 'nullable|string|max:1000',
            'team'                          => 'required|array|min:1|max:6',
            'team.*.species'                => 'nullable|string|max:60',
            'team.*.dex_number'             => 'nullable|integer',
            'team.*.ability'                => 'nullable|string|max:80',
            'team.*.passive_ability'        => 'nullable|string|max:80',
            'team.*.nature'                 => 'nullable|string|max:20',
            'team.*.moves'                  => 'nullable|array|max:4',
            'team.*.moves.*'                => 'nullable|string|max:60',
            'team.*.items'                  => 'nullable|array|max:20',
            'team.*.items.*.key'            => 'nullable|string|max:60',
            'team.*.items.*.name'           => 'nullable|string|max:80',
            'team.*.items.*.stack'          => 'nullable|integer|min:1|max:99',
            'team.*.items.*.params'         => 'nullable|array',
            'team.*.items.*.params.type1'   => 'nullable|string|max:20',
            'team.*.items.*.params.type2'   => 'nullable|string|max:20',
            'team.*.items.*.params.stat1'   => 'nullable|string|max:10',
            'team.*.items.*.params.stat2'   => 'nullable|string|max:10',
            'team.*.alt_build_rank'          => 'nullable|integer|min:1|max:9',
            'team.*.level'                   => 'nullable|integer|min:1|max:10000',
            'team.*.override_type1'         => 'nullable|string|max:20',
            'team.*.override_type2'         => 'nullable|string|max:20',
            'team.*.notes'                  => 'nullable|string|max:500',
        ]);

        // Filter out completely empty slots
        $team = collect($data['team'])->filter(fn($slot) => !empty($slot['species']))->values()->toArray();

        if (empty($team)) {
            return back()->withErrors(['team' => 'Add at least one Pokémon.'])->withInput();
        }

        $user  = Auth::user();
        $slug  = CommunityBuild::generateSlug($data['title'], $user->username);

        $build = CommunityBuild::create([
            'slug'        => $slug,
            'title'       => $data['title'],
            'description' => $data['description'] ?? null,
            'user_id'     => $user->id,
            'team'        => $team,
        ]);

        return redirect("/build/{$build->slug}.html")->with('success', 'Build submitted!');
    }

    // ── Edit / update ─────────────────────────────────────────────

    public function edit(string $slug)
    {
        $build = CommunityBuild::where('slug', $slug)->firstOrFail();
        $user  = Auth::user();
        abort_unless($user && ($user->id === $build->user_id || $user->isAdmin()), 403);

        $plateDriveTypes = [
            'FIST_PLATE'=>'Fighting','SKY_PLATE'=>'Flying','TOXIC_PLATE'=>'Poison',
            'EARTH_PLATE'=>'Ground','STONE_PLATE'=>'Rock','INSECT_PLATE'=>'Bug',
            'SPOOKY_PLATE'=>'Ghost','IRON_PLATE'=>'Steel','FLAME_PLATE'=>'Fire',
            'SPLASH_PLATE'=>'Water','MEADOW_PLATE'=>'Grass','ZAP_PLATE'=>'Electric',
            'MIND_PLATE'=>'Psychic','ICICLE_PLATE'=>'Ice','DRACO_PLATE'=>'Dragon',
            'DREAD_PLATE'=>'Dark','PIXIE_PLATE'=>'Fairy','BLANK_PLATE'=>'Normal',
            'SHOCK_DRIVE'=>'Electric','BURN_DRIVE'=>'Fire',
            'CHILL_DRIVE'=>'Ice','DOUSE_DRIVE'=>'Water',
        ];
        $whitelist = [
            'STAT_SWITCHER', 'PRIMARY_TYPE_SWITCHER', 'SECONDARY_TYPE_SWITCHER',
            'TYPE_SWITCHER', 'STAT_SACRIFICE', 'TYPE_SACRIFICE', 'POKEMON_ALT_BUILD',
            'HP_UP', 'PROTEIN', 'IRON', 'CALCIUM', 'ZINC', 'CARBOS',
            'SOUL_DEW',
            ...array_keys($plateDriveTypes),
        ];
        $items   = GameItem::whereIn('key', $whitelist)->orderBy('name')->get();
        $species = \App\Models\BuiltinForm::orderBy('name')->pluck('name');

        return view('build-edit', compact('build', 'items', 'species'));
    }

    public function update(Request $request, string $slug)
    {
        $build = CommunityBuild::where('slug', $slug)->firstOrFail();
        $user  = Auth::user();
        abort_unless($user && ($user->id === $build->user_id || $user->isAdmin()), 403);

        $data = $request->validate([
            'title'                         => 'required|string|max:80',
            'description'                   => 'nullable|string|max:1000',
            'team'                          => 'required|array|min:1|max:6',
            'team.*.species'                => 'nullable|string|max:60',
            'team.*.dex_number'             => 'nullable|integer',
            'team.*.ability'                => 'nullable|string|max:80',
            'team.*.passive_ability'        => 'nullable|string|max:80',
            'team.*.nature'                 => 'nullable|string|max:20',
            'team.*.alt_build_rank'         => 'nullable|integer|min:1|max:9',
            'team.*.level'                  => 'nullable|integer|min:1|max:10000',
            'team.*.moves'                  => 'nullable|array|max:4',
            'team.*.moves.*'                => 'nullable|string|max:60',
            'team.*.items'                  => 'nullable|array|max:20',
            'team.*.items.*.key'            => 'nullable|string|max:60',
            'team.*.items.*.name'           => 'nullable|string|max:80',
            'team.*.items.*.stack'          => 'nullable|integer|min:1|max:99',
            'team.*.items.*.params'         => 'nullable|array',
            'team.*.items.*.params.type1'   => 'nullable|string|max:20',
            'team.*.items.*.params.type2'   => 'nullable|string|max:20',
            'team.*.items.*.params.stat1'   => 'nullable|string|max:10',
            'team.*.items.*.params.stat2'   => 'nullable|string|max:10',
            'team.*.override_type1'         => 'nullable|string|max:20',
            'team.*.override_type2'         => 'nullable|string|max:20',
            'team.*.notes'                  => 'nullable|string|max:500',
        ]);

        $team = collect($data['team'])->filter(fn($slot) => !empty($slot['species']))->values()->toArray();

        if (empty($team)) {
            return back()->withErrors(['team' => 'Add at least one Pokémon.'])->withInput();
        }

        $build->update([
            'title'       => $data['title'],
            'description' => $data['description'] ?? null,
            'team'        => $team,
        ]);

        return redirect("/build/{$build->slug}.html")->with('success', 'Build updated!');
    }

    // ── Delete ────────────────────────────────────────────────────

    public function destroy(string $slug)
    {
        $build = CommunityBuild::where('slug', $slug)->firstOrFail();

        $user = Auth::user();
        abort_unless($user && ($user->id === $build->user_id || $user->isAdmin()), 403);

        $build->delete();
        return redirect('/builds.html')->with('success', 'Build deleted.');
    }

    // ── Voting ────────────────────────────────────────────────────

    public function vote(string $slug)
    {
        abort_unless(Auth::check(), 403);

        $build = CommunityBuild::where('slug', $slug)->firstOrFail();
        $userId = Auth::id();

        $existing = CommunityBuildVote::where('build_id', $build->id)
            ->where('user_id', $userId)
            ->first();

        if ($existing) {
            // Toggle off
            $existing->delete();
            $build->decrement('votes');
            $voted = false;
        } else {
            CommunityBuildVote::create(['build_id' => $build->id, 'user_id' => $userId]);
            $build->increment('votes');
            $voted = true;
        }

        if (request()->expectsJson()) {
            return response()->json(['votes' => $build->fresh()->votes, 'voted' => $voted]);
        }

        return back();
    }
}
