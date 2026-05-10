<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\GlitchController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\SpriteController;

// Home
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
});

// Auth
Route::get('/auth/discord/redirect', [AuthController::class, 'redirect'])->name('auth.discord');
Route::get('/auth/discord/callback', [AuthController::class, 'callback']);
Route::match(['get', 'post'], '/login.html', [AuthController::class, 'handleLogin']);
Route::get('/logout.html', [AuthController::class, 'logout'])->name('logout');

// Glitches
Route::get('/gallery.html', [GlitchController::class, 'index']);
Route::get('/g:{form}:{id}.html', [GlitchController::class, 'show']);
Route::get('/create.html', [GlitchController::class, 'create']);
Route::post('/upload.html', [GlitchController::class, 'store']);
Route::get('/d:{id}.html', [GlitchController::class, 'download']);

// Sprites
Route::get('/front:{id}.png', [SpriteController::class, 'front']);
Route::get('/back:{id}.png', [SpriteController::class, 'back']);

// Likes
Route::middleware('throttle:30,1')->group(function () {
    Route::post('/like:{id}.html', [LikeController::class, 'like']);
    Route::post('/rLike:{id}.html', [LikeController::class, 'removeLike']);
    Route::post('/dislike:{id}.html', [LikeController::class, 'dislike']);
    Route::post('/rDislike:{id}.html', [LikeController::class, 'removeDislike']);
    Route::post('/uLike:{id}.html', [LikeController::class, 'likeUser']);
    Route::post('/uRLike:{id}.html', [LikeController::class, 'removeUserLike']);
});

// Users
Route::get('/u:{username}.html', [UserController::class, 'profile']);
Route::post('/u:{username}.html', [UserController::class, 'handleProfilePost']);
Route::get('/trainercard:{username}.html', [UserController::class, 'trainerCard']);

// Static pages
Route::get('/faq.html', function () {
    return view('faq');
});
Route::get('/gacha.html', function () {
    return view('gacha');
});

Route::get('/galleryCore.html', [GlitchController::class, 'galleryCore']);
Route::get('/gallerySmitty.html', [GlitchController::class, 'gallerySmitty']);
Route::get('/gallerySmittyForm.html', [GlitchController::class, 'gallerySmittyForm']);

Route::get('/cFront:{name}.png', [SpriteController::class, 'coreFront']);
Route::get('/cBack:{name}.png', [SpriteController::class, 'coreBack']);

Route::get('/core:{form}.html', [GlitchController::class, 'coreMon']);
Route::get('/smitty:{form}.html', [GlitchController::class, 'smittyMon']);
Route::get('/smittyForm:{form}.html', [GlitchController::class, 'smittyFormMon']);

// Bot
Route::post('/discord/interactions', [App\Http\Controllers\DiscordInteractionController::class, 'handle'])->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);

// ── Wiki ──────────────────────────────────────────────────────────
use App\Http\Controllers\WikiController;

Route::get('/wiki.html',               [WikiController::class, 'index'])->name('wiki.index');
Route::get('/wiki:items.html',         [WikiController::class, 'items'])->name('wiki.items');
Route::get('/wiki:alt-builds.html',    [WikiController::class, 'altBuilds'])->name('wiki.altbuilds');
Route::get('/wiki:changelog.html',     [WikiController::class, 'changelog'])->name('wiki.changelog');
Route::get('/wiki:{slug}.html',        [WikiController::class, 'show'])->name('wiki.show');
Route::get('/wiki-search.json',        [\App\Http\Controllers\WikiSearchController::class, 'search'])->name('wiki.search');

Route::middleware('auth')->group(function () {
    Route::get('/admin/wiki.html',           [WikiController::class, 'adminIndex'])->name('wiki.admin.index');
    Route::get('/admin/wiki/new.html',       [WikiController::class, 'adminNew'])->name('wiki.admin.new');
    Route::post('/admin/wiki/new.html',      [WikiController::class, 'adminCreate'])->name('wiki.admin.create');
    Route::get('/admin/wiki:{slug}.html',    [WikiController::class, 'adminEdit'])->name('wiki.admin.edit');
    Route::post('/admin/wiki:{slug}.html',   [WikiController::class, 'adminSave'])->name('wiki.admin.save');
    Route::delete('/admin/wiki:{slug}.html', [WikiController::class, 'adminDelete'])->name('wiki.admin.delete');
});
