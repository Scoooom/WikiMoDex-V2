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
                                   autocomplete="off" data-typeahead="species">
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

                        {{-- Override Type 1 --}}
                        <div class="build-form-field">
                            <label>Override Type 1</label>
                            <select name="team[{{ $i }}][override_type1]" class="build-form-select">
                                <option value="">— None —</option>
                                @foreach(['Normal','Fighting','Flying','Poison','Ground','Rock','Bug','Ghost','Steel','Fire','Water','Grass','Electric','Psychic','Ice','Dragon','Dark','Fairy'] as $type)
                                <option value="{{ $type }}" {{ old("team.$i.override_type1") === $type ? 'selected' : '' }}>{{ $type }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Override Type 2 --}}
                        <div class="build-form-field">
                            <label>Override Type 2</label>
                            <select name="team[{{ $i }}][override_type2]" class="build-form-select">
                                <option value="">— None —</option>
                                @foreach(['Normal','Fighting','Flying','Poison','Ground','Rock','Bug','Ghost','Steel','Fire','Water','Grass','Electric','Psychic','Ice','Dragon','Dark','Fairy'] as $type)
                                <option value="{{ $type }}" {{ old("team.$i.override_type2") === $type ? 'selected' : '' }}>{{ $type }}</option>
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
    {!! json_encode($item->name) !!}: {!! json_encode($item->key) !!},
    @endforeach
};

const TYPES = ['Normal','Fighting','Flying','Poison','Ground','Rock','Bug','Ghost','Steel','Fire','Water','Grass','Electric','Psychic','Ice','Dragon','Dark','Fairy'];
const STATS = ['HP','ATK','DEF','SP.ATK','SP.DEF','SPD'];

const PARAM_ITEMS = {
    'STAT_SWITCHER':           { type: 'stat2', label1: 'Swap stat',        label2: 'With stat' },
    'STAT_SACRIFICE':          { type: 'stat1', label1: 'Stat sacrificed' },
    'TYPE_SWITCHER':           { type: 'type2', label1: 'Type 1',           label2: 'Type 2 (blank = keep existing)' },
    'PRIMARY_TYPE_SWITCHER':   { type: 'type1', label1: 'New primary type' },
    'SECONDARY_TYPE_SWITCHER': { type: 'type2secondary', label1: 'Type 1 (blank = keep)', label2: 'New secondary type' },
    // TYPE_SACRIFICE and POKEMON_ALT_BUILD have no params — override type fields on the slot handle typing
};

function buildParamHtml(base, cfg, params) {
    if (!cfg) return '';
    let html = '<div class="build-item-params">';
    if (cfg.type.startsWith('type')) {
        const t1 = (params && params.type1) || '';
        const t2 = (params && params.type2) || '';
        const blankLabel1 = cfg.type === 'type2secondary' ? '— Keep existing —' : '— Default —';
        const typeOpts1 = `<option value="">${blankLabel1}</option>` + TYPES.map(t => `<option value="${t}"${t1===t?' selected':''}>${t}</option>`).join('');
        html += `<div class="build-item-param-row"><label class="build-item-param-label">${cfg.label1}</label><select name="${base}[type1]" class="build-form-select build-item-param-input">${typeOpts1}</select></div>`;
        if (cfg.type === 'type2' || cfg.type === 'type2secondary') {
            const typeOpts2 = '<option value="">— Keep existing —</option>' + TYPES.map(t => `<option value="${t}"${t2===t?' selected':''}>${t}</option>`).join('');
            html += `<div class="build-item-param-row"><label class="build-item-param-label">${cfg.label2}</label><select name="${base}[type2]" class="build-form-select build-item-param-input">${typeOpts2}</select></div>`;
        }
    } else if (cfg.type.startsWith('stat')) {
        const s1 = (params && params.stat1) || '';
        const s2 = (params && params.stat2) || '';
        const statOpts1 = '<option value="">— Any —</option>' + STATS.map(s => `<option value="${s}"${s1===s?' selected':''}>${s}</option>`).join('');
        html += `<div class="build-item-param-row"><label class="build-item-param-label">${cfg.label1}</label><select name="${base}[stat1]" class="build-form-select build-item-param-input">${statOpts1}</select></div>`;
        if (cfg.type === 'stat2') {
            const statOpts2 = '<option value="">— Any —</option>' + STATS.map(s => `<option value="${s}"${s2===s?' selected':''}>${s}</option>`).join('');
            html += `<div class="build-item-param-row"><label class="build-item-param-label">${cfg.label2}</label><select name="${base}[stat2]" class="build-form-select build-item-param-input">${statOpts2}</select></div>`;
        }
    } else if (cfg.type === 'ability') {
        const ab = (params && params.ability) || '';
        html += `<div class="build-item-param-row"><label class="build-item-param-label">${cfg.label1}</label><input type="text" name="${base}[ability]" value="${ab}" class="build-form-input build-item-param-input" list="abilityOptions" placeholder="Ability name" autocomplete="off"></div>`;
    }
    html += '</div>';
    return html;
}

function addItemRow(slot, nameVal, keyVal, stackVal, params) {
    nameVal  = nameVal  || '';
    keyVal   = keyVal   || '';
    stackVal = stackVal || 1;
    params   = params   || {};

    const list = document.getElementById('itemsList' + slot);
    const idx  = list.querySelectorAll('.build-item-row').length;
    const base = `team[${slot}][items][${idx}]`;

    const row = document.createElement('div');
    row.className = 'build-item-row';

    function render(key, p) {
        const cfg     = PARAM_ITEMS[key];
        const paramBase = `${base}[params]`;
        const paramHtml = buildParamHtml(paramBase, cfg, p);
        row.innerHTML = `
            <div class="build-item-row-top">
                <input type="text" name="${base}[name]" value="${nameVal}"
                       class="build-form-input build-item-name-input"
                       placeholder="Item name" list="itemOptions" autocomplete="off">
                <input type="hidden" name="${base}[key]" class="build-item-key-input" value="${key}">
                <input type="number" name="${base}[stack]" value="${stackVal}"
                       class="build-form-input build-item-stack-input" min="1" max="99" placeholder="x">
                <button type="button" class="build-item-remove-btn" onclick="this.closest('.build-item-row').remove()">✕</button>
            </div>
            ${paramHtml}
        `;
        row.querySelector('.build-item-name-input').addEventListener('input', function() {
            const newKey = (ITEM_MAP[this.value] !== undefined) ? ITEM_MAP[this.value] : '';
            render(newKey, {});
        });
    }

    render(keyVal, params);
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
addItemRow({!! $i !!}, {!! json_encode($item['name'] ?? '') !!}, {!! json_encode($item['key'] ?? '') !!}, {!! $item['stack'] ?? 1 !!});
@endforeach
@endif
@endforeach
@endif


// ── Species typeahead ─────────────────────────────────────────────
(function initSpeciesTypeahead() {
    const CATEGORY_COLORS = {
        'Official':       'var(--dim)',
        'Core Glitch':    '#ffaaa5',
        'SMITTY Pokémon': '#a8e6cf',
        'SMITTY Form':    '#a8e6cf',
        'Mod Glitch':     '#c9a8ff',
        'Alt Build':      '#f5d76e',
    };

    document.querySelectorAll('input[data-typeahead="species"]').forEach(input => {
        const slot = input.dataset.slot;
        const dexInput = document.querySelector(`.build-dex-input[data-slot="${slot}"]`);
        const ab1Input = input.closest('.build-slot-editor').querySelector('[name$="[ability]"]');
        const ab2Input = input.closest('.build-slot-editor').querySelector('[name$="[passive_ability]"]');

        let dropdown = null;
        let debounce = null;
        let activeIdx = -1;
        let lastResults = [];

        function createDropdown() {
            dropdown = document.createElement('div');
            dropdown.className = 'pokemon-typeahead-dropdown';
            input.parentNode.style.position = 'relative';
            input.parentNode.appendChild(dropdown);
        }

        function closeDropdown() {
            if (dropdown) { dropdown.innerHTML = ''; dropdown.style.display = 'none'; }
            activeIdx = -1;
        }

        function renderDropdown(results) {
            lastResults = results;
            if (!results.length) { closeDropdown(); return; }
            dropdown.innerHTML = '';
            dropdown.style.display = 'block';
            results.slice(0, 12).forEach((r, i) => {
                const item = document.createElement('div');
                item.className = 'pokemon-typeahead-item';
                item.dataset.idx = i;
                const color = CATEGORY_COLORS[r.category] || 'var(--dim)';
                item.innerHTML = `
                    <span class="pokemon-typeahead-name">${r.label}</span>
                    <span class="pokemon-typeahead-cat" style="color:${color}">${r.category}</span>
                `;
                item.addEventListener('mousedown', (e) => {
                    e.preventDefault();
                    selectResult(r);
                });
                dropdown.appendChild(item);
            });
            activeIdx = -1;
        }

        function selectResult(r) {
            input.value = r.value;
            if (dexInput) dexInput.value = (r.dex !== undefined && r.dex !== null) ? r.dex : '';
            // Auto-fill abilities if fields are empty
            if (ab1Input && !ab1Input.value && r.ability1) ab1Input.value = r.ability1;
            if (ab2Input && !ab2Input.value && r.abilityH) ab2Input.value = r.abilityH;
            closeDropdown();
        }

        input.addEventListener('input', () => {
            clearTimeout(debounce);
            const q = input.value.trim();
            if (q.length < 2) { closeDropdown(); return; }
            debounce = setTimeout(() => {
                fetch(`/pokemon-search.json?q=${encodeURIComponent(q)}`)
                    .then(r => r.json())
                    .then(renderDropdown)
                    .catch(() => closeDropdown());
            }, 180);
        });

        input.addEventListener('keydown', (e) => {
            const items = dropdown?.querySelectorAll('.pokemon-typeahead-item');
            if (!items?.length) return;
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                activeIdx = Math.min(activeIdx + 1, items.length - 1);
                items.forEach((el, i) => el.classList.toggle('active', i === activeIdx));
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                activeIdx = Math.max(activeIdx - 1, 0);
                items.forEach((el, i) => el.classList.toggle('active', i === activeIdx));
            } else if (e.key === 'Enter' && activeIdx >= 0) {
                e.preventDefault();
                selectResult(lastResults[activeIdx]);
            } else if (e.key === 'Escape') {
                closeDropdown();
            }
        });

        input.addEventListener('blur', () => setTimeout(closeDropdown, 150));

        createDropdown();
    });
})();
</script>
