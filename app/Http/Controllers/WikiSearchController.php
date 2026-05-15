<?php

namespace App\Http\Controllers;

use App\Models\WikiArticle;
use App\Models\WikiSection;
use App\Models\GameItem;
use App\Models\BuiltinForm;
use App\Models\Glitch;
use Illuminate\Http\Request;

class WikiSearchController extends Controller
{
    public function search(Request $request)
    {
        $q = trim($request->query('q', ''));

        if (strlen($q) < 2) {
            return response()->json(['results' => []]);
        }

        $results = [];

        // ── Special pages ─────────────────────────────────────────────
        $specialPages = [
            ['title' => 'Items Reference',  'url' => route('wiki.items'),      'subtitle' => 'Items & Shop',  'excerpt' => 'All in-game items by tier — Common, Great, Ultra, Rogue, Master, Omega.'],
            ['title' => 'Alt Builds',        'url' => route('wiki.altbuilds'),  'subtitle' => 'Champions',     'excerpt' => 'Alternate forms of Champion Signature Pokémon with unique types, abilities and stats.'],
            ['title' => 'Changelog',         'url' => route('wiki.changelog'),  'subtitle' => 'Meta',          'excerpt' => 'All PokéVoid releases, auto-generated from the game source repository.'],
        ];

        foreach ($specialPages as $page) {
            if (stripos($page['title'], $q) !== false || stripos($page['excerpt'], $q) !== false) {
                $results[] = [
                    'type'     => 'article',
                    'label'    => 'Wiki',
                    'title'    => $page['title'],
                    'subtitle' => $page['subtitle'],
                    'excerpt'  => $page['excerpt'],
                    'url'      => $page['url'],
                ];
            }
        }

        // ── Wiki Sections ─────────────────────────────────────────────
        // Split into words; require all words to appear in the section.
        // Score and rank so the best heading match surfaces first.
        $words = array_values(array_filter(explode(' ', $q), fn($w) => strlen($w) >= 2));
        if (empty($words)) {
            $words = [$q];
        }

        $sectionQuery = WikiSection::query();
        foreach ($words as $word) {
            $sectionQuery->where(function ($sub) use ($word) {
                $sub->where('heading', 'like', "%{$word}%")
                    ->orWhere('body', 'like', "%{$word}%");
            });
        }
        $sections = $sectionQuery->limit(30)->get();

        // Score each candidate and pick the top 5
        $scored = $sections
            ->map(fn($s) => ['section' => $s, 'score' => $s->score($words)])
            ->sortByDesc('score')
            ->take(5);

        // Deduplicate: only one result per article (the highest-scoring section)
        $seenArticles = [];
        foreach ($scored as $item) {
            $s = $item['section'];

            // Build a display title: "Article Title → Section Heading"
            $isTopLevel = strtolower(trim($s->heading)) === strtolower(trim($s->article_title));
            $displayTitle = $isTopLevel
                ? $s->article_title
                : $s->article_title . ' → ' . $s->heading;

            // Excerpt from the section body
            $excerpt = $this->excerptFromBody($s->body, $words);

            $url = route('wiki.show', $s->article_slug) . '#' . $s->anchor;

            $key = $s->article_slug . '#' . $s->anchor;
            if (isset($seenArticles[$key])) continue;
            $seenArticles[$key] = true;

            $results[] = [
                'type'     => 'article',
                'label'    => 'Wiki',
                'title'    => $displayTitle,
                'subtitle' => $s->article_category,
                'excerpt'  => $excerpt,
                'url'      => $url,
            ];
        }

        // Fallback: if sections yielded nothing, search article titles directly
        if (empty($seenArticles)) {
            $articleQuery = WikiArticle::query();
            foreach ($words as $word) {
                $articleQuery->where(function ($sub) use ($word) {
                    $sub->where('title', 'like', "%{$word}%")
                        ->orWhere('content', 'like', "%{$word}%");
                });
            }
            $articles = $articleQuery
                ->orderByRaw("CASE WHEN title LIKE ? THEN 0 ELSE 1 END", ["%{$q}%"])
                ->limit(5)
                ->get(['slug', 'title', 'category', 'content']);

            foreach ($articles as $article) {
                $results[] = [
                    'type'     => 'article',
                    'label'    => 'Wiki',
                    'title'    => $article->title,
                    'subtitle' => $article->category,
                    'excerpt'  => $this->excerpt($article->content, $q),
                    'url'      => route('wiki.show', $article->slug),
                ];
            }
        }

        // ── Items ─────────────────────────────────────────────────────
        $items = GameItem::where('name', 'like', "%{$q}%")
            ->orWhere('description', 'like', "%{$q}%")
            ->orderByRaw("CASE WHEN name LIKE ? THEN 0 ELSE 1 END", ["%{$q}%"])
            ->limit(5)
            ->get(['name', 'description', 'tier']);

        foreach ($items as $item) {
            $results[] = [
                'type'     => 'item',
                'label'    => 'Item',
                'title'    => $item->name,
                'subtitle' => ucfirst(strtolower($item->tier)) . ' tier',
                'excerpt'  => $item->description ? $this->truncate($item->description, 80) : null,
                'url'      => route('wiki.items') . '#' . \Illuminate\Support\Str::slug($item->name),
            ];
        }

        // ── Builtin Forms (Core, Smitty, Smitty Forms) ────────────────
        $forms = BuiltinForm::where('name', 'like', "%{$q}%")
            ->orWhere('og_mon', 'like', "%{$q}%")
            ->orderByRaw("CASE WHEN name LIKE ? THEN 0 ELSE 1 END", ["%{$q}%"])
            ->limit(5)
            ->get(['name', 'form_type', 'og_mon']);

        foreach ($forms as $form) {
            $typeLabels = ['core' => 'Core Glitch', 'smitty' => 'SMITTY', 'smitty_form' => 'SMITTY Form'];
            $routes     = [
                'core'        => '/core:' . urlencode($form->name) . '.html',
                'smitty'      => '/smitty:' . urlencode($form->name) . '.html',
                'smitty_form' => '/smittyForm:' . urlencode($form->name) . '.html',
            ];
            $results[] = [
                'type'     => 'form',
                'label'    => $typeLabels[$form->form_type] ?? 'Form',
                'title'    => $form->name,
                'subtitle' => $form->og_mon ? 'Base: ' . ucwords(str_replace('-', ' ', $form->og_mon)) : null,
                'excerpt'  => null,
                'url'      => $routes[$form->form_type] ?? '#',
            ];
        }

        // ── Mod Glitch Forms ──────────────────────────────────────────
        $glitches = Glitch::where('name', 'like', "%{$q}%")
            ->limit(4)
            ->get(['id', 'name']);

        foreach ($glitches as $glitch) {
            $slug = str_replace(' ', '', $glitch->name);
            $results[] = [
                'type'     => 'glitch',
                'label'    => 'Mod Glitch',
                'title'    => $glitch->name,
                'subtitle' => null,
                'excerpt'  => null,
                'url'      => '/g:' . urlencode($slug) . ':' . $glitch->id . '.html',
            ];
        }

        // ── Alt Builds ────────────────────────────────────────────────
        $altBuilds = \App\Models\AltBuild::where('name', 'like', "%{$q}%")
            ->orWhere('species', 'like', "%{$q}%")
            ->orderByRaw("CASE WHEN name LIKE ? THEN 0 ELSE 1 END", ["%{$q}%"])
            ->limit(4)
            ->get(['build_id', 'name', 'species', 'champion', 'type1', 'type2']);

        $championLabels = \App\Models\AltBuild::championLabel();
        foreach ($altBuilds as $build) {
            $types = collect([$build->type1, $build->type2])->filter()->join(' / ');
            $results[] = [
                'type'     => 'altbuild',
                'label'    => 'Alt Build',
                'title'    => "{$build->species} — {$build->name}",
                'subtitle' => ($championLabels[$build->champion] ?? ucfirst($build->champion ?? '')) . ($types ? " · {$types}" : ''),
                'excerpt'  => null,
                'url'      => route('wiki.altbuilds') . '#build-' . $build->build_id,
            ];
        }

        return response()->json(['query' => $q, 'results' => $results]);
    }

    /**
     * Build an excerpt from a pre-stripped section body, highlighting the
     * first query word found. Falls back to the start of the body.
     */
    private function excerptFromBody(string $body, array $words, int $length = 120): ?string
    {
        $pos = false;
        foreach ($words as $word) {
            $p = stripos($body, $word);
            if ($p !== false) {
                $pos = $p;
                break;
            }
        }

        if ($pos === false) {
            return $this->truncate($body, $length);
        }

        $start   = max(0, $pos - 30);
        $snippet = substr($body, $start, $length);
        if ($start > 0) $snippet = '…' . ltrim($snippet);
        if ($start + $length < strlen($body)) $snippet .= '…';

        return $snippet;
    }

    private function excerpt(string $text, string $q, int $length = 100): ?string
    {
        // Strip markdown
        $plain = preg_replace('/[#*`\[\]_>~]/u', '', $text);
        $plain = preg_replace('/\|.*?\|/u', '', $plain);
        $plain = preg_replace('/\s+/', ' ', $plain);

        $pos = stripos($plain, $q);
        if ($pos === false) return $this->truncate($plain, $length);

        $start = max(0, $pos - 30);
        $snippet = substr($plain, $start, $length);
        if ($start > 0) $snippet = '…' . ltrim($snippet);
        if ($start + $length < strlen($plain)) $snippet .= '…';

        return $snippet;
    }

    private function truncate(string $text, int $length): string
    {
        return strlen($text) > $length ? substr($text, 0, $length) . '…' : $text;
    }
}
