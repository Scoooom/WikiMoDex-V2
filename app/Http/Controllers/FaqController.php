<?php

namespace App\Http\Controllers;

use App\Models\FaqEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;

class FaqController extends Controller
{
    private function availableGroups(): array
    {
        // Existing groups from DB, plus defaults for new installs
        $fromDb = FaqEntry::distinct()->orderBy('group_order')->pluck('group')->toArray();

        $defaults = [
            'Getting Started',
            'Champions & Skill Trees',
            'Glitch & Smitty Forms',
            'Eggs & Gacha',
            'Omega System & Progression',
            'Saving & Technical',
        ];

        return array_unique(array_merge($defaults, $fromDb));
    }

    private function purgeFaqCache(): void
    {
        Artisan::call('cf:purge', [
            '--url' => [
                rtrim(config('services.cloudflare.base_url'), '/') . '/faq.html',
            ],
        ]);
    }

    // ── Admin routes ───────────────────────────────────────────────────────

    public function index()
    {
        $grouped = FaqEntry::grouped();
        return view('admin.faq-index', compact('grouped'));
    }

    public function create()
    {
        $groups = $this->availableGroups();
        return view('admin.faq-new', compact('groups'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'question'        => 'required|string|max:500',
            'answer_html'     => 'required|string',
            'answer_plain'    => 'required|string',
            'group'           => 'required|string|max:100',
            'group_order'     => 'required|integer|min:0',
            'order'           => 'required|integer|min:0',
            'open_by_default' => 'nullable|boolean',
        ]);

        $data['open_by_default'] = $request->boolean('open_by_default');
        $data['slug'] = $this->uniqueSlug(FaqEntry::slugFor($data['question']));

        FaqEntry::create($data);
        $this->purgeFaqCache();

        return redirect()->route('faq.admin.index')
            ->with('success', 'FAQ entry created.');
    }

    public function edit(string $slug)
    {
        $entry  = FaqEntry::where('slug', $slug)->firstOrFail();
        $groups = $this->availableGroups();
        return view('admin.faq-edit', compact('entry', 'groups'));
    }

    public function update(Request $request, string $slug)
    {
        $entry = FaqEntry::where('slug', $slug)->firstOrFail();

        $data = $request->validate([
            'question'        => 'required|string|max:500',
            'answer_html'     => 'required|string',
            'answer_plain'    => 'required|string',
            'group'           => 'required|string|max:100',
            'group_order'     => 'required|integer|min:0',
            'order'           => 'required|integer|min:0',
            'open_by_default' => 'nullable|boolean',
        ]);

        $data['open_by_default'] = $request->boolean('open_by_default');

        // Regenerate slug if question changed
        if ($data['question'] !== $entry->question) {
            $data['slug'] = $this->uniqueSlug(
                FaqEntry::slugFor($data['question']),
                $entry->id
            );
        }

        $entry->update($data);
        $this->purgeFaqCache();

        return redirect()->route('faq.admin.edit', $entry->slug)
            ->with('success', 'FAQ entry saved.');
    }

    public function destroy(string $slug)
    {
        FaqEntry::where('slug', $slug)->firstOrFail()->delete();
        $this->purgeFaqCache();

        return redirect()->route('faq.admin.index')
            ->with('success', 'FAQ entry deleted.');
    }

    // ── Helpers ────────────────────────────────────────────────────────────

    private function uniqueSlug(string $base, ?int $excludeId = null): string
    {
        $slug = $base;
        $i    = 1;
        while (
            FaqEntry::where('slug', $slug)
                ->when($excludeId, fn($q) => $q->where('id', '!=', $excludeId))
                ->exists()
        ) {
            $slug = $base . '-' . $i++;
        }
        return $slug;
    }
}
