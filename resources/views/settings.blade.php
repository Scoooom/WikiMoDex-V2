@extends('layouts.app')
@section('title', 'Settings')

@section('content')
<div class="container mt-2" style="max-width:600px">

    <h1 class="mon-name mb-4" style="font-size:20px">Settings</h1>

    @if(session('success'))
    <div class="alert alert-success mb-4">{{ session('success') }}</div>
    @endif

    <form method="POST" action="/settings.html">
        @csrf

        {{-- Profile --}}
        <div class="card mb-4">
            <div class="card-header">Profile</div>
            <div class="card-body" style="display:flex;flex-direction:column;gap:14px">

                <div class="settings-field">
                    <label class="settings-label">Display name</label>
                    <input type="text" name="display_name" class="form-input"
                           value="{{ old('display_name', $user->display_name) }}"
                           maxlength="64" placeholder="{{ $user->username }}">
                    <div class="settings-hint">Shown instead of your username sitewide. Max 64 characters.</div>
                </div>

                <div class="settings-field">
                    <label class="settings-label">Pronouns</label>
                    <input type="text" name="pronouns" class="form-input"
                           value="{{ old('pronouns', $user->pronouns) }}"
                           maxlength="32" placeholder="e.g. they/them">
                </div>

                <div class="settings-field">
                    <label class="settings-label">Bio</label>
                    <textarea name="bio" class="form-textarea" rows="3"
                              maxlength="300" placeholder="Tell the community a bit about yourself…">{{ old('bio', $user->bio) }}</textarea>
                    <div class="settings-hint">Max 300 characters.</div>
                </div>

            </div>
        </div>

        {{-- Trainer Card --}}
        <div class="card mb-4">
            <div class="card-header">Trainer Card</div>
            <div class="card-body" style="display:flex;flex-direction:column;gap:18px">

                {{-- Type (overrides colour) --}}
                @php
                    $currentType = $user->tc_type ?? '';
                    $typeMap = [
                        'normal'=>'#A8A77A','fire'=>'#EE8130','water'=>'#6390F0','electric'=>'#F7D02C',
                        'grass'=>'#7AC74C','ice'=>'#96D9D6','fighting'=>'#C22E28','poison'=>'#A33EA1',
                        'ground'=>'#E2BF65','flying'=>'#A98FF3','psychic'=>'#F95587','bug'=>'#A6B91A',
                        'rock'=>'#B6A136','ghost'=>'#735797','dragon'=>'#6F35FC','dark'=>'#705746',
                        'steel'=>'#B7B7CE','fairy'=>'#D685AD',
                    ];
                @endphp
                <div class="settings-field" id="tc-type-field">
                    <label class="settings-label">Card type <span class="settings-hint-inline">(overrides colour when set)</span></label>
                    <div style="display:flex;gap:10px;flex-wrap:wrap;margin-top:6px;align-items:center">
                        {{-- None option --}}
                        <label class="settings-color-option" id="tc-type-none-label">
                            <input type="radio" name="tc_type" id="tc-type-none" value=""
                                   {{ $currentType === '' ? 'checked' : '' }}>
                            <span class="settings-color-swatch {{ $currentType === '' ? 'active' : '' }}"
                                  style="background:var(--border);color:var(--muted);font-size:10px;display:flex;align-items:center;justify-content:center">✕</span>
                            <span class="settings-color-label">None</span>
                        </label>
                        @foreach($typeMap as $t => $hex)
                        <label class="settings-color-option">
                            <input type="radio" name="tc_type" value="{{ $t }}"
                                   {{ $currentType === $t ? 'checked' : '' }}>
                            <span class="settings-color-swatch {{ $currentType === $t ? 'active' : '' }}"
                                  style="background:{{ $hex }};position:relative;overflow:hidden">
                                <img src="https://raw.githubusercontent.com/duiker101/pokemon-type-svg-icons/master/icons/{{ $t }}.svg"
                                     style="width:22px;height:22px;object-fit:contain;display:block;margin:0 auto"
                                     alt="{{ $t }}" onerror="this.style.display='none'">
                            </span>
                            <span class="settings-color-label">{{ ucfirst($t) }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>

                {{-- Color (hidden when type is active) --}}
                <div class="settings-field" id="tc-color-field" style="{{ $currentType ? 'display:none' : '' }}">
                    <label class="settings-label">Card colour</label>
                    <div style="display:flex;gap:12px;flex-wrap:wrap;margin-top:6px">
                        @php
                            $colorMap = ['maroon'=>'#800000','blue'=>'#4a90c8','red'=>'#c84a4a','green'=>'#4a9a5a','gold'=>'#c8a030','purple'=>'#7a4ac8','black'=>'#444444'];
                            $currentColor = $user->tc_color ?? 'maroon';
                        @endphp
                        @foreach($colorMap as $c => $hex)
                        <label class="settings-color-option">
                            <input type="radio" name="tc_color" value="{{ $c }}"
                                   {{ $currentColor === $c ? 'checked' : '' }}>
                            <span class="settings-color-swatch {{ $currentColor === $c ? 'active' : '' }}"
                                  style="background:{{ $hex }}"></span>
                            <span class="settings-color-label">{{ ucfirst($c) }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>

                {{-- Favourite mon --}}
                <div class="settings-field">
                    <label class="settings-label">Favourite mon <span class="settings-hint-inline">(shown on trainer card)</span></label>
                    @if(count($monOptions) > 0)
                    <select name="tc_favorite_mon" class="form-select">
                        <option value="">— None —</option>
                        @foreach($monOptions as $mon)
                        <option value="{{ $mon['name'] }}" {{ $user->tc_favorite_mon === $mon['name'] ? 'selected' : '' }}>
                            {{ ucwords($mon['name']) }} ({{ ucfirst($mon['type']) }})
                        </option>
                        @endforeach
                    </select>
                    @else
                    <div class="settings-hint">Upload a save file to unlock favourite mon selection.</div>
                    @endif
                </div>

                {{-- Sections --}}
                <div class="settings-field">
                    <label class="settings-label">Visible sections</label>
                    <div style="display:flex;flex-direction:column;gap:6px;margin-top:6px">
                        @php $sections = $user->getTcSections(); @endphp
                        @foreach([
                            'rivals'    => 'Rivals defeated',
                            'core'      => 'Core glitch unlocks',
                            'mod'       => 'Mod glitch unlocks',
                            'smitty'    => 'SMITTY form unlocks',
                            'unismitty' => 'UniSMITTY form unlocks',
                            'submitted' => 'Submitted glitches count',
                        ] as $key => $label)
                        <label class="settings-toggle">
                            <input type="checkbox" name="tc_section_{{ $key }}"
                                   {{ $sections[$key] ? 'checked' : '' }}>
                            <span>{{ $label }}</span>
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
.settings-field { display:flex; flex-direction:column; gap:6px; }
.settings-label { font-size:13px; font-weight:600; color:var(--text); }
.settings-hint  { font-size:11px; color:var(--muted); }
.settings-hint-inline { font-size:11px; color:var(--muted); font-weight:400; }

.settings-color-option {
    display:flex; flex-direction:column; align-items:center; gap:5px;
    cursor:pointer;
}
.settings-color-option input { display:none; }
.settings-color-swatch {
    width:32px; height:32px; border-radius:50%;
    border:3px solid transparent;
    transition:border-color .15s, box-shadow .15s;
    cursor:pointer;
}
.settings-color-swatch.active,
.settings-color-option input:checked + .settings-color-swatch {
    border-color:var(--accent);
    box-shadow:0 0 0 2px var(--accent);
}
.settings-color-label { font-size:10px; color:var(--muted); }

.settings-toggle {
    display:flex; align-items:center; gap:10px;
    cursor:pointer; font-size:13px; color:var(--text);
    padding:6px 0;
    border-bottom:1px solid var(--border);
}
.settings-toggle:last-child { border-bottom:none; }
.settings-toggle input[type=checkbox] { width:16px; height:16px; accent-color:var(--accent); cursor:pointer; }
</style>

<script>
// Activate swatch on radio change
document.querySelectorAll('.settings-color-option input').forEach(radio => {
    radio.addEventListener('change', () => {
        // Only deactivate swatches within the same field group
        const field = radio.closest('.settings-field');
        field.querySelectorAll('.settings-color-swatch').forEach(s => s.classList.remove('active'));
        radio.nextElementSibling.classList.add('active');
    });
});

// Type <-> Color mutual exclusion
const typeRadios = document.querySelectorAll('input[name="tc_type"]');
const colorField = document.getElementById('tc-color-field');

typeRadios.forEach(radio => {
    radio.addEventListener('change', () => {
        const hasType = radio.value !== '';
        colorField.style.display = hasType ? 'none' : '';
    });
});
</script>
@endsection
