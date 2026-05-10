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
        ]);
        $environment->addExtension(new \League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension());
        $environment->addExtension(new \League\CommonMark\Extension\Table\TableExtension());
        $environment->addExtension(new \League\CommonMark\Extension\Strikethrough\StrikethroughExtension());

        $converter = new \League\CommonMark\MarkdownConverter($environment);
        return $converter->convert($content)->getContent();
    }

    private function isAdmin(): bool
    {
        $user = auth()->user();
        return $user && $user->discord_id === self::ADMIN_DISCORD_ID;
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

        // Sidebar: all articles grouped
        $categoryOrder = WikiArticle::categoryOrder();
        $allArticles = WikiArticle::orderBy('order')->orderBy('title')->get();
        $grouped = collect($categoryOrder)
            ->mapWithKeys(fn($cat) => [
                $cat => $allArticles->where('category', $cat)->values()
            ])
            ->filter(fn($group) => $group->isNotEmpty());

        return view('wiki-article', compact('article', 'html', 'grouped'));
    }

    // ── Admin routes ───────────────────────────────────────────────

    public function adminIndex()
    {
        abort_unless($this->isAdmin(), 403);
        $articles = WikiArticle::orderBy('category')->orderBy('order')->get();
        return view('admin.wiki-index', compact('articles'));
    }

    public function adminEdit(string $slug)
    {
        abort_unless($this->isAdmin(), 403);
        $article = WikiArticle::where('slug', $slug)->firstOrFail();
        $categories = WikiArticle::categoryOrder();
        return view('admin.wiki-edit', compact('article', 'categories'));
    }

    public function adminSave(Request $request, string $slug)
    {
        abort_unless($this->isAdmin(), 403);
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
        abort_unless($this->isAdmin(), 403);
        $categories = WikiArticle::categoryOrder();
        return view('admin.wiki-new', compact('categories'));
    }

    public function adminCreate(Request $request)
    {
        abort_unless($this->isAdmin(), 403);

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
        abort_unless($this->isAdmin(), 403);
        WikiArticle::where('slug', $slug)->firstOrFail()->delete();
        return redirect()->route('wiki.admin.index')
            ->with('success', 'Article deleted.');
    }
}
