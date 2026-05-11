@extends('layouts.app')

@section('title', 'Two-Factor Authentication Required')

@section('content')
<div class="error-shell">
    <div class="error-card error-card--mfa">

        <div class="error-icon">🔐</div>

        <h1 class="error-title">2FA Required</h1>

        @php $user = auth()->user(); @endphp

        @if($user && $user->isAdmin())
            <p class="error-lead">
                Your account has <strong>admin privileges</strong>, but two-factor authentication
                is <strong>not enabled</strong> on your Discord account.
            </p>
        @elseif($user && $user->is_wiki_editor)
            <p class="error-lead">
                Your account has <strong>wiki editor privileges</strong>, but two-factor authentication
                is <strong>not enabled</strong> on your Discord account.
            </p>
        @else
            <p class="error-lead">
                Two-factor authentication is required to access this area.
            </p>
        @endif

        <div class="error-mfa-steps">
            <div class="error-mfa-step">
                <span class="error-mfa-num">1</span>
                <div>
                    <strong>Open Discord</strong> and go to
                    <strong>User Settings → My Account</strong>
                </div>
            </div>
            <div class="error-mfa-step">
                <span class="error-mfa-num">2</span>
                <div>
                    Click <strong>"Enable Two-Factor Auth"</strong> and follow the prompts
                </div>
            </div>
            <div class="error-mfa-step">
                <span class="error-mfa-num">3</span>
                <div>
                    <strong>Log out and back in</strong> here — WikiMoDex syncs your 2FA status on every login
                </div>
            </div>
        </div>

        <div class="error-mfa-actions">
            <a href="https://support.discord.com/hc/en-us/articles/219576828-Setting-up-Two-Factor-Authentication"
               target="_blank" rel="noopener" class="btn-accent">
                Discord 2FA Guide ↗
            </a>
            <form method="POST" action="/login.html" style="display:contents">
                @csrf
                <input type="hidden" name="logoutkey" value="1">
                <input type="hidden" name="returnURL" value="/">
                <button type="submit" class="btn-sm">Log out</button>
            </form>
        </div>

    </div>
</div>
@endsection
