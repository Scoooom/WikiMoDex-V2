<nav class="site-nav" id="site-nav">
    <div class="nav-inner">
        <a class="nav-brand" href="/">
            <span class="nav-brand-gem">V</span>
            WikiMoDex
        </a>

        <div class="nav-links" id="nav-links">
            <a class="nav-link {{ request()->is('/') ? 'active' : '' }}" href="/">Home</a>

            <div class="nav-dropdown" id="forms-dropdown">
                <button class="nav-link nav-dropdown-toggle" aria-expanded="false" onclick="toggleDropdown('forms-dropdown')">
                    Pokémon Forms <span class="nav-caret">▾</span>
                </button>
                <div class="nav-dropdown-menu">
                    <a class="nav-dropdown-item" href="/gallery.html">Mod Glitch Forms</a>
                    <a class="nav-dropdown-item" href="/galleryCore.html">Core Glitches</a>
                    <a class="nav-dropdown-item" href="/gallerySmitty.html">SMITTY Pokémon</a>
                    <a class="nav-dropdown-item" href="/gallerySmittyForm.html">SMITTY Forms</a>
                </div>
            </div>

            <a class="nav-link {{ request()->is('gacha*') ? 'active' : '' }}" href="/gacha.html">Gacha</a>
            <a class="nav-link {{ request()->is('faq*') ? 'active' : '' }}" href="/faq.html">FAQ</a>
            <a class="nav-link {{ request()->is('wiki*') ? 'active' : '' }}" href="/wiki.html">Wiki</a>
            <button class="nav-search-btn" id="wikiSearchOpen" aria-label="Search" title="Search (Ctrl+K)">
                <svg width="15" height="15" viewBox="0 0 15 15" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M10 6.5a3.5 3.5 0 1 1-7 0 3.5 3.5 0 0 1 7 0zm-.691 3.516a4.5 4.5 0 1 1 .707-.707l2.838 2.837a.5.5 0 0 1-.708.708L9.31 10.016z" fill="currentColor"/></svg>
            </button>
            <a class="nav-link" href="https://pvoffine.scooom.xyz/" target="_blank" rel="noopener">Offline ↗</a>

            @auth
            <div class="nav-dropdown" id="account-dropdown">
                <button class="nav-link nav-dropdown-toggle" aria-expanded="false" onclick="toggleDropdown('account-dropdown')">
                    <img class="nav-avatar" src="{{ Auth::user()->getAvatarURL() }}" alt="{{ Auth::user()->username }}">
                    {{ Auth::user()->username }} <span class="nav-caret">▾</span>
                </button>
                <div class="nav-dropdown-menu nav-dropdown-menu--right">
                    <a class="nav-dropdown-item" href="/u:{{ Auth::user()->username }}.html">Profile</a>
                    <a class="nav-dropdown-item" href="/create.html">Upload Glitch</a>
                    @if(Auth::user()->user_id === '356260100064673814')
                    <div class="nav-dropdown-divider"></div>
                    <a class="nav-dropdown-item" href="/admin/wiki.html">Wiki Admin</a>
                    @endif
                    <div class="nav-dropdown-divider"></div>
                    <form method="post" action="/login.html" style="display:contents">
                        @csrf
                        <input type="hidden" name="logoutkey" value="1">
                        <input type="hidden" name="returnURL" value="/">
                        <button type="submit" class="nav-dropdown-item nav-dropdown-item--danger">Logout</button>
                    </form>
                </div>
            </div>
            @else
            <form method="post" action="/login.html" style="display:contents">
                @csrf
                <input type="hidden" name="loginkey" value="1">
                <input type="hidden" name="returnURL" value="{{ request()->getRequestUri() }}">
                <button type="submit" class="nav-login-btn">Login with Discord</button>
            </form>
            @endauth
        </div>

        <button class="nav-burger" aria-label="Toggle navigation" onclick="toggleMobileNav()">
            <span></span><span></span><span></span>
        </button>
    </div>
</nav>

<script>
function toggleDropdown(id) {
    const el = document.getElementById(id);
    const isOpen = el.classList.contains('open');
    document.querySelectorAll('.nav-dropdown.open').forEach(d => d.classList.remove('open'));
    if (!isOpen) el.classList.add('open');
}
function toggleMobileNav() {
    document.getElementById('nav-links').classList.toggle('mobile-open');
}
document.addEventListener('click', function(e) {
    if (!e.target.closest('.nav-dropdown')) {
        document.querySelectorAll('.nav-dropdown.open').forEach(d => d.classList.remove('open'));
    }
});
</script>

{{-- ── Wiki Search Modal ──────────────────────────────────────────── --}}
<div class="wiki-search-backdrop" id="wikiSearchBackdrop"></div>
<div class="wiki-search-modal" id="wikiSearchModal" role="dialog" aria-modal="true" aria-label="Search">
    <div class="wiki-search-input-wrap">
        <svg class="wiki-search-icon" width="16" height="16" viewBox="0 0 15 15" fill="none"><path d="M10 6.5a3.5 3.5 0 1 1-7 0 3.5 3.5 0 0 1 7 0zm-.691 3.516a4.5 4.5 0 1 1 .707-.707l2.838 2.837a.5.5 0 0 1-.708.708L9.31 10.016z" fill="currentColor"/></svg>
        <input type="text" id="wikiSearchInput" class="wiki-search-field"
               placeholder="Search wiki, items, forms…" autocomplete="off" spellcheck="false">
        <kbd class="wiki-search-esc">Esc</kbd>
    </div>
    <div class="wiki-search-results" id="wikiSearchResults">
        <div class="wiki-search-hint">Type at least 2 characters to search…</div>
    </div>
</div>

<script>
function initWikiSearch() {
    const btn      = document.getElementById('wikiSearchOpen');
    const backdrop = document.getElementById('wikiSearchBackdrop');
    const modal    = document.getElementById('wikiSearchModal');
    const input    = document.getElementById('wikiSearchInput');
    const results  = document.getElementById('wikiSearchResults');

    if (!btn || !modal) return;

    const TYPE_ICONS = {
        article: '📄',
        item:    '🎒',
        form:    '✨',
        glitch:  '👾',
    };

    const TYPE_COLORS = {
        article: 'var(--accent2)',
        item:    '#f5d76e',
        form:    '#a8e6cf',
        glitch:  '#ffaaa5',
    };

    function open() {
        modal.classList.add('open');
        backdrop.classList.add('open');
        document.body.style.overflow = 'hidden';
        input.focus();
        input.select();
    }

    function close() {
        modal.classList.remove('open');
        backdrop.classList.remove('open');
        document.body.style.overflow = '';
    }

    // Ensure closed on init
    close();

    btn.addEventListener('click', open);
    backdrop.addEventListener('click', close);

    document.addEventListener('keydown', e => {
        if ((e.ctrlKey || e.metaKey) && e.key === 'k') { e.preventDefault(); open(); }
        if (e.key === 'Escape') close();
        if (e.key === 'ArrowDown' && modal.classList.contains('open')) {
            e.preventDefault();
            const links = results.querySelectorAll('.wiki-search-result-item');
            const idx = [...links].indexOf(document.activeElement);
            (links[idx + 1] || links[0])?.focus();
        }
        if (e.key === 'ArrowUp' && modal.classList.contains('open')) {
            e.preventDefault();
            const links = results.querySelectorAll('.wiki-search-result-item');
            const idx = [...links].indexOf(document.activeElement);
            (links[idx - 1] || links[links.length - 1])?.focus();
        }
    });

    let debounce;
    let lastQ = '';

    input.addEventListener('input', () => {
        clearTimeout(debounce);
        const q = input.value.trim();
        if (q === lastQ) return;
        lastQ = q;

        if (q.length < 2) {
            results.innerHTML = '<div class="wiki-search-hint">Type at least 2 characters to search…</div>';
            return;
        }

        results.innerHTML = '<div class="wiki-search-hint">Searching…</div>';

        debounce = setTimeout(() => {
            fetch(`/wiki-search.json?q=${encodeURIComponent(q)}`)
                .then(r => r.json())
                .then(data => renderResults(data.results, q))
                .catch(() => {
                    results.innerHTML = '<div class="wiki-search-hint wiki-search-error">Search failed. Try again.</div>';
                });
        }, 200);
    });

    function highlight(text, q) {
        if (!text) return '';
        return text.replace(new RegExp(`(${q.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')})`, 'gi'),
            '<mark class="wiki-search-mark">$1</mark>');
    }

    function renderResults(items, q) {
        if (!items.length) {
            results.innerHTML = `<div class="wiki-search-hint">No results for "<strong>${q}</strong>"</div>`;
            return;
        }

        // Group by type
        const groups = {};
        const groupOrder = ['article', 'item', 'form', 'glitch'];
        const groupLabels = { article: 'Wiki Articles', item: 'Items', form: 'Pokémon Forms', glitch: 'Mod Glitch Forms' };
        items.forEach(item => { (groups[item.type] = groups[item.type] || []).push(item); });

        let html = '';
        for (const type of groupOrder) {
            if (!groups[type]) continue;
            html += `<div class="wiki-search-group-label">${groupLabels[type]}</div>`;
            for (const item of groups[type]) {
                html += `<a href="${item.url}" class="wiki-search-result-item" tabindex="0">
                    <span class="wiki-search-result-icon" style="color:${TYPE_COLORS[item.type]}">${TYPE_ICONS[item.type]}</span>
                    <span class="wiki-search-result-body">
                        <span class="wiki-search-result-title">${highlight(item.title, q)}</span>
                        ${item.subtitle ? `<span class="wiki-search-result-sub">${item.subtitle}</span>` : ''}
                        ${item.excerpt ? `<span class="wiki-search-result-excerpt">${highlight(item.excerpt, q)}</span>` : ''}
                    </span>
                    <span class="wiki-search-result-badge" style="color:${TYPE_COLORS[item.type]}">${item.label}</span>
                </a>`;
            }
        }

        results.innerHTML = html;

        // Close on result click
        results.querySelectorAll('.wiki-search-result-item').forEach(a => {
            a.addEventListener('click', close);
        });
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initWikiSearch);
} else {
    initWikiSearch();
}
</script>
