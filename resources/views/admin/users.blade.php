@extends('layouts.app')

@section('title', 'Admin — Users')

@section('content')
<div class="admin-shell">

    @include('admin.partials.sidebar', ['active' => 'users'])

    <main class="admin-main">
        <div class="admin-page-header">
            <h1 class="admin-page-title">Users</h1>
            <span class="admin-page-sub">{{ $users->total() }} total</span>
        </div>

        @if(session('success'))
            <div class="flash-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="flash-error">{{ session('error') }}</div>
        @endif

        {{-- Search / filter --}}
        <form method="GET" action="{{ route('admin.users') }}" class="admin-filter-bar">
            <input type="text" name="q" value="{{ request('q') }}"
                   placeholder="Search username…" class="editor-input admin-filter-input">
            <label class="admin-filter-check">
                <input type="checkbox" name="admins_only" value="1"
                       {{ request('admins_only') ? 'checked' : '' }}
                       onchange="this.form.submit()">
                Admins only
            </label>
            <label class="admin-filter-check">
                <input type="checkbox" name="editors_only" value="1"
                       {{ request('editors_only') ? 'checked' : '' }}
                       onchange="this.form.submit()">
                Editors only
            </label>
            <button type="submit" class="btn-accent">Search</button>
            @if(request('q') || request('admins_only') || request('editors_only'))
                <a href="{{ route('admin.users') }}" class="btn-sm">Clear</a>
            @endif
        </form>

        <table class="admin-table">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Discord ID</th>
                    <th>Role</th>
                    <th>2FA</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $u)
                <tr>
                    <td>
                        <div class="admin-user-cell">
                            <img src="{{ $u->getAvatarURL() }}" class="admin-user-avatar" alt="">
                            <a href="/u:{{ $u->username }}.html" class="admin-user-name">{{ $u->username }}</a>
                        </div>
                    </td>
                    <td><code>{{ $u->user_id }}</code></td>
                    <td class="admin-role-cell">
                        @if($u->is_admin)
                            <span class="admin-badge admin-badge--admin">Admin</span>
                        @elseif($u->is_wiki_editor)
                            <span class="admin-badge admin-badge--editor">Editor</span>
                        @else
                            <span class="admin-badge">User</span>
                        @endif
                    </td>
                    <td>
                        @if($u->mfa_enabled)
                            <span class="admin-mfa-status admin-mfa-status--on" title="2FA enabled">✓</span>
                        @else
                            <span class="admin-mfa-status admin-mfa-status--off" title="2FA not enabled">✗</span>
                        @endif
                    </td>
                    <td class="admin-actions">
                        <a href="/u:{{ $u->username }}.html" class="btn-sm">Profile</a>

                        @if($u->id !== auth()->id())
                            {{-- Admin toggle (full admins only) --}}
                            <form method="POST" action="{{ route('admin.users.toggle', $u) }}" style="display:contents">
                                @csrf
                                <button type="submit" class="btn-sm {{ $u->is_admin ? 'btn-danger' : 'btn-primary' }}"
                                        onclick="return confirm('{{ $u->is_admin ? 'Revoke admin from' : 'Grant admin to' }} {{ addslashes($u->username) }}?')">
                                    {{ $u->is_admin ? 'Revoke Admin' : 'Make Admin' }}
                                </button>
                            </form>

                            {{-- Editor toggle (hidden for admins — they already have full access) --}}
                            @if(!$u->is_admin)
                                <form method="POST" action="{{ route('admin.users.toggle-editor', $u) }}" style="display:contents">
                                    @csrf
                                    <button type="submit" class="btn-sm {{ $u->is_wiki_editor ? 'btn-danger' : '' }}"
                                            onclick="return confirm('{{ $u->is_wiki_editor ? 'Revoke editor from' : 'Grant editor to' }} {{ addslashes($u->username) }}?')">
                                        {{ $u->is_wiki_editor ? 'Revoke Editor' : 'Make Editor' }}
                                    </button>
                                </form>
                            @endif

                            <form method="POST" action="{{ route('admin.users.delete', $u) }}" style="display:contents">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn-sm btn-danger"
                                        onclick="return confirm('Delete {{ addslashes($u->username) }}? This cannot be undone.')">
                                    Delete
                                </button>
                            </form>
                        @else
                            <span class="admin-badge" style="opacity:.5">You</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="admin-pagination">
            {{ $users->links() }}
        </div>
    </main>
</div>
@endsection
