<?php

namespace App\Http\Controllers;

use App\Models\Rival;
use App\Models\WikiArticle;

class RivalController extends Controller
{
    /**
     * Shared sidebar data for wiki layout.
     */
    private function sidebarGrouped(): \Illuminate\Support\Collection
    {
        $categoryOrder = WikiArticle::categoryOrder();
        $articles = WikiArticle::orderBy('order')->orderBy('title')->get();

        return collect($categoryOrder)
            ->mapWithKeys(fn($cat) => [
                $cat => $articles->where('category', $cat)->values()
            ])
            ->filter(fn($group) => $group->isNotEmpty());
    }

    /**
     * /rivals.html — full roster grouped by role category.
     */
    public function index()
    {
        $rivals = Rival::orderBy('rival_id')->get();

        // Group into display categories
        $grouped = [
            'Gym Leaders'       => $rivals->filter(fn($r) => str_contains($r->role, 'Gym Leader')),
            'Elite Four'        => $rivals->filter(fn($r) => str_contains($r->role, 'Elite Four') && !str_contains($r->role, 'Gym')),
            'Champions'         => $rivals->filter(fn($r) => str_contains($r->role, 'Champion')),
            'Team Bosses'       => $rivals->filter(fn($r) =>
                str_contains($r->role, 'Boss') || str_contains($r->role, 'Foundation')
            ),
        ];

        // Remove empties
        $grouped = array_filter($grouped, fn($g) => $g->isNotEmpty());

        return view('wiki-rivals', [
            'grouped'        => $grouped,
            'sidebarGrouped' => $this->sidebarGrouped(),
        ]);
    }

    /**
     * /rival:{slug}.html — per-rival detail page.
     */
    public function show(string $slug)
    {
        $rival = Rival::where('slug', $slug)->firstOrFail();

        return view('wiki-rival', [
            'rival'          => $rival,
            'sidebarGrouped' => $this->sidebarGrouped(),
        ]);
    }
}
