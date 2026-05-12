@extends('layouts.app')

@section('content')
<div class="container mt-2" style="max-width:640px">

    <h1 class="mon-name mb-4" style="font-size:20px">Settings</h1>

    @if(session('success'))
    <div class="alert alert-success mb-4">{{ session('success') }}</div>
    @endif

    <form method="POST" action="/settings.html">
        @csrf

        {{-- Profile --}}
        <div class="card mb-4">
            <div class="card-header">Profile</div>
            <div class="card-body" style="display:flex;flex-direction:column;gap:16px">

                <div>
                    <label class="form-label">Display name</label>
                    <input type="text" name="display_name" class="form-control"
                           value="{{ old('display_name', $user->display_name) }}"
                           maxlength="64" placeholder="{{ $user->username }}">
                    <div style="font-size:11px;color:var(--muted);margin-top:4px">Shown instead of your username on your profile and trainer card. Max 64 characters.</div>
                </div>

                <div>
                    <label class="form-label">Pronouns</label>
                    <input type="text" name="pronouns" class="form-control"
                           value="{{ old('pronouns', $user->pronouns) }}"
                           maxlength="32" placeholder="e.g. they/them">
                </div>

                <div>
                    <label class="form-label">Bio</label>
                    <textarea name="bio" class="form-control" rows="3"
                              maxlength="300" placeholder="Tell the community a bit about yourself…">{{ old('bio', $user->bio) }}</textarea>
                    <div style="font-size:11px;color:var(--muted);margin-top:4px">Max 300 characters.</div>
                </div>

            </div>
        </div>

        {{-- Trainer Card --}}
        <div class="card mb-4">
            <div class="card-header">Trainer Card</div>
            <div class="card-body" style="display:flex;flex-direction:column;gap:20px">

                {{-- Color --}}
                <div>
                    <label class="form-label">Card colour</label>
                    <div style="display:flex;gap:10px;flex-wrap:wrap;margin-top:6px">
                        @foreach(['maroon','blue','red','green','gold','purple','black'] as $c)
                        @php
                            $colors = ['maroon'=>'#800000','blue'=>'#4a90c8','red'=>'#c84a4a','green'=>'#4a9a5a','gold'=>'#c8a030','purple'=>'#7a4ac8','black'=>'#444444'];
                        @endphp
                        <label style="display:flex;flex-direction:column;align-items:center;gap:4px;cursor:pointer">
                            <input type="radio" name="tc_color" value="{{ $c }}"
                                   {{ ($user->tc_color ?? 'maroon') === $c ? 'checked' : '' }}
                                   style="display:none" class="tc-radio">
                            <span class="tc-color-swatch {{ ($user->tc_color ?? 'maroon') === $c ? 'active' : '' }}"
                                  style="background:{{ $colors[$c] }};width:32px;height:32px;border-radius:50%;border:3px solid transparent;display:block;cursor:pointer"
                                  onclick="this.previousElementSibling.checked=true;document.querySelectorAll('.tc-color-swatch').forEach(s=>s.classList.remove('active'));this.classList.add('active')">
                            </span>
                            <span style="font-size:10px;color:var(--muted)">{{ ucfirst($c) }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>

                {{-- Favourite mon --}}
                @if(count($monOptions) > 0)
                <div>
                    <label class="form-label">Favourite mon <span style="color:var(--muted);font-size:11px">(shown on trainer card)</span></label>
                    <select name="tc_favorite_mon" class="form-control">
                        <option value="">— None —</option>
                        @foreach($monOptions as $mon)
                        <option value="{{ $mon['name'] }}" {{ $user->tc_favorite_mon === $mon['name'] ? 'selected' : '' }}>
                            {{ ucwords($mon['name']) }} ({{ ucfirst($mon['type']) }})
                        </option>
                        @endforeach
                    </select>
                </div>
                @else
                <div style="font-size:13px;color:var(--muted)">Upload a save file to unlock favourite mon selection.</div>
                @endif

                {{-- Sections --}}
                <div>
                    <label class="form-label">Visible sections</label>
                    <div style="display:flex;flex-direction:column;gap:8px;margin-top:6px">
                        @php $sections = $user->getTcSections(); @endphp
                        @foreach([
                            'rivals'    => 'Rivals defeated',
                            'core'      => 'Core glitch unlocks',
                            'mod'       => 'Mod glitch unlocks',
                            'smitty'    => 'SMITTY form unlocks',
                            'unismitty' => 'UniSMITTY form unlocks',
                            'submitted' => 'Submitted glitches count',
                        ] as $key => $label)
                        <label style="display:flex;align-items:center;gap:10px;cursor:pointer">
                            <input type="checkbox" name="tc_section_{{ $key }}"
                                   {{ $sections[$key] ? 'checked' : '' }}
                                   style="width:16px;height:16px">
                            <span style="font-size:13px">{{ $label }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>

            </div>
        </div>

        <button type="submit" class="btn btn-primary">Save settings</button>
    </form>

</div>

<style>
.tc-color-swatch.active { border-color: var(--accent) !important; box-shadow: 0 0 0 2px var(--accent); }
</style>
@endsection
