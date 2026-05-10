<?php

namespace App\Http\Controllers;

use App\Models\WikiArticle;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class WikiController extends Controller
{
    private const ADMIN_DISCORD_ID = '356260100064673814'; // scooom's Discord user ID

    private function markdown(string $content): string
    {
        $environment = new \League\CommonMark\Environment\Environment([
            'html_input'         => 'strip',
            'allow_unsafe_links' => false,
            'heading_permalink'  => [
                'html_class'        => 'wiki-heading-anchor',
                'id_prefix'         => '',
                'fragment_prefix'   => '',
                'insert'            => 'after',
                'min_heading_level' => 1,
                'max_heading_level' => 4,
                'symbol'            => '#',
                'title'             => 'Link to this section',
            ],
        ]);
        $environment->addExtension(new \League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension());
        $environment->addExtension(new \League\CommonMark\Extension\Table\TableExtension());
        $environment->addExtension(new \League\CommonMark\Extension\Strikethrough\StrikethroughExtension());
        $environment->addExtension(new \League\CommonMark\Extension\HeadingPermalink\HeadingPermalinkExtension());

        $converter = new \League\CommonMark\MarkdownConverter($environment);
        return $converter->convert($content)->getContent();
    }

    private function isAdmin(): bool
    {
        $user = auth()->user();
        return $user && $user->user_id === self::ADMIN_DISCORD_ID;
    }

    // ── Public routes ──────────────────────────────────────────────

    public function index()
    {
        $categoryOrder = WikiArticle::categoryOrder();
        $articles = WikiArticle::orderBy('order')->orderBy('title')->get();

        $grouped = collect($categoryOrder)
            ->mapWithKeys(fn($cat) => [
                $cat => $articles->where('category', $cat)->values()
            ])
            ->filter(fn($group) => $group->isNotEmpty());

        // Append any categories not in the predefined order
        $remaining = $articles
            ->whereNotIn('category', $categoryOrder)
            ->groupBy('category');
        foreach ($remaining as $cat => $group) {
            $grouped[$cat] = $group->values();
        }

        return view('wiki', compact('grouped'));
    }

    public function show(string $slug)
    {
        $article = WikiArticle::where('slug', $slug)->firstOrFail();
        $html = $this->markdown($article->content);

        $categoryOrder = WikiArticle::categoryOrder();
        $allArticles = WikiArticle::orderBy('order')->orderBy('title')->get();
        $grouped = collect($categoryOrder)
            ->mapWithKeys(fn($cat) => [
                $cat => $allArticles->where('category', $cat)->values()
            ])
            ->filter(fn($group) => $group->isNotEmpty());

        // Cross-links to gallery pages based on article slug
        $galleryMap = [
            'champions-overview' => [
                ['url' => '/wiki:champions-index.html', 'icon' => '🏆', 'label' => 'All Champions',     'sub' => 'Full champion roster'],
                ['url' => '/wiki:apollo.html',          'icon' => '☀️', 'label' => 'Apollo / Diana',    'sub' => 'Champions of Sun & Moon'],
                ['url' => '/wiki:brock.html',           'icon' => '🪨', 'label' => 'Brock',             'sub' => 'Rock / Ground'],
                ['url' => '/wiki:misty.html',           'icon' => '💧', 'label' => 'Misty',             'sub' => 'Water'],
            ],
                ['url' => '/gallery.html',     'icon' => '👾', 'label' => 'Mod Glitch Forms',  'sub' => 'Community-made forms'],
                ['url' => '/galleryCore.html',  'icon' => '✨', 'label' => 'Core Glitches',     'sub' => 'Official glitch forms'],
            ],
            'smitty-forms' => [
                ['url' => '/gallerySmitty.html',     'icon' => '⚡', 'label' => 'SMITTY Pokémon',   'sub' => 'All SMITTY forms'],
                ['url' => '/gallerySmittyForm.html', 'icon' => '🌀', 'label' => 'SMITTY Forms',      'sub' => 'SMITTY alt forms'],
            ],
            'rivals' => [
                ['url' => '/gallery.html', 'icon' => '👾', 'label' => 'Mod Glitch Forms', 'sub' => 'Browse uploaded forms'],
            ],
            'items-overview' => [
                ['url' => '/wiki:items.html', 'icon' => '🎒', 'label' => 'Items Reference', 'sub' => 'Full item list by tier'],
            ],
            'eggs-gacha' => [
                ['url' => '/gacha.html', 'icon' => '📅', 'label' => 'Gacha Calendar', 'sub' => 'Today\'s legendary & Pokérus'],
            ],
        ];

        $galleryLinks = $galleryMap[$slug] ?? [];

        return view('wiki-article', compact('article', 'html', 'grouped', 'galleryLinks'));
    }

    // ── Admin routes ───────────────────────────────────────────────

    public function adminIndex()
    {
        abort_unless($this->isAdmin(), 404);
        $articles = WikiArticle::orderBy('category')->orderBy('order')->get();
        return view('admin.wiki-index', compact('articles'));
    }

    public function adminEdit(string $slug)
    {
        abort_unless($this->isAdmin(), 404);
        $article = WikiArticle::where('slug', $slug)->firstOrFail();
        $categories = WikiArticle::categoryOrder();
        return view('admin.wiki-edit', compact('article', 'categories'));
    }

    public function adminSave(Request $request, string $slug)
    {
        abort_unless($this->isAdmin(), 404);
        $article = WikiArticle::where('slug', $slug)->firstOrFail();

        $data = $request->validate([
            'title'    => 'required|string|max:255',
            'content'  => 'required|string',
            'category' => 'required|string|max:100',
            'order'    => 'required|integer|min:0',
        ]);

        // Regenerate slug if title changed
        if ($data['title'] !== $article->title) {
            $newSlug = Str::slug($data['title']);
            // Ensure uniqueness
            $base = $newSlug;
            $i = 1;
            while (WikiArticle::where('slug', $newSlug)->where('id', '!=', $article->id)->exists()) {
                $newSlug = $base . '-' . $i++;
            }
            $data['slug'] = $newSlug;
        }

        $article->update($data);
        return redirect()->route('wiki.admin.edit', $article->slug)
            ->with('success', 'Article saved.');
    }

    public function adminNew()
    {
        abort_unless($this->isAdmin(), 404);
        $categories = WikiArticle::categoryOrder();
        return view('admin.wiki-new', compact('categories'));
    }

    public function adminCreate(Request $request)
    {
        abort_unless($this->isAdmin(), 404);

        $data = $request->validate([
            'title'    => 'required|string|max:255',
            'content'  => 'required|string',
            'category' => 'required|string|max:100',
            'order'    => 'required|integer|min:0',
        ]);

        $slug = Str::slug($data['title']);
        $base = $slug;
        $i = 1;
        while (WikiArticle::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $i++;
        }
        $data['slug'] = $slug;

        $article = WikiArticle::create($data);
        return redirect()->route('wiki.show', $article->slug)
            ->with('success', 'Article created.');
    }

    public function adminDelete(Request $request, string $slug)
    {
        abort_unless($this->isAdmin(), 404);
        WikiArticle::where('slug', $slug)->firstOrFail()->delete();
        return redirect()->route('wiki.admin.index')
            ->with('success', 'Article deleted.');
    }

    public function changelog()
    {
        $entries = \App\Models\ChangelogEntry::orderBy('committed_at', 'desc')
            ->get()
            ->unique('title')
            ->values();
        return view('wiki-changelog', compact('entries'));
    }

    public function items()
    {
        $byTier = \App\Models\GameItem::orderBy('name')
            ->get()
            ->groupBy('tier');

        $categoryOrder = WikiArticle::categoryOrder();
        $allArticles = WikiArticle::orderBy('order')->orderBy('title')->get();
        $grouped = collect($categoryOrder)
            ->mapWithKeys(fn($cat) => [
                $cat => $allArticles->where('category', $cat)->values()
            ])
            ->filter(fn($group) => $group->isNotEmpty());

        return view('wiki-items', compact('byTier', 'grouped'));
    }
}
