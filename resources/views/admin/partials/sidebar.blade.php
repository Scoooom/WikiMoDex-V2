<aside class="admin-sidebar">
    <div class="admin-sidebar-header">
        <span class="admin-sidebar-title">⚙ Admin</span>
    </div>
    <nav class="admin-sidebar-nav">
        <a href="{{ route('admin.dashboard') }}"
           class="admin-sidebar-link {{ ($active ?? '') === 'dashboard' ? 'active' : '' }}">
            <span class="admin-sidebar-icon">🏠</span> Dashboard
        </a>
        <a href="{{ route('admin.users') }}"
           class="admin-sidebar-link {{ ($active ?? '') === 'users' ? 'active' : '' }}">
            <span class="admin-sidebar-icon">👥</span> Users
        </a>
        <div class="admin-sidebar-divider"></div>
        <span class="admin-sidebar-section-label">Wiki</span>
        <a href="{{ route('wiki.admin.index') }}"
           class="admin-sidebar-link {{ ($active ?? '') === 'wiki' ? 'active' : '' }}">
            <span class="admin-sidebar-icon">📄</span> Articles
        </a>
        <div class="admin-sidebar-divider"></div>
        <span class="admin-sidebar-section-label">Site</span>
        <a href="/" class="admin-sidebar-link">
            <span class="admin-sidebar-icon">↗</span> View Site
        </a>
    </nav>
</aside>
