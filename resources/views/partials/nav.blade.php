<nav class="navbar navbar-expand-md navbar-dark bg-dark fixed-top">
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarsExampleDefault"
        aria-controls="navbarsExampleDefault" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarsExampleDefault">
        <ul class="navbar-nav mr-auto">
            <li class="nav-item active">
                <a class="nav-link" href="/">Home</a>
            </li>
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button"
                    data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    Pokemon Forms
                </a>
                <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                    <a class="dropdown-item" href="/gallery.html">Mod Glitch Forms</a>
                    <a class="dropdown-item" href="/galleryCore.html">Core Glitches</a>
                    <a class="dropdown-item" href="/gallerySmitty.html">SMITTY Pokemon</a>
                    <a class="dropdown-item" href="/gallerySmittyForm.html">SMITTY Forms</a>
                </div>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="/gacha.html">Gacha Calendar</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="/faq.html">FAQs</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="https://pvoffine.scooom.xyz/">Offline</a>
            </li>

            @auth
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" role="button"
                    data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    Account
                </a>
                <div class="dropdown-menu">
                    <a class="dropdown-item" href="/u:{{ Auth::user()->username }}.html">Profile</a>
                    <a class="dropdown-item" href="/create.html">Upload Glitch</a>
                    <a class="dropdown-item" href="/logout.html">Logout</a>
                </div>
            </li>
            @endauth
        </ul>

        <form class="form-inline my-2 my-md-0" method="post" action="/login.html">
            @csrf
            @auth
                <input type="hidden" name="logoutkey" value="1">
                <input type="hidden" name="returnURL" value="/">
                <input class="form-control" type="submit" value="Logout {{ Auth::user()->username }}">
                <div class="image-wrapper ml-2">
                    <a href="/u:{{ Auth::user()->username }}.html">
                        <img width="32px" height="32px" class="image-round"
                            src="{{ Auth::user()->getAvatarURL() }}" />
                    </a>
                </div>
            @else
                <input type="hidden" name="loginkey" value="1">
                <input type="hidden" name="returnURL" value="{{ request()->getRequestUri() }}">
                <input class="form-control" type="submit" value="Login">
            @endauth
        </form>
    </div>
</nav>
