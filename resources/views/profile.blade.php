@extends('layouts.app')

@section('content')
<div class="container">

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="profile-grid mt-2">

        {{-- Sidebar --}}
        <div>
            <div class="card mb-3">
                <div class="card-body" style="text-align:center">
                    <img src="{{ $user->getAvatarURL() }}" class="profile-avatar mb-3" alt="{{ $user->username }}">
                    <div class="mon-name" style="font-size:18px">{{ $user->getDisplayName() }}</div>
                    @if($user->username !== $user->getDisplayName())
                    <div style="font-size:11px;color:var(--muted);margin-top:2px">@{{ $user->username }}</div>
                    @endif
                    @if($user->pronouns)
                    <div style="font-size:12px;color:var(--muted);margin-top:2px">{{ $user->pronouns }}</div>
                    @endif
                    <div style="font-size:12px;color:var(--muted);margin-top:4px">
                        Joined {{ date('F Y', $user->join_date) }}
                    </div>
                    @if($user->bio)
                    <div style="font-size:13px;color:var(--text);margin-top:10px;line-height:1.5;text-align:left">{{ $user->bio }}</div>
                    @endif

                    @if($isOwner ?? false)
                    <div style="margin-top:12px;display:flex;gap:8px;justify-content:center;flex-wrap:wrap">
                        <a href="/settings.html" class="btn btn-secondary btn-sm">⚙ Settings</a>
                    </div>
                    @endif

                    @auth
                    <div style="margin-top:14px;display:flex;gap:8px;justify-content:center;flex-wrap:wrap">
                        @if(Auth::user()->likesUser($user->id))
                        <form action="/uRLike:{{ $user->id }}.html" method="post">
                            @csrf
                            <input type="hidden" name="returnURL" value="{{ url()->current() }}">
                            <button type="submit" class="btn btn-success btn-sm">♥ Liked</button>
                        </form>
                        @else
                        <form action="/uLike:{{ $user->id }}.html" method="post">
                            @csrf
                            <input type="hidden" name="returnURL" value="{{ url()->current() }}">
                            <button type="submit" class="btn btn-secondary btn-sm">♡ Like</button>
                        </form>
                        @endif
                    </div>
                    @endauth

                    @if($user->b64_prsv && $user->raw_prsv)
                    <div style="margin-top:10px">
                        <a href="/trainercard:{{ $user->username }}.html" class="btn btn-info btn-sm">Trainer Card</a>
                    </div>
                    @endif
                </div>
            </div>

            @if($isOwner ?? false)
            <div class="card mb-3">
                <div class="card-header">Save file</div>
                <div class="card-body">
                    <form action="/u:{{ $user->username }}.html" method="post" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="action" value="uploadNew">
                        <input type="file" name="saveFile" class="form-input mb-2" style="padding:6px">
                        <button type="submit" class="btn btn-primary btn-sm" style="width:100%">
                            {{ ($user->b64_prsv && $user->raw_prsv) ? 'Update save file' : 'Upload save file' }}
                        </button>
                    </form>
                    @if($user->b64_prsv && $user->raw_prsv)
                    <div style="display:flex;gap:8px;margin-top:8px">
                        <form action="/u:{{ $user->username }}.html" method="post" style="flex:1">
                            @csrf
                            <input type="hidden" name="action" value="dlSave">
                            <button type="submit" class="btn btn-secondary btn-sm" style="width:100%">Download</button>
                        </form>
                        <form action="/u:{{ $user->username }}.html" method="post" style="flex:1">
                            @csrf
                            <input type="hidden" name="action" value="delSave">
                            <button type="submit" class="btn btn-danger btn-sm" style="width:100%">Delete</button>
                        </form>
                    </div>
                    @endif
                </div>
            </div>
            @endif
        </div>

        {{-- Main --}}
        <div>
            <div class="card mb-3">
                <div class="card-header">Trainer info</div>
                <div class="card-body" style="padding:0">
                    <table class="info-table" style="margin:0">
                        <tr>
                            <td style="padding:12px 20px">Discord</td>
                            <td style="padding:12px 20px">{{ $user->username }}</td>
                        </tr>
                        <tr>
                            <td style="padding:12px 20px">Likes received</td>
                            <td style="padding:12px 20px">{{ $likes }}</td>
                        </tr>
                        <tr>
                            <td style="padding:12px 20px">Uploaded glitches</td>
                            <td style="padding:12px 20px">{{ $user->getUploadCount() ?: '0' }}</td>
                        </tr>
                        <tr>
                            <td style="padding:12px 20px">Last login</td>
                            <td style="padding:12px 20px">{{ date('F j, Y', $user->last_login) }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            @if($glitches->count() > 0)
            <div class="section-header mb-3">
                <span class="section-title">Uploaded glitch forms</span>
            </div>
            <div class="gallery-wrap">
                <div class="gallery-toolbar">
                    <input type="text" id="gallery-search" class="gallery-search" placeholder="Search glitches…">
                </div>
                <div style="overflow-x:auto">
                <table class="gallery-table">
                    <thead>
                        <tr>
                            <th style="width:64px">Sprite</th>
                            <th data-col="1">Name</th>
                            <th data-col="2">Base Pokémon</th>
                            <th>Types</th>
                            <th data-col="4">Rating</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($glitches as $glitch)
                            @php
                                $mon2 = $glitch->getJsonData();
                                $ogMon = $glitch->getOGMon();
                                $ogName = ucwords(str_replace('-', ' ', $ogMon->name));
                            @endphp
                            <tr data-search="{{ strtolower($glitch->name . ' ' . $ogName) }}">
                                <td>
                                    <img src="/front:{{ $glitch->id }}.png" class="sprite-sm" alt="{{ $glitch->name }}">
                                </td>
                                <td data-sort="{{ $glitch->name }}"><strong>{{ $glitch->name }}</strong></td>
                                <td data-sort="{{ $ogName }}">{{ $ogName }}</td>
                                <td>
                                    <div style="display:flex;gap:4px;flex-wrap:wrap">
                                        <span class="type-badge type-{{ $mon2->primaryType }}">
                                            {{ \App\Services\PokemonService::getTypeName($mon2->primaryType) }}
                                        </span>
                                        @if(isset($mon2->secondaryType) && $mon2->secondaryType !== $mon2->primaryType)
                                        <span class="type-badge type-{{ $mon2->secondaryType }}">
                                            {{ \App\Services\PokemonService::getTypeName($mon2->secondaryType) }}
                                        </span>
                                        @endif
                                    </div>
                                </td>
                                <td data-sort="{{ $glitch->getRating() }}">
                                    <span style="color:var(--accent2);font-weight:600">♥ {{ $glitch->getRating() }}</span>
                                </td>
                                <td>
                                    <a href="/g:{{ urlencode(str_replace(' ', '', $glitch->name)) }}:{{ $glitch->id }}.html"
                                        class="btn btn-secondary btn-sm">View</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                </div>
                <div class="pagination">
                    <span class="pagination-info" id="pag-info"></span>
                    <div class="pagination-btns" id="pag-btns"></div>
                </div>
            </div>
            @include('partials.gallery-js')
            @endif
        </div>

    </div>
</div>
@endsection
