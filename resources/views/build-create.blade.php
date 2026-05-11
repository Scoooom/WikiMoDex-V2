@extends('layouts.app')

@section('title', 'Submit a Build')

@section('content')
<div class="wiki-page">
    <div class="wiki-page-header">
        <div>
            <a href="/builds.html" class="build-back-link">← All Builds</a>
            <h1 class="wiki-page-title">Submit a Build</h1>
        </div>
    </div>

    @if($errors->any())
    <div class="flash-error">
        @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
    </div>
    @endif

    <form method="POST" action="/builds" id="buildForm">
        @csrf

        {{-- Title + description --}}
        <div class="build-form-section">
            <label class="build-form-label" for="title">Build Title <span class="req">*</span></label>
            <input type="text" id="title" name="title" value="{{ old('title') }}"
                   class="build-form-input" placeholder="e.g. Sun Hyper Offense" maxlength="80" required>

            <label class="build-form-label" for="description" style="margin-top:1rem">Description / Strategy Notes</label>
            <textarea id="description" name="description" class="build-form-textarea"
                      placeholder="Optional — describe your overall strategy, win conditions, threats, etc."
                      maxlength="1000" rows="3">{{ old('description') }}</textarea>
        </div>

        {{-- Team slots --}}
        <div class="build-form-section">
            <div class="build-form-section-title">Team (up to 6 Pokémon)</div>
            <div class="build-slots-editor" id="buildSlots">
                @for($i = 0; $i < 6; $i++)
                <div class="build-slot-editor" data-slot="{{ $i }}">
                    <div class="build-slot-editor-header">
                        <span class="build-slot-editor-num">Slot {{ $i + 1 }}</span>
                        <button type="button" class="build-slot-clear-btn" onclick="clearSlot({{ $i }})">Clear</button>
                    </div>

                    <div class="build-slot-editor-grid">
                        {{-- Species --}}
                        <div class="build-form-field">
                            <label>Species</label>
                            <input type="text" name="team[{{ $i }}][species]"
                                   value="{{ old("team.$i.species") }}"
                                   class="build-form-input build-species-input"
                                   placeholder="e.g. Charizard"
                                   data-slot="{{ $i }}"
                                   list="speciesOptions"
                                   autocomplete="off">
                            <input type="hidden" name="team[{{ $i }}][dex_number]"
                                   class="build-dex-input" data-slot="{{ $i }}">
                        </div>

                        {{-- Ability --}}
                        <div class="build-form-field">
                            <label>Ability</label>
                            <input type="text" name="team[{{ $i }}][ability]"
                                   value="{{ old("team.$i.ability") }}"
                                   class="build-form-input" placeholder="e.g. Drought"
                                   list="abilityOptions" autocomplete="off">
                        </div>

                        {{-- Passive --}}
                        <div class="build-form-field">
                            <label>Passive Ability</label>
                            <input type="text" name="team[{{ $i }}][passive_ability]"
                                   value="{{ old("team.$i.passive_ability") }}"
                                   class="build-form-input" placeholder="e.g. Flash Fire"
                                   list="abilityOptions" autocomplete="off">
                        </div>

                        {{-- Nature --}}
                        <div class="build-form-field">
                            <label>Nature</label>
                            <select name="team[{{ $i }}][nature]" class="build-form-select">
                                <option value="">— Any —</option>
                                @foreach(['Hardy','Lonely','Brave','Adamant','Naughty','Bold','Docile','Relaxed','Impish','Lax','Timid','Hasty','Serious','Jolly','Naive','Modest','Mild','Quiet','Bashful','Rash','Calm','Gentle','Sassy','Careful','Quirky'] as $nature)
                                <option value="{{ $nature }}" {{ old("team.$i.nature") === $nature ? 'selected' : '' }}>{{ $nature }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Moves --}}
                    <div class="build-form-subsection">
                        <div class="build-form-sublabel">Moves (up to 4)</div>
                        <div class="build-moves-row">
                            @for($m = 0; $m < 4; $m++)
                            <input type="text" name="team[{{ $i }}][moves][{{ $m }}]"
                                   value="{{ old("team.$i.moves.$m") }}"
                                   class="build-form-input build-move-input"
                                   placeholder="Move {{ $m + 1 }}">
                            @endfor
                        </div>
                    </div>

                    {{-- Items --}}
                    <div class="build-form-subsection">
                        <div class="build-form-sublabel">Held Items</div>
                        <div class="build-items-list" id="itemsList{{ $i }}">
                            {{-- Rows added dynamically --}}
                        </div>
                        <button type="button" class="build-add-item-btn" onclick="addItemRow({{ $i }})">+ Add Item</button>
                    </div>

                    {{-- Slot notes --}}
                    <div class="build-form-subsection">
                        <label class="build-form-sublabel">Slot Notes</label>
                        <textarea name="team[{{ $i }}][notes]" class="build-form-textarea build-slot-notes-input"
                                  placeholder="Role, tips, alternatives…" rows="2"
                                  maxlength="500">{{ old("team.$i.notes") }}</textarea>
                    </div>
                </div>
                @endfor
            </div>
        </div>

        <div class="build-form-submit-row">
            <button type="submit" class="btn-primary">Submit Build</button>
            <a href="/builds.html" class="btn-secondary">Cancel</a>
        </div>
    </form>
</div>

{{-- Species datalist --}}
<datalist id="speciesOptions">
    @foreach($species as $s)
    <option value="{{ $s }}">
    @endforeach
</datalist>

{{-- Ability datalist --}}
<datalist id="abilityOptions">
    @foreach($abilities as $a)
    <option value="{{ $a }}">
    @endforeach
</datalist>

{{-- Item select datalist --}}
<datalist id="itemOptions">
    @foreach($items as $item)
    <option value="{{ $item->name }}" data-key="{{ $item->key }}" data-tier="{{ $item->tier }}">
    @endforeach
</datalist>

<script>
// Build a lookup map from item name → key
const ITEM_MAP = {
    @foreach($items as $item)
    {{ json_encode($item->name) }}: {{ json_encode($item->key) }},
    @endforeach
};

function addItemRow(slot, nameVal = '', keyVal = '', stackVal = 1) {
    const list  = document.getElementById('itemsList' + slot);
    const idx   = list.querySelectorAll('.build-item-row').length;
    const nameField = `team[${slot}][items][${idx}][name]`;
    const keyField  = `team[${slot}][items][${idx}][key]`;
    const stackField = `team[${slot}][items][${idx}][stack]`;

    const row = document.createElement('div');
    row.className = 'build-item-row';
    row.innerHTML = `
        <input type="text" name="${nameField}" value="${nameVal}"
               class="build-form-input build-item-name-input"
               placeholder="Item name" list="itemOptions" autocomplete="off">
        <input type="hidden" name="${keyField}" class="build-item-key-input" value="${keyVal}">
        <input type="number" name="${stackField}" value="${stackVal}"
               class="build-form-input build-item-stack-input"
               min="1" max="99" placeholder="Stack">
        <button type="button" class="build-item-remove-btn" onclick="this.closest('.build-item-row').remove()">✕</button>
    `;

    // Auto-fill key when name matches
    row.querySelector('.build-item-name-input').addEventListener('input', function() {
        const key = ITEM_MAP[this.value] ?? '';
        row.querySelector('.build-item-key-input').value = key;
    });

    list.appendChild(row);
}

function clearSlot(slot) {
    const el = document.querySelector(`.build-slot-editor[data-slot="${slot}"]`);
    el.querySelectorAll('input[type=text], input[type=number], textarea').forEach(i => i.value = '');
    el.querySelectorAll('select').forEach(s => s.selectedIndex = 0);
    el.querySelectorAll('input[type=hidden]').forEach(i => i.value = '');
    document.getElementById('itemsList' + slot).innerHTML = '';
}

// Restore old() item data if validation failed
@if(old('team'))
@foreach(old('team', []) as $i => $slot)
@if(!empty($slot['items']))
@foreach($slot['items'] as $item)
addItemRow({{ $i }}, {{ json_encode($item['name'] ?? '') }}, {{ json_encode($item['key'] ?? '') }}, {{ $item['stack'] ?? 1 }});
@endforeach
@endif
@endforeach
@endif
</script>
