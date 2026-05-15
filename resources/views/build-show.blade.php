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

            {{-- Build ID for /build Discord command --}}
            <span class="build-id-label" title="Use this ID with the /build Discord command">
                ID: <code class="build-id-code" onclick="navigator.clipboard.writeText('{{ $build->slug }}').then(() => this.classList.add('copied'))" title="Click to copy">{{ $build->slug }}</code>
            </span>

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
        @php
            $slotStat   = $slotStats[$i] ?? null;
            $statValues    = $slotStat['stats'] ?? null;
            $effectiveVals = $slotStat['effective'] ?? null;
            $statError     = $slotStat['error'] ?? null;
            $statSource = $slotStat['source'] ?? null;
            $slotTypes   = $slotStat['types'] ?? ['type1'=>null,'type2'=>null,'type1_name'=>null,'type2_name'=>null];
            $slotPalette  = $slotStat['palette'] ?? [];
                $slotGlitchId = $slotStat['glitch_id'] ?? null;
            $statFocus  = '';
            if ($statSource === 'alt_build') {
                $ab = \App\Models\AltBuild::where('name', $slot['species'])->first();
                $statFocus = $ab->stat_focus ?? '';
            }
            $displayStats = $statValues
                ? \App\Services\StatService::formatForDisplay($statValues, $slot['items'] ?? [], $statFocus, $slot['nature'] ?? '')
                : null;
        @endphp
        <div class="build-slot">
            <div class="build-slot-header">
                <div class="build-slot-sprite-wrap">
                    @if(!empty($slot['dex_number']))
                        @php
                            // Some pokemon have type-suffixed sprites (Arceus=493, Silvally=773)
                            $typeSuffixDex = [493, 773];
                            $spriteType = in_array((int)$slot['dex_number'], $typeSuffixDex)
                                ? strtolower($slotTypes['type1_name'] ?? 'normal')
                                : null;
                        @endphp
                        <canvas class="build-slot-sprite build-slot-sprite--canvas"
                                data-dex="{{ $slot['dex_number'] }}"
                                data-palette="{{ json_encode($slotPalette) }}"
                                data-shiny="{{ !empty($slot['shiny']) ? '1' : '0' }}"
                                data-variant="{{ $slot['variant'] ?? 0 }}"
                                data-form-key="{{ $slot['form_key'] ?? '' }}"
                                @if($spriteType) data-type="{{ $spriteType }}" @endif
                                width="80" height="80"></canvas>
                    @elseif($slotGlitchId)
                        <img
                            src="/front:{{ $slotGlitchId }}.png"
                            class="build-slot-sprite"
                            alt="{{ $slot['species'] }}"
                            onerror="this.src='/avatars/default.svg'"
                        >
                    @else
                        <img
                            src="/cFront:{{ $slot['species'] }}.png"
                            class="build-slot-sprite"
                            alt="{{ $slot['species'] }}"
                            onerror="this.src='/avatars/default.svg'"
                        >
                    @endif
                </div>
                <div class="build-slot-info">
                    <div class="build-slot-species">
                        {{ $slot['species'] }} Lv. {{ $slot['level'] }}
                        @if(isset($slot['gender']) && $slot['gender'] === 0) <span class="build-slot-gender build-slot-gender--male">♂</span>
                        @elseif(isset($slot['gender']) && $slot['gender'] === 1) <span class="build-slot-gender build-slot-gender--female">♀</span>
                        @endif
                    </div>
                    @if(!empty($slot['nickname']))
                    <div class="build-slot-nickname">&ldquo;{{ $slot['nickname'] }}&rdquo;</div>
                    @endif
                    @php
                        $pokeballNames = ['Poké Ball','Great Ball','Ultra Ball','Master Ball','Cherish Ball','Premier Ball','Friend Ball','Luxury Ball','Heal Ball','Quick Ball','Timer Ball','Dusk Ball','Nest Ball','Net Ball','Dive Ball','Fast Ball','Level Ball','Lure Ball','Moon Ball','Love Ball','Heavy Ball','Dream Ball'];
                        $biomeNames = ['Town','Plains','Grass','Tall Grass','Metro','Forest','Sea','Swamp','Beach','Lake','Pond','Mountain','Badlands','Cave','Desert','Ice Cave','Meadow','Power Plant','Volcano','Graveyard','Dojo','Factory','Ruins','Wasteland','Abyss','Space','Jungle','Laboratory'];
                    @endphp
                    <div class="build-slot-meta-badges">
                        @if(!empty($slot['shiny']))
                            <span class="build-slot-badge build-slot-badge--shiny">✨ Shiny</span>
                        @endif
                        @if(!empty($slot['pokerus']))
                            <span class="build-slot-badge build-slot-badge--pokerus">🦠 Pokérus</span>
                        @endif
                        @if(isset($slot['pokeball']) && isset($pokeballNames[$slot['pokeball']]))
                            <span class="build-slot-badge build-slot-badge--pokeball">{{ $pokeballNames[$slot['pokeball']] }}</span>
                        @endif
                        @if(isset($slot['met_biome']) && isset($biomeNames[$slot['met_biome']]))
                            <span class="build-slot-badge build-slot-badge--biome">Met: {{ $biomeNames[$slot['met_biome']] }}</span>
                        @endif
                    </div>
                    @if($slotTypes['type1'] !== null)
                    <div class="build-slot-types-row">
                        <span class="type-badge type-{{ $slotTypes['type1'] }}">{{ $slotTypes['type1_name'] }}</span>
                        @if($slotTypes['type2'] !== null)
                        <span class="type-badge type-{{ $slotTypes['type2'] }}">{{ $slotTypes['type2_name'] }}</span>
                        @endif
                    </div>
                    @endif
                    @if(!empty($slot['ability']))
                        <div class="build-slot-ability">Ability: <span>{{ $slot['ability'] }}</span></div>
                    @endif
                    @if(!empty($slot['passive_ability']))
                        <div class="build-slot-ability">Passive: <span>{{ $slot['passive_ability'] }}</span></div>
                    @else
                        <div class="build-slot-ability build-slot-ability--none">Passive: <span>None</span></div>
                    @endif
                    <div class="build-slot-nature">Nature: <span>{{ $slot['nature'] ?? 'Unknown' }}</span></div>
                </div>
            </div>

            <div class="build-slot-body">
                {{-- Moves --}}
                @if(!empty(array_filter($slot['moves'] ?? [])))
                <div class="build-slot-section">
                    <div class="build-slot-section-label build-slot-collapsible" onclick="toggleSection(this)">
                        Moves <span class="build-slot-collapse-icon">▾</span>
                    </div>
                    <div class="build-move-grid build-slot-collapsible-body" style="display:none">
                        @foreach(array_filter($slot['moves'] ?? []) as $move)
                            @php
                                $moveData = $moveCache->get($move);
                                $typeInt  = $moveData?->type ?? null;
                                $typeName = $moveData?->type_name ?? null;
                                if ($moveData?->is_dynamic_type) {
                                    $behaviour = $moveData->dynamic_type_behaviour;
                                    // Plate/Drive key -> type name mapping
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
                                    $typeNameMap = array_flip(\App\Services\StatService::TYPE_NAMES);
                                    switch ($behaviour) {
                                        case 'primary':
                                            $typeInt  = $slotTypes['type1'];
                                            $typeName = $slotTypes['type1_name'] ?? 'Dynamic';
                                            break;
                                        case 'secondary':
                                            $typeInt  = $slotTypes['type2'] ?? $slotTypes['type1'];
                                            $typeName = $slotTypes['type2_name'] ?? $slotTypes['type1_name'] ?? 'Dynamic';
                                            break;
                                        case 'form':
                                            // Check slot items for a plate/drive — use that type, else fall back to move's base type (Normal)
                                            $formType = null;
                                            foreach ($slot['items'] ?? [] as $slotItem) {
                                                $ik = $slotItem['key'] ?? '';
                                                if (isset($plateDriveTypes[$ik])) {
                                                    $formType = $plateDriveTypes[$ik];
                                                    break;
                                                }
                                            }
                                            if ($formType) {
                                                $typeInt  = $typeNameMap[$formType] ?? null;
                                                $typeName = $formType;
                                            }
                                            // else keep default stored type (Normal)
                                            break;
                                        case 'weather':
                                            $typeInt  = null;
                                            $typeName = 'Weather';
                                            break;
                                        case 'terrain':
                                            $typeInt  = null;
                                            $typeName = 'Terrain';
                                            break;
                                        case 'iv':
                                            $typeInt  = null;
                                            $typeName = 'IV-based';
                                            break;
                                        default:
                                            $typeInt  = null;
                                            $typeName = 'Dynamic';
                                    }
                                }
                            @endphp
                            <div class="build-move-card {{ $typeInt !== null ? 'type-bg-' . $typeInt : 'type-bg-none' }}">
                                <div class="build-move-card-name {{ $typeInt !== null ? 'type-text-' . $typeInt : '' }}">{{ $move }}</div>
                                <div class="build-move-card-grid">
                                    <div class="build-move-card-cell">
                                        <span class="build-move-card-stat-label">POWER</span>
                                        <span class="build-move-card-stat-value">{{ $moveData?->power ?? '—' }}</span>
                                    </div>
                                    <div class="build-move-card-cell">
                                        <span class="build-move-card-stat-label">ACC</span>
                                        <span class="build-move-card-stat-value">{{ $moveData?->accuracy ? $moveData->accuracy . '%' : '—' }}</span>
                                    </div>
                                    <div class="build-move-card-cell">
                                        <span class="build-move-card-stat-label">PP</span>
                                        <span class="build-move-card-stat-value">{{ $moveData?->pp ?? '—' }}</span>
                                    </div>
                                    <div class="build-move-card-cell">
                                        <span class="build-move-card-stat-label">TYPE</span>
                                        <span class="build-move-card-stat-value build-move-card-type-val">
                                            @if($typeName)
                                                <span class="type-badge {{ $typeInt !== null ? 'type-' . $typeInt : 'type-badge--dynamic' }}">{{ $typeName }}</span>
                                            @else
                                                —
                                            @endif
                                        </span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Items --}}
                <div class="build-slot-section">
                    <div class="build-slot-section-label build-slot-collapsible" onclick="toggleSection(this)">
                        Held Items <span class="build-slot-collapse-icon">▾</span>
                    </div>  
                    <div class="build-move-grid build-slot-collapsible-body" style="display:none">

                    <div class="build-slot-items">
                        @if(empty($slot['items']))
                          <div class="build-item-chip">None</div>
                        @else
                        @foreach($slot['items'] as $item)
                        @if(!empty($item['name']))
                        <div class="build-item-chip">
                            @php
                                $gameItem  = $items->get($item['key'] ?? '');
                                $tierColor = $gameItem ? \App\Models\GameItem::TIER_COLORS[$gameItem->tier] ?? 'var(--dim)' : 'var(--dim)';
                                $iconUrl   = $gameItem?->getIconUrl();
                                $params    = $item['params'] ?? [];
                            @endphp
                            @if($iconUrl)
                                <img src="{{ $iconUrl }}" class="build-item-icon" alt=""
                                     onerror="this.style.display='none';this.nextElementSibling.style.display=''">
                                <span class="build-item-dot" style="background:{{ $tierColor }};display:none"></span>
                            @else
                                <span class="build-item-dot" style="background:{{ $tierColor }}"></span>
                            @endif
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
                        @endif
                    </div></div>
                </div>

                {{-- Notes --}}
                  <div class="build-slot-section">
                    <div class="build-slot-section-label build-slot-collapsible" onclick="toggleSection(this)">
                        Notes <span class="build-slot-collapse-icon">▾</span>
                    </div>  
                    <div class="build-move-grid build-slot-collapsible-body" style="display:none">
                     <div class="build-slot-notes">{{ !empty($slot['notes']) ? $slot['notes'] : 'None' }}</div>
                   </div>
                 </div>
                {{-- Base Stats + Effective Stats --}}
                @if($displayStats)
                @php
                    $effectiveDisplay = $effectiveVals
                        ? \App\Services\StatService::formatEffectiveForDisplay($effectiveVals, $slot['items'] ?? [], $statFocus)
                        : null;
                    $level = (int)($slot['level'] ?? 0);
                @endphp

                {{-- Base Stats --}}
                <div class="build-slot-section">
                    <div class="build-slot-section-label build-slot-collapsible" onclick="toggleSection(this)">
                        Base Stats
                        @if(!empty($slot['alt_build_rank']))
                            <span style="color:var(--dim);font-weight:normal"> — Rank {{ $slot['alt_build_rank'] }}</span>
                        @endif
                        <span class="build-stat-bst">{{ array_sum(array_column($displayStats, 'value')) }} BST</span>
                        <span class="build-slot-collapse-icon">▾</span>
                    </div>
                    <div class="build-stat-bars build-slot-collapsible-body" style="display:none">
                        @foreach($displayStats as $stat)
                        <div class="build-stat-row">
                            <span class="build-stat-label {{ $stat['is_focus'] ? 'build-stat-focus' : '' }} {{ $stat['nature_mod'] === 'boost' ? 'build-stat-nature-boost' : ($stat['nature_mod'] === 'nerf' ? 'build-stat-nature-nerf' : '') }}">{{ $stat['label'] }}</span>
                            <div class="build-stat-bar-wrap">
                                <div class="build-stat-bar {{ $stat['is_focus'] ? 'build-stat-bar--focus' : '' }}"
                                     style="width:{{ $stat['pct'] }}%"></div>
                            </div>
                            <span class="build-stat-value">{{ $stat['value'] }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>

                {{-- Effective Stats (only when level is set) --}}
                @if($effectiveDisplay)
                <div class="build-slot-section">
                    <div class="build-slot-section-label build-slot-collapsible" onclick="toggleSection(this)">
                        Effective Stats
                        <span style="color:var(--dim);font-weight:normal"> — Lv.{{ $level }}</span>
                        <span class="build-stat-bst">
                            {{ array_sum(array_column($effectiveDisplay, 'min')) }}–{{ array_sum(array_column($effectiveDisplay, 'max')) }}
                        </span>
                        <span class="build-slot-collapse-icon">▾</span>
                    </div>
                    <div class="build-stat-bars build-slot-collapsible-body" style="display:none">
                        @foreach($effectiveDisplay as $idx => $stat)
                        @php $iv = $slot['ivs'][$idx] ?? null; @endphp
                        <div class="build-stat-row">
                            <span class="build-stat-label {{ $stat['is_focus'] ? 'build-stat-focus' : '' }}">{{ $stat['label'] }}</span>
                            <div class="build-stat-bar-wrap">
                                <div class="build-stat-bar {{ $stat['is_focus'] ? 'build-stat-bar--focus' : '' }}"
                                     style="width:{{ $stat['pct_max'] }}%"></div>
                            </div>
                            <span class="build-stat-value">
                                @if($stat['is_range']){{ $stat['min'] }}–{{ $stat['max'] }}@else{{ $stat['min'] }}@endif
                            </span>
                            @if($iv !== null && $iv !== '')
                                <span class="build-stat-iv">IV:{{ $iv }}</span>
                            @endif
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                @elseif($statError)
                <div class="build-slot-section">
                    <div class="build-stat-error">⚠ {{ $statError }}</div>
                </div>
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
            const isOwner = me.userId === {{ $build->user_id }} || me.username === '{{ $build->user->username }}';
            if (isOwner || me.isAdmin || me.isSuperAdmin) {
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

// ── Collapsible sections ─────────────────────────────────────────
function toggleSection(label) {
    const body = label.parentElement.querySelector('.build-slot-collapsible-body');
    const icon = label.querySelector('.build-slot-collapse-icon');
    if (!body) return;
    const open = body.style.display !== 'none';
    body.style.display = open ? 'none' : '';
    if (icon) icon.textContent = open ? '▾' : '▴';
}

// ── Sprite rendering ──────────────────────────────────────────────
(function() {
    async function extractFirstFrame(dex, shiny = false, formKey = '') {
        return new Promise(resolve => {
            const img = new Image();
            img.crossOrigin = 'anonymous';
            img.onload = () => {
                const tmp = document.createElement('canvas');
                tmp.width = img.naturalWidth; tmp.height = img.naturalHeight;
                const tctx = tmp.getContext('2d', { willReadFrequently: true });
                tctx.drawImage(img, 0, 0);
                const atlasUrl = shiny
                    ? `/pokevoid-atlas-shiny/${dex}${formKey ? '/' + formKey : ''}.json`
                    : `/pokevoid-atlas/${dex}.json`;
                fetch(atlasUrl)
                    .then(r => r.json())
                    .then(atlas => {
                        const frame = atlas.textures[0].frames[0];
                        const { x, y, w, h } = frame.frame;
                        const { x: sx, y: sy } = frame.spriteSourceSize;
                        const { w: sw, h: sh } = frame.sourceSize;
                        const out = document.createElement('canvas');
                        out.width = sw; out.height = sh;
                        out.getContext('2d').drawImage(tmp, x, y, w, h, sx, sy, w, h);
                        resolve(out);
                    })
                    .catch(() => resolve(tmp));
            };
            img.onerror = () => {
                // Form not found — fall back to base shiny
                if (shiny && formKey) {
                    extractFirstFrame(dex, true, '').then(resolve);
                } else {
                    resolve(null);
                }
            };
            const formSuffix = formKey ? `-${formKey}` : '';
            img.src = shiny
                ? `/pokevoid-sprites/shiny/${dex}${formSuffix}.png`
                : `/pokevoid-sprites/${dex}${formSuffix}.png`;
        });
    }

    function softLight(bg, fg) {
        return bg <= 0.5 ? 2 * bg * fg : 1 - 2 * (1 - bg) * (1 - fg);
    }
    function hexToRgb(hex) {
        return [parseInt(hex.slice(1,3),16), parseInt(hex.slice(3,5),16), parseInt(hex.slice(5,7),16)];
    }
    function applyGrayscaleOverlay(imageData, palette) {
        const data = imageData.data;
        const targets = palette.map(hexToRgb);
        for (let i = 0; i < data.length; i += 4) {
            if (data[i+3] === 0) continue;
            const lum = (data[i]/255 + data[i+1]/255 + data[i+2]/255) / 3;
            const [tr, tg, tb] = targets[Math.min(Math.floor(lum * targets.length), targets.length-1)].map(c => c/255);
            data[i]   = Math.round(softLight(lum, tr) * 255);
            data[i+1] = Math.round(softLight(lum, tg) * 255);
            data[i+2] = Math.round(softLight(lum, tb) * 255);
        }
        return imageData;
    }

    document.querySelectorAll('.build-slot-sprite--canvas').forEach(async canvas => {
        const dex     = canvas.dataset.dex;
        const typeStr = canvas.dataset.type || null;
        const palette = JSON.parse(canvas.dataset.palette || '[]');
        const shiny   = canvas.dataset.shiny === '1';
        const formKey = canvas.dataset.formKey || '';
        if (!dex) return;
        // Some pokemon (Arceus=493, Silvally=773) have type-suffixed sprites
        const spriteKey = typeStr ? `${dex}-${typeStr}` : dex;
        const frame = await extractFirstFrame(spriteKey, shiny, formKey);
        if (!frame) { canvas.style.display = 'none'; return; }
        const ctx   = canvas.getContext('2d', { willReadFrequently: palette.length > 0 });
        const scale = Math.min(canvas.width / frame.width, canvas.height / frame.height);
        const dx    = (canvas.width  - frame.width  * scale) / 2;
        const dy    = (canvas.height - frame.height * scale) / 2;
        ctx.imageSmoothingEnabled = false;
        ctx.drawImage(frame, dx, dy, frame.width * scale, frame.height * scale);
        if (palette.length > 0) {
            const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
            applyGrayscaleOverlay(imageData, palette);
            ctx.putImageData(imageData, 0, 0);
        }
    });
})();
</script>

@endsection
