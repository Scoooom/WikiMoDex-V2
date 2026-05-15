@extends('layouts.app')

@section('title', 'Community Builds')

@section('content')
<div class="wiki-page">

    {{-- Header --}}
    <div class="wiki-page-header">
        <div class="wiki-page-header-inner">
            <h1 class="wiki-page-title">Community Builds</h1>
            <p class="wiki-page-lead">Team builds shared by the PokéVoid community. Submit yours to help others climb.</p>
        </div>
        <div id="builds-header-actions" style="display:none;gap:0.5rem;display:none;align-items:center">
            <a href="/builds/import.html" class="btn-secondary">⬆ Import Save</a>
            <a href="/builds/new.html" class="btn-primary">+ Submit Build</a>
        </div>
    </div>

    {{-- Toolbar --}}
    <div class="builds-toolbar">
        <form method="GET" action="/builds.html" class="builds-search-form">
            <input
                type="text"
                name="q"
                value="{{ request('q') }}"
                placeholder="Search Pokémon, title, description…"
                class="builds-search-input"
                autocomplete="off"
            >
            <button type="submit" class="builds-search-btn">Search</button>
            @if(request('q'))
                <a href="/builds.html" class="builds-search-clear">✕</a>
            @endif
        </form>

        <div class="builds-sort-tabs">
            <a href="/builds.html?{{ http_build_query(array_merge(request()->except(['sort', 'page']), ['sort' => 'top'])) }}"
               class="builds-sort-tab {{ $sort === 'top' ? 'active' : '' }}">⬆ Top</a>
            <a href="/builds.html?{{ http_build_query(array_merge(request()->except(['sort', 'page']), ['sort' => 'new'])) }}"
               class="builds-sort-tab {{ $sort === 'new' ? 'active' : '' }}">🕒 New</a>
        </div>
    </div>

    {{-- Results --}}
    @if($builds->isEmpty())
        <div class="builds-empty">
            <div class="builds-empty-icon">🏗️</div>
            <p>No builds yet{{ request('q') ? ' matching "' . e(request('q')) . '"' : '' }}.</p>
            <a href="/builds/new.html" class="btn-primary">Be the first to submit one</a>
        </div>
    @else
        <div class="builds-grid">
            @foreach($builds as $build)
            <a href="/build/{{ $build->slug }}.html" class="build-card">
                {{-- Team preview sprites --}}
                <div class="build-card-sprites">
                    @foreach(collect($build->team ?? [])->take(6) as $slot)
                        @if(!empty($slot['dex_number']))
                            <canvas
                                class="build-card-sprite build-card-sprite--canvas {{ !empty($slot['shiny']) ? 'build-card-sprite--shiny' : '' }}"
                                data-dex="{{ $slot['dex_number'] }}"
                                data-shiny="{{ !empty($slot['shiny']) ? '1' : '0' }}"
                                data-variant="{{ $slot['variant'] ?? 0 }}"
                                data-form-key="{{ $slot['form_key'] ?? '' }}"
                                data-palette="{{ json_encode($slot['palette'] ?? []) }}"
                                width="42" height="42"
                                alt="{{ $slot['species'] ?? '' }}"
                            ></canvas>
                        @elseif(!empty($slot['species']))
                            {{-- Core glitch / SMITTY: use cFront route by name --}}
                            <img
                                src="/cFront:{{ $slot['species'] }}.png"
                                class="build-card-sprite build-card-sprite--canvas"
                                alt="{{ $slot['species'] }}"
                                onerror="this.style.opacity='0'"
                            >
                        @endif
                    @endforeach
                </div>

                <div class="build-card-body">
                    <div class="build-card-title">{{ $build->title }}</div>
                    @if($build->description)
                        <div class="build-card-desc">{{ Str::limit($build->description, 80) }}</div>
                    @endif
                    <div class="build-card-meta">
                        <span class="build-card-author">
                            <img src="{{ $build->user->getAvatarURL() }}" class="build-card-avatar" alt="">
                            {{ $build->user->username }}
                        </span>
                        <span class="build-card-votes">▲ {{ $build->votes }}</span>
                    </div>
                </div>
            </a>
            @endforeach
        </div>

        {{-- Pagination --}}
        @if($builds->hasPages())
        <div class="builds-pagination">
            {{ $builds->links() }}
        </div>
        @endif
    @endif

</div>

<script>
// Show submit button only when logged in
fetch('/me.json', { credentials: 'same-origin' })
    .then(r => r.json())
    .then(me => {
        if (me.authed) {
            document.getElementById('builds-header-actions').style.display = '';
        }
    }).catch(() => {});

// Render first frame of each card sprite from the atlas
(async () => {
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

    async function renderCardSprite(canvas) {
        const dex     = canvas.dataset.dex;
        const shiny   = canvas.dataset.shiny === '1';
        const variant = parseInt(canvas.dataset.variant || '0', 10);
        const formKey = canvas.dataset.formKey || '';
        const palette = JSON.parse(canvas.dataset.palette || '[]');
        if (!dex) return;

        const formSuffix = formKey ? `-${formKey}` : '';
        const imgSrc    = shiny ? `/pokevoid-sprites/shiny/${dex}${formSuffix}.png` : `/pokevoid-sprites/${dex}${formSuffix}.png`;
        const atlasUrl  = shiny ? `/pokevoid-atlas-shiny/${dex}${formKey ? '/' + formKey : ''}.json` : `/pokevoid-atlas/${dex}${formSuffix}.json`;

        const [img, atlas] = await Promise.all([
            new Promise(res => {
                const i = new Image(); i.crossOrigin = 'anonymous';
                i.onload = () => res(i); i.onerror = () => res(null);
                i.src = imgSrc;
            }),
            fetch(atlasUrl).then(r => r.json()).catch(() => null),
        ]);

        if (!img || !atlas) { canvas.style.display = 'none'; return; }

        const tmp = document.createElement('canvas');
        tmp.width = img.naturalWidth; tmp.height = img.naturalHeight;
        tmp.getContext('2d').drawImage(img, 0, 0);

        const frame = atlas.textures[0].frames[0];
        const { x, y, w, h } = frame.frame;
        const { x: sx, y: sy } = frame.spriteSourceSize;
        const { w: sw, h: sh } = frame.sourceSize;

        // Draw first frame centred into the 42×42 canvas
        const scale = Math.min(canvas.width / sw, canvas.height / sh);
        const ctx   = canvas.getContext('2d', { willReadFrequently: palette.length > 0 });
        ctx.imageSmoothingEnabled = false;
        ctx.drawImage(tmp, x, y, w, h,
            (canvas.width  - sw * scale) / 2 + sx * scale,
            (canvas.height - sh * scale) / 2 + sy * scale,
            w * scale, h * scale);
        if (palette.length > 0) {
            const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
            applyGrayscaleOverlay(imageData, palette);
            ctx.putImageData(imageData, 0, 0);
        }
    }

    // Use IntersectionObserver so off-screen cards don't block page load
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (!entry.isIntersecting) return;
            observer.unobserve(entry.target);
            renderCardSprite(entry.target);
        });
    }, { rootMargin: '200px' });

    document.querySelectorAll('canvas.build-card-sprite').forEach(c => observer.observe(c));
})();
</script>

@endsection
