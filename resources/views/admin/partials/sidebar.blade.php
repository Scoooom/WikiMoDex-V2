<aside class="admin-sidebar">
    <div class="admin-sidebar-header">
        @if(auth()->user()->isAdmin())
            <span class="admin-sidebar-title">⚙ Admin</span>
        @else
            <span class="admin-sidebar-title">✏ Editor</span>
        @endif
    </div>
    <nav class="admin-sidebar-nav">
        <a href="{{ route('admin.dashboard') }}"
           class="admin-sidebar-link {{ ($active ?? '') === 'dashboard' ? 'active' : '' }}">
            <span class="admin-sidebar-icon">🏠</span> Dashboard
        </a>

        {{-- User management is admin-only --}}
        @if(auth()->user()->isAdmin())
            <a href="{{ route('admin.users') }}"
               class="admin-sidebar-link {{ ($active ?? '') === 'users' ? 'active' : '' }}">
                <span class="admin-sidebar-icon">👥</span> Users
            </a>
        @endif

        <div class="admin-sidebar-divider"></div>
        <span class="admin-sidebar-section-label">Wiki</span>
        <a href="{{ route('wiki.admin.index') }}"
           class="admin-sidebar-link {{ ($active ?? '') === 'wiki' ? 'active' : '' }}">
            <span class="admin-sidebar-icon">📄</span> Articles
        </a>
        <a href="{{ route('faq.admin.index') }}"
           class="admin-sidebar-link {{ ($active ?? '') === 'faq' ? 'active' : '' }}">
            <span class="admin-sidebar-icon">❓</span> FAQ
        </a>
        <div class="admin-sidebar-divider"></div>
        <span class="admin-sidebar-section-label">Site</span>
        <a href="/" class="admin-sidebar-link">
            <span class="admin-sidebar-icon">↗</span> View Site
        </a>
    </nav>
</aside>
