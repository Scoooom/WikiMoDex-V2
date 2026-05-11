@extends('layouts.app')

@section('title', $build->title . ' — Community Build')

@section('content')
<div class="wiki-page">

    <div class="build-show-header">
        <div>
            <a href="/builds.html" class="build-back-link">← All Builds</a>
            <h1 class="build-show-title">{{ $build->title }}</h1>
            <div class="build-show-meta">
                <img src="{{ $build->user->getAvatarURL() }}" class="build-show-avatar" alt="">
                <span>by <strong>{{ $build->user->username }}</strong></span>
                <span class="build-show-date">{{ $build->created_at->diffForHumans() }}</span>
            </div>
        </div>
        <div class="build-show-actions">
            {{-- Vote button - shown/updated by JS --}}
            <button
                class="build-vote-btn {{ $voted ? 'voted' : '' }}"
                id="voteBtn"
                data-slug="{{ $build->slug }}"
                data-voted="{{ $voted ? '1' : '0' }}"
            >
                <span class="build-vote-icon">▲</span>
                <span class="build-vote-count" id="voteCount">{{ $build->votes }}</span>
            </button>

            {{-- Share --}}
            <button class="build-share-btn" onclick="copyBuildLink()" title="Copy link">🔗 Share</button>

            {{-- Owner / admin delete --}}
            <span id="build-delete-slot"></span>
        </div>
    </div>

    @if($build->description)
    <div class="build-show-desc">{{ $build->description }}</div>
    @endif

    {{-- Team --}}
    <div class="build-team">
        @foreach($build->team as $i => $slot)
        @if(!empty($slot['species']))
        <div class="build-slot">
            <div class="build-slot-header">
                <div class="build-slot-sprite-wrap">
                    <img
                        src="/cFront:{{ $slot['species'] }}.png"
                        class="build-slot-sprite"
                        alt="{{ $slot['species'] }}"
                        onerror="this.src='/avatars/default.svg'"
                    >
                </div>
                <div class="build-slot-info">
                    <div class="build-slot-species">
                        {{ $slot['species'] }}
                        @if(!empty($slot['override_type1']) || !empty($slot['override_type2']))
                            <span class="build-slot-types">
                                @if(!empty($slot['override_type1']))
                                    <span class="build-type-badge">{{ $slot['override_type1'] }}</span>
                                @endif
                                @if(!empty($slot['override_type2']))
                                    <span class="build-type-badge">{{ $slot['override_type2'] }}</span>
                                @endif
                            </span>
                        @endif
                    </div>
                    @if(!empty($slot['ability']))
                        <div class="build-slot-ability">Ability: <span>{{ $slot['ability'] }}</span></div>
                    @endif
                    @if(!empty($slot['passive_ability']))
                        <div class="build-slot-ability">Passive: <span>{{ $slot['passive_ability'] }}</span></div>
                    @endif
                    @if(!empty($slot['nature']))
                        <div class="build-slot-nature">Nature: <span>{{ $slot['nature'] }}</span></div>
                    @endif
                </div>
            </div>

            @php
                $slotStat   = $slotStats[$i] ?? null;
                $statValues = $slotStat['stats'] ?? null;
                $statError  = $slotStat['error'] ?? null;
                $statSource = $slotStat['source'] ?? null;
                // DEBUG — remove after fixing
                \Illuminate\Support\Facades\Log::info('SlotStat debug', ['i' => $i, 'species' => $slot['species'] ?? null, 'slotStat' => $slotStat]);
                $statFocus  = '';
                if ($statSource === 'alt_build') {
                    $ab = \App\Models\AltBuild::where('name', $slot['species'])->first();
                    $statFocus = $ab->stat_focus ?? '';
                }
                $displayStats = $statValues
                    ? \App\Services\StatService::formatForDisplay($statValues, $slot['items'] ?? [], $statFocus)
                    : null;
            @endphp
            <div class="build-slot-body">
                {{-- Moves --}}
                @if(!empty(array_filter($slot['moves'] ?? [])))
                <div class="build-slot-section">
                    <div class="build-slot-section-label">Moves</div>
                    <div class="build-slot-moves">
                        @foreach(array_filter($slot['moves'] ?? []) as $move)
                            <span class="build-move-pill">{{ $move }}</span>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Items --}}
                @if(!empty($slot['items']))
                <div class="build-slot-section">
                    <div class="build-slot-section-label">Held Items</div>
                    <div class="build-slot-items">
                        @foreach($slot['items'] as $item)
                        @if(!empty($item['name']))
                        <div class="build-item-chip">
                            @php
                                $gameItem = $items->get($item['key'] ?? '');
                                $tierColor = $gameItem ? \App\Models\GameItem::TIER_COLORS[$gameItem->tier] ?? 'var(--dim)' : 'var(--dim)';
                                $params = $item['params'] ?? [];
                            @endphp
                            <span class="build-item-dot" style="background:{{ $tierColor }}"></span>
                            <span class="build-item-name">{{ $item['name'] }}</span>
                            @if(($item['stack'] ?? 1) > 1)
                                <span class="build-item-stack">×{{ $item['stack'] }}</span>
                            @endif
                            @if(!empty($params))
                                <span class="build-item-params-summary">
                                    @if(!empty($params['stat1']) || !empty($params['stat2']))
                                        ({{ $params['stat1'] ?? '?' }}{{ !empty($params['stat2']) ? ' ↔ ' . $params['stat2'] : '' }})
                                    @elseif(!empty($params['type1']) || !empty($params['type2']))
                                        ({{ $params['type1'] ?? 'Default' }}{{ !empty($params['type2']) ? ' / ' . $params['type2'] : '' }})
                                    @endif
                                </span>
                            @endif
                        </div>
                        @endif
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Notes --}}
                @if(!empty($slot['notes']))
                <div class="build-slot-notes">{{ $slot['notes'] }}</div>
                @endif
            </div>
        </div>
        @endif
        @endforeach
    </div>

</div>

<script>
(function() {
    const voteBtn   = document.getElementById('voteBtn');
    const voteCount = document.getElementById('voteCount');
    const slug      = voteBtn?.dataset.slug;
    let   voted     = voteBtn?.dataset.voted === '1';
    let   pending   = false;

    // Render delete button if owner/admin
    fetch('/me.json', { credentials: 'same-origin' })
        .then(r => r.json())
        .then(me => {
            if (!me.authed) {
                // Disable vote btn for guests
                if (voteBtn) {
                    voteBtn.title = 'Login to vote';
                    voteBtn.onclick = () => { window.location = '/login.html'; };
                }
                return;
            }
            const isOwner = me.username === '{{ $build->user->username }}';
            if (isOwner || me.isAdmin) {
                const slot = document.getElementById('build-delete-slot');
                // Edit button
                const editLink = document.createElement('a');
                editLink.href = '/build/{{ $build->slug }}/edit.html';
                editLink.className = 'build-edit-btn';
                editLink.textContent = '✏ Edit';
                slot.appendChild(editLink);
                // Delete form
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '/build/{{ $build->slug }}.html';
                form.style.display = 'inline';
                form.innerHTML = `
                    <input type="hidden" name="_token" value="${getCsrfToken()}">
                    <input type="hidden" name="_method" value="DELETE">
                    <button type="submit" class="build-delete-btn"
                        onclick="return confirm('Delete this build?')">🗑 Delete</button>`;
                slot.appendChild(form);
            }
        }).catch(() => {});

    if (voteBtn) {
        voteBtn.addEventListener('click', function () {
            if (pending) return;
            pending = true;
            voteBtn.disabled = true;

            fetch(`/build/${slug}/vote.html`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
            })
            .then(r => {
                if (r.status === 403) { window.location = '/login.html'; return null; }
                return r.json();
            })
            .then(data => {
                if (!data) return;
                voted = data.voted;
                voteCount.textContent = data.votes;
                voteBtn.classList.toggle('voted', voted);
            })
            .finally(() => { pending = false; voteBtn.disabled = false; });
        });
    }

    function getCsrfToken() {
        const m = document.cookie.match(/XSRF-TOKEN=([^;]+)/);
        return m ? decodeURIComponent(m[1]) : '';
    }
})();

function copyBuildLink() {
    navigator.clipboard.writeText(window.location.href).then(() => {
        const btn = document.querySelector('.build-share-btn');
        const orig = btn.textContent;
        btn.textContent = '✓ Copied!';
        setTimeout(() => btn.textContent = orig, 1800);
    });
}
</script>
