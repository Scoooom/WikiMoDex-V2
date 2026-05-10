@extends('layouts.app')

@section('title', 'Alt Builds — PokéVoid Wiki')

@section('content')

<button class="wiki-sidebar-toggle" id="wikiSidebarToggle" aria-label="Toggle navigation">
    <span class="wiki-sidebar-toggle-icon">☰</span>
    <span class="wiki-sidebar-toggle-label">Contents</span>
</button>
<div class="wiki-sidebar-overlay" id="wikiSidebarOverlay"></div>

<aside class="wiki-sidebar" id="wikiSidebar">
    <div class="wiki-sidebar-header">
        <a href="{{ route('wiki.index') }}" class="wiki-back-link">← Wiki Index</a>
        <button class="wiki-sidebar-close" id="wikiSidebarClose">✕</button>
    </div>
    <div class="wiki-sidebar-inner">
        <div class="wiki-sidebar-section">
            <button class="wiki-sidebar-cat-toggle" aria-expanded="true">
                <span>By Champion</span>
                <span class="wiki-sidebar-chevron">▾</span>
            </button>
            <ul class="wiki-sidebar-list">
                @foreach(\App\Models\AltBuild::championLabel() as $key => $label)
                    @if(isset($grouped[$key]))
                    <li>
                        <a href="#champion-{{ $key }}" class="wiki-sidebar-link">
                            {{ $label }}
                            <span style="color:var(--dim);font-size:0.7em;margin-left:0.3em">{{ $grouped[$key]->count() }}</span>
                        </a>
                    </li>
                    @endif
                @endforeach
            </ul>
        </div>
        @foreach($sidebarGrouped as $category => $articles)
        <div class="wiki-sidebar-section">
            <button class="wiki-sidebar-cat-toggle" aria-expanded="false">
                <span>{{ $category }}</span>
                <span class="wiki-sidebar-chevron">▾</span>
            </button>
            <ul class="wiki-sidebar-list" style="max-height:0;overflow:hidden">
                @foreach($articles as $a)
                <li><a href="{{ route('wiki.show', $a['slug']) }}" class="wiki-sidebar-link">{{ $a['title'] }}</a></li>
                @endforeach
            </ul>
        </div>
        @endforeach
    </div>
</aside>

<div class="wiki-layout">
    <main class="wiki-content">
        <div class="wiki-article-meta">
            <span class="wiki-article-category">Champions</span>
            <span class="wiki-article-updated">{{ $builds->count() }} builds · auto-generated from source</span>
        </div>

        <div class="wiki-prose">
            <h1>Alt Builds</h1>
            <p>Alt Builds are alternate forms of a Champion's Signature Pokémon with changed types, abilities, stat focuses, and movesets. Sprites are recoloured using the game's <code>grayscale_overlay</code> shader applied to the HD source sprites.</p>
        </div>

        @php
            $championLabels = \App\Models\AltBuild::championLabel();
            $championLinks  = [
                'apollo_diana' => route('wiki.show', 'apollo'),
                'brock'        => route('wiki.show', 'brock'),
                'misty'        => route('wiki.show', 'misty'),
            ];
        @endphp

        @foreach($grouped as $champion => $championBuilds)
        <div class="altbuild-champion-section" id="champion-{{ $champion }}">
            <div class="altbuild-champion-header">
                <h2 class="wiki-prose" style="border-bottom:none;margin:0">
                    @if(isset($championLinks[$champion]))
                        <a href="{{ $championLinks[$champion] }}" class="altbuild-champion-link">
                            {{ $championLabels[$champion] ?? ucfirst(str_replace('_', ' ', $champion)) }}
                        </a>
                    @else
                        {{ $championLabels[$champion] ?? ucfirst(str_replace('_', ' ', $champion)) }}
                    @endif
                </h2>
                <span class="altbuild-count">{{ $championBuilds->count() }} builds</span>
            </div>

            <div class="altbuild-grid">
                @foreach($championBuilds as $build)
                <div class="altbuild-card"
                     data-dex="{{ $build->dex_number }}"
                     data-palette='@json($build->target_palette ?? [])'
                     data-dark='@json($build->dark_palette ?? [])'>

                    <div class="altbuild-sprite-wrap">
                        <canvas class="altbuild-canvas" width="160" height="160"></canvas>
                    </div>

                    <div class="altbuild-card-header">
                        <div class="altbuild-card-title-wrap">
                            <span class="altbuild-species">{{ $build->species }}</span>
                            <span class="altbuild-name">{{ $build->name }}</span>
                        </div>
                        <div class="altbuild-badges">
                            @if($build->type1)
                                <span class="altbuild-type type-{{ strtolower($build->type1) }}">{{ $build->type1 }}</span>
                            @endif
                            @if($build->type2)
                                <span class="altbuild-type type-{{ strtolower($build->type2) }}">{{ $build->type2 }}</span>
                            @endif
                            @if(!$build->type1)
                                <span class="altbuild-type-unchanged">Type unchanged</span>
                            @endif
                        </div>
                    </div>

                    <div class="altbuild-card-body">
                        <div class="altbuild-row">
                            <span class="altbuild-label">Stat Focus</span>
                            <span class="altbuild-val">{{ $build->stat_focus }}</span>
                        </div>
                        <div class="altbuild-row">
                            <span class="altbuild-label">Requires</span>
                            <span class="altbuild-val">
                                <strong>{{ $build->species }}</strong>
                                <span style="color:var(--dim);font-size:0.8em"> (Signature form must be unlocked first)</span>
                            </span>
                        </div>
                        @if($build->ability1 || $build->ability2 || $build->ability3)
                        <div class="altbuild-row">
                            <span class="altbuild-label">Abilities</span>
                            <span class="altbuild-val">{{ collect([$build->ability1, $build->ability2, $build->ability3])->filter()->join(' / ') }}</span>
                        </div>
                        @endif
                        @if($build->passive_ability)
                        <div class="altbuild-row">
                            <span class="altbuild-label">Passive</span>
                            <span class="altbuild-val">{{ $build->passive_ability }}</span>
                        </div>
                        @endif
                        @if($build->key_moves && count($build->key_moves))
                        <div class="altbuild-row altbuild-row--moves">
                            <span class="altbuild-label">Key Moves</span>
                            <span class="altbuild-val altbuild-moves">
                                @foreach($build->key_moves as $move)
                                <span class="altbuild-move">{{ $move }}</span>
                                @endforeach
                            </span>
                        </div>
                        @endif
                        @if($build->prevents_evolution)
                        <div class="altbuild-row">
                            <span class="altbuild-label">Evolution</span>
                            <span class="altbuild-val altbuild-noevo">⚠ Prevents evolution</span>
                        </div>
                        @endif
                    </div>

                    @if($build->dex_number)
                    <button class="altbuild-stats-toggle" data-target="stats-{{ $build->build_id }}">
                        <span>Base Stats</span>
                        <span class="chevron">▾</span>
                    </button>
                    <div class="altbuild-stats-block" id="stats-{{ $build->build_id }}"
                         data-dex="{{ $build->dex_number }}"
                         data-focus="{{ $build->stat_focus }}"
                         data-rank="{{ $build->rank }}">
                        <div class="altbuild-stat-loading" style="color:var(--dim);font-size:0.72rem;font-family:var(--font-mono);padding:0.25rem 0">Loading…</div>
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        @endforeach
    </main>
</div>

<script>
// ── Extract first frame from embedded atlas PNG ───────────────────────────
async function extractFirstFrame(src) {
    return new Promise((resolve) => {
        const img = new Image();
        img.crossOrigin = 'anonymous';
        img.onload = () => {
            // Draw full sheet to a temp canvas to read pixel data
            const tmp = document.createElement('canvas');
            tmp.width = img.naturalWidth;
            tmp.height = img.naturalHeight;
            const tctx = tmp.getContext('2d', { willReadFrequently: true });
            tctx.drawImage(img, 0, 0);

            // Try to fetch the atlas JSON from the same URL with ?atlas=1
            fetch(`/pokevoid-atlas/${src.split('/').pop().replace('.png','')}.json`)
                .then(r => r.json())
                .then(atlas => {
                    const frame = atlas.textures[0].frames[0];
                    const { x, y, w, h } = frame.frame;
                    const { x: sx, y: sy } = frame.spriteSourceSize;
                    const { w: sw, h: sh } = frame.sourceSize;

                    const out = document.createElement('canvas');
                    out.width = sw;
                    out.height = sh;
                    const octx = out.getContext('2d');
                    octx.drawImage(tmp, x, y, w, h, sx, sy, w, h);
                    resolve(out);
                })
                .catch(() => {
                    // No atlas available — use the full image
                    resolve(tmp);
                });
        };
        img.onerror = () => resolve(null);
        img.src = src;
    });
}

// ── Grayscale Overlay shader ──────────────────────────────────────────────
function softLight(bg, fg) {
    // GLSL: mix(1-2*(1-bg)*(1-fg), 2*bg*fg, step(bg, 0.5))
    if (bg <= 0.5) {
        return 2 * bg * fg;
    } else {
        return 1 - 2 * (1 - bg) * (1 - fg);
    }
}

function hexToRgb(hex) {
    const r = parseInt(hex.slice(1,3), 16);
    const g = parseInt(hex.slice(3,5), 16);
    const b = parseInt(hex.slice(5,7), 16);
    return [r, g, b];
}

function applyGrayscaleOverlay(imageData, targetPalette) {
    const data = imageData.data;
    const targets = targetPalette.map(hexToRgb);

    for (let i = 0; i < data.length; i += 4) {
        if (data[i+3] === 0) continue; // skip transparent

        const r = data[i] / 255;
        const g = data[i+1] / 255;
        const b = data[i+2] / 255;

        // Pick closest target colour based on pixel luminance
        const lum = (r + g + b) / 3;
        const idx = Math.min(
            Math.floor(lum * targets.length),
            targets.length - 1
        );
        const [tr, tg, tb] = targets[idx].map(c => c / 255);

        // Apply soft-light blend: grayscale bg × target colour fg
        data[i]   = Math.round(softLight(lum, tr) * 255);
        data[i+1] = Math.round(softLight(lum, tg) * 255);
        data[i+2] = Math.round(softLight(lum, tb) * 255);
    }
    return imageData;
}

// ── Load and render all sprites ───────────────────────────────────────────
document.querySelectorAll('.altbuild-card').forEach(card => {
    const dex     = card.dataset.dex;
    const palette = JSON.parse(card.dataset.palette || '[]');
    const canvas  = card.querySelector('.altbuild-canvas');
    const ctx     = canvas.getContext('2d', { willReadFrequently: true });

    if (!dex || !palette.length) { canvas.style.display = 'none'; return; }

    extractFirstFrame(`/pokevoid-sprites/${dex}.png`).then(frameCanvas => {
        if (!frameCanvas) { canvas.style.display = 'none'; return; }

        const size = 160;
        const scale = Math.min(size / frameCanvas.width, size / frameCanvas.height) * 0.85;
        const w = Math.round(frameCanvas.width * scale);
        const h = Math.round(frameCanvas.height * scale);
        const x = Math.round((size - w) / 2);
        const y = Math.round((size - h) / 2);

        ctx.clearRect(0, 0, size, size);
        ctx.imageSmoothingEnabled = false;
        ctx.drawImage(frameCanvas, 0, 0, frameCanvas.width, frameCanvas.height, x, y, w, h);

        const imageData = ctx.getImageData(0, 0, size, size);
        applyGrayscaleOverlay(imageData, palette);
        ctx.putImageData(imageData, 0, 0);
    });
});

// ── Stat block toggles ────────────────────────────────────────────────────
document.querySelectorAll('.altbuild-stats-toggle').forEach(btn => {
    btn.addEventListener('click', () => {
        const block = document.getElementById(btn.dataset.target);
        const chevron = btn.querySelector('.chevron');
        const isOpen = block.classList.contains('open');

        block.classList.toggle('open', !isOpen);
        chevron.style.transform = isOpen ? '' : 'rotate(180deg)';

        // Load stats on first open
        if (!isOpen && block.dataset.loaded !== 'true') {
            block.dataset.loaded = 'true';
            const buildId = btn.dataset.target.replace('stats-', '');

            fetch(`/alt-build-stats/${buildId}.json`)
                .then(r => r.json())
                .then(data => {
                    let html = '';
                    data.stats.forEach(s => {
                        const pct = Math.min(100, Math.round((s.value / 255) * 100));
                        const color = s.focus ? 'var(--accent3)' : 'var(--accent)';
                        html += `<div class="altbuild-stat-row">
                            <span class="altbuild-stat-label">${s.label}</span>
                            <div class="altbuild-stat-bar-wrap">
                                <div class="altbuild-stat-bar" style="width:${pct}%;background:${color}"></div>
                            </div>
                            <span class="altbuild-stat-val">${s.value}</span>
                        </div>`;
                    });
                    html += `<div class="altbuild-bst">BST: ${data.bst}</div>`;
                    block.innerHTML = html;
                })
                .catch(() => {
                    block.innerHTML = '<div style="color:var(--dim);font-size:0.72rem;font-family:var(--font-mono)">Stats unavailable</div>';
                });
        }
    });
});
(function() {
    const sidebar  = document.getElementById('wikiSidebar');
    const toggle   = document.getElementById('wikiSidebarToggle');
    const close    = document.getElementById('wikiSidebarClose');
    const overlay  = document.getElementById('wikiSidebarOverlay');
    function open()  { sidebar.classList.add('open');  overlay.classList.add('open');  document.body.classList.add('wiki-sidebar-open'); }
    function shut()  { sidebar.classList.remove('open'); overlay.classList.remove('open'); document.body.classList.remove('wiki-sidebar-open'); }
    toggle.addEventListener('click', () => sidebar.classList.contains('open') ? shut() : open());
    close.addEventListener('click', shut);
    overlay.addEventListener('click', shut);
    if (localStorage.getItem('wikiSidebar') === 'open') open();
    sidebar.addEventListener('transitionend', () => localStorage.setItem('wikiSidebar', sidebar.classList.contains('open') ? 'open' : 'closed'));
    document.querySelectorAll('.wiki-sidebar-cat-toggle').forEach(btn => {
        btn.addEventListener('click', () => {
            const list = btn.nextElementSibling;
            const expanded = btn.getAttribute('aria-expanded') === 'true';
            btn.setAttribute('aria-expanded', !expanded);
            list.style.maxHeight = expanded ? '0' : list.scrollHeight + 'px';
            btn.querySelector('.wiki-sidebar-chevron').style.transform = expanded ? 'rotate(-90deg)' : '';
        });
        const list = btn.nextElementSibling;
        if (btn.getAttribute('aria-expanded') === 'true') list.style.maxHeight = list.scrollHeight + 'px';
    });
})();
</script>
@endsection
