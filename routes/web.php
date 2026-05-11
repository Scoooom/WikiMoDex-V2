<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\GlitchController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\SpriteController;

// Home — 5 min (stats + featured glitches change)
Route::get('/', function () {
    $stats = [
        'glitches' => \App\Models\Glitch::count(),
        'core'     => \App\Models\BuiltinForm::where('form_type', 'core')->count(),
        'smitty'   => \App\Models\BuiltinForm::where('form_type', 'smitty')->count(),
        'users'    => \App\Models\User::count(),
    ];
    $featured = \App\Models\Glitch::withCount('likes')
        ->orderBy('likes_count', 'desc')
        ->take(6)
        ->get();
    return view('welcome', compact('stats', 'featured'));
})->middleware(['nosession', 'cache:public, max-age=0, s-maxage=300, stale-while-revalidate=30']);

// Auth — never cache
Route::get('/auth/discord/redirect', [AuthController::class, 'redirect'])->name('auth.discord');
Route::get('/auth/discord/callback', [AuthController::class, 'callback']);
Route::match(['get', 'post'], '/login.html', [AuthController::class, 'handleLogin']);
Route::get('/logout.html', [AuthController::class, 'logout'])->name('logout');

// Glitches — gallery 5 min, individual pages 1 hour, write/download no-store
Route::middleware(['nosession', 'cache:public, max-age=0, s-maxage=31536000, stale-while-revalidate=30'])->group(function () {
    Route::get('/gallery.html', [GlitchController::class, 'index']);
    Route::get('/galleryCore.html', [GlitchController::class, 'galleryCore']);
    Route::get('/gallerySmitty.html', [GlitchController::class, 'gallerySmitty']);
    Route::get('/gallerySmittyForm.html', [GlitchController::class, 'gallerySmittyForm']);
});
Route::get('/g:{form}:{id}.html', [GlitchController::class, 'show'])->middleware('cache:no-store');
Route::middleware(['nosession', 'cache:public, max-age=0, s-maxage=31536000, stale-while-revalidate=30'])->group(function () {
    Route::get('/core:{form}.html', [GlitchController::class, 'coreMon']);
    Route::get('/smitty:{form}.html', [GlitchController::class, 'smittyMon']);
    Route::get('/smittyForm:{form}.html', [GlitchController::class, 'smittyFormMon']);
});
Route::get('/create.html', [GlitchController::class, 'create'])->middleware('cache:no-store');
Route::post('/upload.html', [GlitchController::class, 'store']);
Route::get('/d:{id}.html', [GlitchController::class, 'download'])->middleware('cache:no-store');

// Sprites — browser AND CDN cache for a year, URL is ID-based so never stale
Route::middleware(['nosession', 'cache:public, max-age=0, s-maxage=31536000, stale-while-revalidate=30'])->group(function () {
    Route::get('/front:{id}.png', [SpriteController::class, 'front']);
    Route::get('/back:{id}.png', [SpriteController::class, 'back']);
    Route::get('/cFront:{name}.png', [SpriteController::class, 'coreFront']);
    Route::get('/cBack:{name}.png', [SpriteController::class, 'coreBack']);
});

// Likes
Route::middleware('throttle:30,1')->group(function () {
    Route::post('/like:{id}.html', [LikeController::class, 'like']);
    Route::post('/rLike:{id}.html', [LikeController::class, 'removeLike']);
    Route::post('/dislike:{id}.html', [LikeController::class, 'dislike']);
    Route::post('/rDislike:{id}.html', [LikeController::class, 'removeDislike']);
    Route::post('/uLike:{id}.html', [LikeController::class, 'likeUser']);
    Route::post('/uRLike:{id}.html', [LikeController::class, 'removeUserLike']);
});

// Auth state — lightweight endpoint for JS injection, never cached
Route::get('/me.json', function () {
    if (!Auth::check()) {
        return response()->json(['authed' => false])
            ->header('Cache-Control', 'no-store');
    }
    $user = Auth::user();
    return response()->json([
        'authed'   => true,
        'username' => $user->username,
        'avatar'   => $user->getAvatarURL(),
        'profile'  => '/u:' . $user->username . '.html',
        'isAdmin'  => $user->isAdmin(),
        'isEditor' => $user->isWikiEditor(),
    ])->header('Cache-Control', 'no-store');
})->name('me');

// Users — no-store (isOwner panel, like buttons are user-specific)
Route::get('/u:{username}.html', [UserController::class, 'profile'])->middleware('cache:no-store');
Route::post('/u:{username}.html', [UserController::class, 'handleProfilePost']);
Route::get('/trainercard:{username}.html', [UserController::class, 'trainerCard'])->middleware(['nosession', 'cache:public, max-age=0, s-maxage=31536000, stale-while-revalidate=30']);

// Static pages — 1 hour
Route::middleware(['nosession', 'cache:public, max-age=0, s-maxage=31536000, stale-while-revalidate=30'])->group(function () {
    Route::get('/faq.html', fn() => view('faq'));
    Route::get('/gacha.html', fn() => view('gacha'));
});

// Bot
Route::post('/discord/interactions', [App\Http\Controllers\DiscordInteractionController::class, 'handle'])->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);

// ── Pokevoid sprites ──────────────────────────────────────────────────────
Route::get('/alt-build-stats/{buildId}.json', function ($buildId) {
    $build = \App\Models\AltBuild::where('build_id', $buildId)->first();
    if (!$build || !$build->dex_number) abort(404);

    $pokemon = \App\Services\PokemonService::getMon($build->dex_number);
    if (!$pokemon || !isset($pokemon->stats)) abort(404);

    $baseStats = array_map(fn($s) => $s->base_stat, $pokemon->stats);

    $statMap = ['HP' => 0, 'ATK' => 1, 'DEF' => 2, 'SP.ATK' => 3, 'SP.DEF' => 4, 'SPD' => 5];
    $focusParts = array_map('trim', explode('/', $build->stat_focus ?? ''));
    $focusIndices = array_values(array_filter(array_map(fn($s) => $statMap[$s] ?? null, $focusParts), fn($v) => $v !== null));

    // Port of calculateAltBuildStatsWithSwapping
    $controller = app(\App\Http\Controllers\DiscordInteractionController::class);
    $calculated = (new class($baseStats, $focusIndices, $build->rank ?? 1) {
        public function calc($stats, $focus, $rank) {
            $new = $stats;
            foreach ($focus as $ri => $fi) {
                $ranked = [];
                for ($s = 0; $s <= 5; $s++) $ranked[] = [$s, $new[$s]];
                usort($ranked, fn($a,$b) => $b[1]-$a[1]);
                $hi = $ranked[$ri][0];
                if ($fi !== $hi) { $t=$new[$fi]; $new[$fi]=$new[$hi]; $new[$hi]=$t; }
            }
            $target = 425 + min($rank,9)*25;
            $cur = array_sum($new);
            if ($cur >= $target) return $new;
            $nf = array_values(array_filter([0,1,2,3,4,5], fn($i)=>!in_array($i,$focus)));
            $alloc = $new; $diff = $target - $cur;
            if ($diff <= 30) {
                $ft = array_sum(array_map(fn($i)=>$new[$i],$focus));
                foreach ($focus as $i) $alloc[$i]=$new[$i]+(int)floor($diff*($ft>0?$new[$i]/$ft:1/count($focus)));
                $d=$target-array_sum($alloc); $ix=0;
                while($d&&$ix<100){$t=$focus[$ix%count($focus)];$d>0?$alloc[$t]++:$alloc[$t]--;$d>0?$d--:$d++;$ix++;}
                return $alloc;
            }
            $cap=(int)floor($target*0.30); $fb=count($focus)*(int)floor($cap*0.80); $nfb=$target-$fb;
            $nfr=array_map(fn($i)=>['i'=>$i,'v'=>$new[$i]],$nf);
            usort($nfr,fn($a,$b)=>$b['v']-$a['v']);
            $ws=[];foreach($nfr as $r=>$s){$p=0.50-($r/max(1,count($nfr)-1))*0.15;$ws[]=[$s['i'],pow($p,1.5)];}
            $tw=array_sum(array_column($ws,1));
            foreach($ws as[$i,$w])$alloc[$i]=max($new[$i],(int)floor($nfb*$w/$tw));
            $fw=array_map(fn($i)=>$new[$i]+50,$focus);$tfw=array_sum($fw);
            foreach($focus as $k=>$i)$alloc[$i]=$tfw>0?(int)floor($fb*$fw[$k]/$tfw):(int)floor($fb/count($focus));
            $c=array_map(fn($s)=>min($s,$cap),$alloc);
            $d=$target-array_sum($c);$ai=array_merge($focus,$nf);$ai2=0;
            while($d&&$ai2<1000){$t=$ai[$ai2%count($ai)];
                if($d>0&&$c[$t]<$cap){$c[$t]++;$d--;}elseif($d<0&&$c[$t]>1){$c[$t]--;$d++;}$ai2++;}
            return $c;
        }
    })->calc($baseStats, $focusIndices, $build->rank ?? 1);

    $labels = ['HP', 'ATK', 'DEF', 'SP.ATK', 'SP.DEF', 'SPD'];
    $result = [];
    foreach ($calculated as $i => $val) {
        $result[] = ['label' => $labels[$i], 'value' => $val, 'focus' => in_array($i, $focusIndices)];
    }

    return response()->json(['stats' => $result, 'bst' => array_sum($calculated)])
        ->header('Cache-Control', 'public, max-age=3600');
});

Route::get('/pokevoid-atlas/{dex}.json', function ($dex) {
    if (!preg_match('/^\d+$/', $dex)) abort(404);
    $path = base_path("pokevoid/public/images/pokemon/{$dex}.png");
    if (!file_exists($path)) abort(404);
    $out = shell_exec("python3 " . escapeshellarg(base_path('scripts/extract_atlas.py')) . " " . escapeshellarg($path) . " 2>/dev/null");
    if (!$out) abort(404);
    return response($out)->header('Content-Type', 'application/json')
                          ->header('Access-Control-Allow-Origin', '*')
                          ->header('Cache-Control', 'public, max-age=86400');
})->where('dex', '\d+');

Route::get('/alt-build-sprite:{buildId}.png', function ($buildId) {
    $build = \App\Models\AltBuild::where('build_id', $buildId)->first();
    if (!$build || !$build->dex_number || !$build->target_palette) abort(404);

    $outPath = storage_path("app/alt-build-sprites/{$buildId}.png");

    if (!file_exists($outPath)) {
        $script    = base_path('scripts/render_alt_build_sprite.py');
        $srcSprite = base_path("pokevoid/public/images/pokemon/{$build->dex_number}.png");
        if (!file_exists($script) || !file_exists($srcSprite)) abort(404);
        if (!is_dir(dirname($outPath))) mkdir(dirname($outPath), 0755, true);
        $palette = escapeshellarg(json_encode($build->target_palette));
        shell_exec("python3 {$script} " . escapeshellarg($srcSprite) . " {$palette} " . escapeshellarg($outPath) . " 2>/dev/null");
        if (!file_exists($outPath)) abort(404);
    }

    return response()->file($outPath, ['Content-Type' => 'image/png', 'Cache-Control' => 'public, max-age=3600']);
});

// ── Move search ──────────────────────────────────────────────────
Route::get('/move-search.json', [App\Http\Controllers\MoveSearchController::class, 'search'])
    ->middleware('cache:no-store');

// ── Pokémon search (unified across all sources) ──────────────────
Route::get('/pokemon-search.json', [App\Http\Controllers\PokemonSearchController::class, 'search'])
    ->middleware('cache:no-store');

// ── Community Builds ──────────────────────────────────────────────
use App\Http\Controllers\BuildController;

// Gallery + show — publicly cacheable (1 hour CDN, but no-store for logged-in vote state)
Route::get('/builds.html',              [BuildController::class, 'index'])->middleware('cache:no-store');
Route::get('/build/{slug}.html',        [BuildController::class, 'show'])->middleware('cache:no-store');

// Create / store — auth required, never cached
Route::get('/builds/new.html',          [BuildController::class, 'create'])->middleware('cache:no-store');
Route::post('/builds',                  [BuildController::class, 'store']);
Route::delete('/build/{slug}.html',     [BuildController::class, 'destroy']);
Route::post('/build/{slug}/vote.html',  [BuildController::class, 'vote'])->middleware('throttle:30,1');
Route::get('/build/{slug}/edit.html',   [BuildController::class, 'edit'])->middleware('cache:no-store');
Route::post('/build/{slug}/edit.html',  [BuildController::class, 'update']);

// ── Wiki ──────────────────────────────────────────────────────────
use App\Http\Controllers\WikiController;

// Wiki search — no-store (dynamic per query)
Route::get('/wiki-search.json', [\App\Http\Controllers\WikiSearchController::class, 'search'])
    ->name('wiki.search')
    ->middleware('cache:no-store');

// Wiki pages — 1 hour
Route::middleware(['nosession', 'cache:public, max-age=0, s-maxage=31536000, stale-while-revalidate=30'])->group(function () {
    Route::get('/wiki.html',            [WikiController::class, 'index'])->name('wiki.index');
    Route::get('/wiki:items.html',      [WikiController::class, 'items'])->name('wiki.items');
    Route::get('/wiki:alt-builds.html', [WikiController::class, 'altBuilds'])->name('wiki.altbuilds');
    Route::get('/wiki:changelog.html',  [WikiController::class, 'changelog'])->name('wiki.changelog');
    Route::get('/wiki:{slug}.html',     [WikiController::class, 'show'])->name('wiki.show');
});

// ── Admin — never cached ──────────────────────────────────────────
use App\Http\Controllers\AdminController;

// Admin-only routes (full admins with 2FA)
Route::middleware(['admin', 'cache:no-store'])->prefix('admin')->group(function () {
    Route::get('/users.html',                [AdminController::class, 'users'])->name('admin.users');
    Route::post('/users/{user}/toggle-admin',[AdminController::class, 'toggleAdmin'])->name('admin.users.toggle');
    Route::post('/users/{user}/toggle-editor',[AdminController::class, 'toggleEditor'])->name('admin.users.toggle-editor');
    Route::delete('/users/{user}',           [AdminController::class, 'deleteUser'])->name('admin.users.delete');
});

// Editor routes (wiki editors + admins, both require 2FA)
Route::middleware(['editor', 'cache:no-store'])->prefix('admin')->group(function () {
    Route::get('/',                    [AdminController::class, 'dashboard'])->name('admin.dashboard');

    // Wiki
    Route::get('/wiki.html',           [WikiController::class, 'adminIndex'])->name('wiki.admin.index');
    Route::get('/wiki/new.html',       [WikiController::class, 'adminNew'])->name('wiki.admin.new');
    Route::post('/wiki/new.html',      [WikiController::class, 'adminCreate'])->name('wiki.admin.create');
    Route::get('/wiki:{slug}.html',    [WikiController::class, 'adminEdit'])->name('wiki.admin.edit');
    Route::post('/wiki:{slug}.html',   [WikiController::class, 'adminSave'])->name('wiki.admin.save');
    Route::delete('/wiki:{slug}.html', [WikiController::class, 'adminDelete'])->name('wiki.admin.delete');
});

