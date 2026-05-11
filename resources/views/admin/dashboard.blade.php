@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
<div class="admin-shell">

    @include('admin.partials.sidebar', ['active' => 'dashboard'])

    {{-- Main content --}}
    <main class="admin-main">
        <div class="admin-page-header">
            <h1 class="admin-page-title">Dashboard</h1>
            <span class="admin-page-sub">Welcome back, {{ auth()->user()->username }}</span>
        </div>

        @if(session('success'))
            <div class="flash-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="flash-error">{{ session('error') }}</div>
        @endif

        {{-- Stat cards --}}
        <div class="admin-stat-grid">
            <div class="admin-stat-card">
                <div class="admin-stat-icon">👥</div>
                <div class="admin-stat-body">
                    <div class="admin-stat-value">{{ number_format($stats['users']) }}</div>
                    <div class="admin-stat-label">Users</div>
                </div>
                <a href="{{ route('admin.users') }}" class="admin-stat-link">Manage →</a>
            </div>
            <div class="admin-stat-card">
                <div class="admin-stat-icon">🛡</div>
                <div class="admin-stat-body">
                    <div class="admin-stat-value">{{ $stats['admins'] }}</div>
                    <div class="admin-stat-label">Admins</div>
                </div>
                <a href="{{ route('admin.users') }}?admins_only=1" class="admin-stat-link">View →</a>
            </div>
            <div class="admin-stat-card">
                <div class="admin-stat-icon">👾</div>
                <div class="admin-stat-body">
                    <div class="admin-stat-value">{{ number_format($stats['glitches']) }}</div>
                    <div class="admin-stat-label">Glitch Forms</div>
                </div>
                <a href="/gallery.html" class="admin-stat-link">View →</a>
            </div>
            <div class="admin-stat-card">
                <div class="admin-stat-icon">📄</div>
                <div class="admin-stat-body">
                    <div class="admin-stat-value">{{ $stats['articles'] }}</div>
                    <div class="admin-stat-label">Wiki Articles</div>
                </div>
                <a href="{{ route('wiki.admin.index') }}" class="admin-stat-link">Manage →</a>
            </div>
            <div class="admin-stat-card">
                <div class="admin-stat-icon">🎒</div>
                <div class="admin-stat-body">
                    <div class="admin-stat-value">{{ number_format($stats['items']) }}</div>
                    <div class="admin-stat-label">Game Items</div>
                </div>
                <a href="/wiki:items.html" class="admin-stat-link">View →</a>
            </div>
            <div class="admin-stat-card">
                <div class="admin-stat-icon">✨</div>
                <div class="admin-stat-body">
                    <div class="admin-stat-value">{{ $stats['altBuilds'] }}</div>
                    <div class="admin-stat-label">Alt Builds</div>
                </div>
                <a href="/wiki:alt-builds.html" class="admin-stat-link">View →</a>
            </div>
        </div>

        {{-- Two column lower section --}}
        <div class="admin-lower-grid">

            {{-- Recent users --}}
            <section class="admin-panel">
                <div class="admin-panel-header">
                    <h2 class="admin-panel-title">Recent Users</h2>
                    <a href="{{ route('admin.users') }}" class="admin-panel-action">All users →</a>
                </div>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Role</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentUsers as $u)
                        <tr>
                            <td>
                                <div class="admin-user-cell">
                                    <img src="{{ $u->getAvatarURL() }}" class="admin-user-avatar" alt="">
                                    <a href="/u:{{ $u->username }}.html" class="admin-user-name">{{ $u->username }}</a>
                                </div>
                            </td>
                            <td>
                                @if($u->is_admin)
                                    <span class="admin-badge admin-badge--admin">Admin</span>
                                @else
                                    <span class="admin-badge">User</span>
                                @endif
                            </td>
                            <td class="admin-actions">
                                <a href="/u:{{ $u->username }}.html" class="btn-sm">Profile</a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </section>

            {{-- Recent glitches --}}
            <section class="admin-panel">
                <div class="admin-panel-header">
                    <h2 class="admin-panel-title">Recent Glitches</h2>
                    <a href="/gallery.html" class="admin-panel-action">Gallery →</a>
                </div>
                <div class="admin-glitch-list">
                    @foreach($recentGlitches as $g)
                    <div class="admin-glitch-row">
                        <img src="/front:{{ $g->id }}.png" class="admin-glitch-sprite" alt="">
                        <div class="admin-glitch-info">
                            <a href="/g:{{ $g->form }}:{{ $g->id }}.html" class="admin-glitch-name">
                                {{ $g->nickname ?? $g->form }}
                            </a>
                            <span class="admin-glitch-meta">❤ {{ $g->likes_count }}</span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </section>

        </div>
    </main>
</div>
@endsection
