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
