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
            <div class="build-slots-editor" id="buildSlots"></div>
            <div class="build-slot-actions">
                <button type="button" class="build-add-slot-btn" id="addSlotBtn" onclick="addSlot()">+ Add Pokémon</button>
                <span class="build-slot-count" id="slotCount">1 / 6</span>
            </div>
        </div>

        <div class="build-form-submit-row">
            <button type="submit" class="btn-primary">Submit Build</button>
            <a href="/builds.html" class="btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<script>
const ITEM_MAP = {
    @foreach($items as $item)
    {!! json_encode($item->name) !!}: {!! json_encode($item->key) !!},
    @endforeach
};
const ITEM_LIST = [
    @foreach($items as $item)
    { label: {!! json_encode($item->name) !!}, value: {!! json_encode($item->name) !!}, key: {!! json_encode($item->key) !!}, category: {!! json_encode(ucfirst(strtolower($item->tier))) !!} },
    @endforeach
];

const TYPES   = ['Normal','Fighting','Flying','Poison','Ground','Rock','Bug','Ghost','Steel','Fire','Water','Grass','Electric','Psychic','Ice','Dragon','Dark','Fairy'];
const NATURES = ['Hardy','Lonely','Brave','Adamant','Naughty','Bold','Docile','Relaxed','Impish','Lax','Timid','Hasty','Serious','Jolly','Naive','Modest','Mild','Quiet','Bashful','Rash','Calm','Gentle','Sassy','Careful','Quirky'];
const STATS   = ['HP','ATK','DEF','SP.ATK','SP.DEF','SPD'];
const MAX_SLOTS = 6;

const PARAM_ITEMS = {
    'STAT_SWITCHER':           { type: 'stat2', label1: 'Swap stat',        label2: 'With stat' },
    'STAT_SACRIFICE':          { type: 'stat1', label1: 'Stat sacrificed' },
    'TYPE_SWITCHER':           { type: 'type2', label1: 'Type 1',           label2: 'Type 2 (blank = keep existing)' },
    'PRIMARY_TYPE_SWITCHER':   { type: 'type1', label1: 'New primary type' },
    'SECONDARY_TYPE_SWITCHER': { type: 'type2secondary', label1: 'Type 1 (blank = keep)', label2: 'New secondary type' },
};

// ── Slot management ───────────────────────────────────────────────
let slotCount = 0;

function updateSlotUI() {
    document.getElementById('slotCount').textContent = `${slotCount} / ${MAX_SLOTS}`;
    document.getElementById('addSlotBtn').disabled = slotCount >= MAX_SLOTS;
    document.querySelectorAll('.build-slot-delete-btn').forEach(btn => {
        btn.style.display = slotCount >= 2 ? '' : 'none';
    });
    // Reindex all slot data-slot attributes and input names
    document.querySelectorAll('.build-slot-editor').forEach((el, i) => {
        el.dataset.slot = i;
        el.querySelector('.build-slot-editor-num').textContent = `Slot ${i + 1}`;
        el.querySelectorAll('[name]').forEach(input => {
            input.name = input.name.replace(/team\[\d+\]/, `team[${i}]`);
        });
        // Fix itemsList id
        const list = el.querySelector('.build-items-list');
        if (list) list.id = `itemsList${i}`;
        // Fix add item button
        const addBtn = el.querySelector('.build-add-item-btn');
        if (addBtn) addBtn.onclick = () => addItemRow(i);
    });
}

function deleteSlot(btn) {
    btn.closest('.build-slot-editor').remove();
    slotCount--;
    updateSlotUI();
}

function addSlot(data) {
    if (slotCount >= MAX_SLOTS) return;
    const i = slotCount;
    const el = document.createElement('div');
    el.className = 'build-slot-editor';
    el.dataset.slot = i;

    const typeOpts  = `<option value="">— None —</option>` + TYPES.map(t => `<option value="${t}"${data?.override_type1===t?' selected':''}>${t}</option>`).join('');
    const typeOpts2 = `<option value="">— None —</option>` + TYPES.map(t => `<option value="${t}"${data?.override_type2===t?' selected':''}>${t}</option>`).join('');
    const natOpts   = `<option value="">— Any —</option>` + NATURES.map(n => `<option value="${n}"${data?.nature===n?' selected':''}>${n}</option>`).join('');

    el.innerHTML = `
        <div class="build-slot-editor-header">
            <span class="build-slot-editor-num">Slot ${i + 1}</span>
            <div style="display:flex;gap:.5rem">
                <button type="button" class="build-slot-delete-btn" onclick="deleteSlot(this)">✕ Remove</button>
            </div>
        </div>
        <div class="build-slot-editor-grid">
            <div class="build-form-field" style="position:relative">
                <label>Species</label>
                <input type="text" name="team[${i}][species]" value="${data?.species||''}"
                       class="build-form-input build-species-input"
                       placeholder="e.g. Charizard" autocomplete="off" data-typeahead="species" data-slot="${i}">
                <input type="hidden" name="team[${i}][dex_number]" class="build-dex-input" data-slot="${i}" value="${data?.dex_number||''}">
            </div>
            <div class="build-form-field">
                <label>Ability</label>
                <input type="text" name="team[${i}][ability]" value="${data?.ability||''}"
                       class="build-form-input" placeholder="e.g. Drought" autocomplete="off" data-typeahead="ability">
            </div>
            <div class="build-form-field">
                <label>Passive Ability</label>
                <input type="text" name="team[${i}][passive_ability]" value="${data?.passive_ability||''}"
                       class="build-form-input" placeholder="e.g. Flash Fire" autocomplete="off" data-typeahead="ability">
            </div>
            <div class="build-form-field">
                <label>Nature</label>
                <select name="team[${i}][nature]" class="build-form-select">${natOpts}</select>
            </div>
            <div class="build-form-field">
                <label>Override Type 1</label>
                <select name="team[${i}][override_type1]" class="build-form-select">${typeOpts}</select>
            </div>
            <div class="build-form-field">
                <label>Override Type 2</label>
                <select name="team[${i}][override_type2]" class="build-form-select">${typeOpts2}</select>
            </div>
            <div class="build-form-field build-altbuild-rank" style="display:none">
                <label>Alt Build Rank <span style="color:var(--dim);font-size:0.75em">(1–9)</span></label>
                <input type="number" name="team[${i}][alt_build_rank]" value="${data?.alt_build_rank||1}"
                       class="build-form-input" min="1" max="9" placeholder="1">
            </div>
            <div class="build-form-field">
                <label>Level <span style="color:var(--dim);font-size:0.75em">(1–10000, optional)</span></label>
                <input type="number" name="team[${i}][level]" value="${data?.level||''}"
                       class="build-form-input" min="1" max="10000" placeholder="e.g. 100">
            </div>
        </div>
        <div class="build-form-subsection">
            <div class="build-form-sublabel">Moves (up to 4)</div>
            <div class="build-moves-row">
                ${[0,1,2,3].map(m => `
                <div style="position:relative">
                    <input type="text" name="team[${i}][moves][${m}]" value="${data?.moves?.[m]||''}"
                           class="build-form-input build-move-input"
                           placeholder="Move ${m+1}" data-typeahead="move" autocomplete="off">
                </div>`).join('')}
            </div>
        </div>
        <div class="build-form-subsection">
            <div class="build-form-sublabel">Held Items</div>
            <div class="build-items-list" id="itemsList${i}"></div>
            <button type="button" class="build-add-item-btn" onclick="addItemRow(${i})">+ Add Item</button>
        </div>
        <div class="build-form-subsection">
            <label class="build-form-sublabel">Slot Notes</label>
            <textarea name="team[${i}][notes]" class="build-form-textarea build-slot-notes-input"
                      placeholder="Role, tips, alternatives…" rows="2" maxlength="500">${data?.notes||''}</textarea>
        </div>
    `;

    document.getElementById('buildSlots').appendChild(el);
    slotCount++;
    updateSlotUI();

    // Init typeaheads on new slot
    initSpeciesTypeahead(el.querySelector('input[data-typeahead="species"]'));
    el.querySelectorAll('input[data-typeahead="move"]').forEach(initMoveTypeahead);
    el.querySelectorAll('input[data-typeahead="ability"]').forEach(initAbilityTypeahead);

    // Restore items if data provided
    if (data?.items) {
        data.items.forEach(item => {
            if (item.name) addItemRow(i, item.name, item.key||'', item.stack||1, item.params||{});
        });
    }

    // If pre-populated species, check if it's an alt build and show rank field
    if (data?.species) {
        const rankField = el.querySelector('.build-altbuild-rank');
        fetch(`/pokemon-search.json?q=${encodeURIComponent(data.species)}`)
            .then(r => r.json())
            .then(results => {
                const match = results.find(r => r.value === data.species);
                if (rankField) rankField.style.display = (match?.category === 'Alt Build') ? '' : 'none';
            })
            .catch(() => {});
    }
}

// ── Item rows ─────────────────────────────────────────────────────
function buildParamHtml(base, cfg, params) {
    if (!cfg) return '';
    let html = '<div class="build-item-params">';
    if (cfg.type.startsWith('type')) {
        const t1 = params?.type1 || '';
        const t2 = params?.type2 || '';
        const blank1 = cfg.type === 'type2secondary' ? '— Keep existing —' : '— Default —';
        const opts1 = `<option value="">${blank1}</option>` + TYPES.map(t => `<option value="${t}"${t1===t?' selected':''}>${t}</option>`).join('');
        html += `<div class="build-item-param-row"><label class="build-item-param-label">${cfg.label1}</label><select name="${base}[type1]" class="build-form-select build-item-param-input">${opts1}</select></div>`;
        if (cfg.type === 'type2' || cfg.type === 'type2secondary') {
            const opts2 = '<option value="">— Keep existing —</option>' + TYPES.map(t => `<option value="${t}"${t2===t?' selected':''}>${t}</option>`).join('');
            html += `<div class="build-item-param-row"><label class="build-item-param-label">${cfg.label2}</label><select name="${base}[type2]" class="build-form-select build-item-param-input">${opts2}</select></div>`;
        }
    } else if (cfg.type.startsWith('stat')) {
        const s1 = params?.stat1 || '';
        const s2 = params?.stat2 || '';
        const opts1 = '<option value="">— Any —</option>' + STATS.map(s => `<option value="${s}"${s1===s?' selected':''}>${s}</option>`).join('');
        html += `<div class="build-item-param-row"><label class="build-item-param-label">${cfg.label1}</label><select name="${base}[stat1]" class="build-form-select build-item-param-input">${opts1}</select></div>`;
        if (cfg.type === 'stat2') {
            const opts2 = '<option value="">— Any —</option>' + STATS.map(s => `<option value="${s}"${s2===s?' selected':''}>${s}</option>`).join('');
            html += `<div class="build-item-param-row"><label class="build-item-param-label">${cfg.label2}</label><select name="${base}[stat2]" class="build-form-select build-item-param-input">${opts2}</select></div>`;
        }
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
    if (!list) return;
    const idx  = list.querySelectorAll('.build-item-row').length;
    const base = `team[${slot}][items][${idx}]`;

    const row = document.createElement('div');
    row.className = 'build-item-row';

    function render(key, currentName, p) {
        const cfg       = PARAM_ITEMS[key];
        const paramBase = `${base}[params]`;
        const paramHtml = buildParamHtml(paramBase, cfg, p);
        row.innerHTML = `
            <div class="build-item-row-top">
                <input type="text" name="${base}[name]" value="${currentName}"
                       class="build-form-input build-item-name-input"
                       placeholder="Item name" autocomplete="off" data-typeahead="item">
                <input type="hidden" name="${base}[key]" class="build-item-key-input" value="${key}">
                <input type="number" name="${base}[stack]" value="${stackVal}"
                       class="build-form-input build-item-stack-input" min="1" max="99" placeholder="x">
                <button type="button" class="build-item-remove-btn" onclick="this.closest('.build-item-row').remove()">✕</button>
            </div>
            ${paramHtml}
        `;
        const nameInput = row.querySelector('.build-item-name-input');
        const keyInput  = row.querySelector('.build-item-key-input');
        // Item typeahead — filters ITEM_LIST locally, no network call
        makeTypeahead(nameInput, null, r => {
            const prevKey  = keyInput.value;
            const prevName = nameInput.value;
            nameInput.value = r.value;
            keyInput.value  = r.key;
            // Only re-render params if key changed
            if (r.key !== prevKey) {
                render(r.key, r.value, {});
            }
        }, ITEM_LIST);
    }

    render(keyVal, nameVal, params);
    list.appendChild(row);
}

// ── Typeaheads ────────────────────────────────────────────────────
const CATEGORY_COLORS = {
    'Official':'var(--dim)', 'Core Glitch':'#ffaaa5',
    'SMITTY Pokémon':'#a8e6cf', 'SMITTY Form':'#a8e6cf',
    'Mod Glitch':'#c9a8ff', 'Alt Build':'#f5d76e',
    'Move':'var(--dim)', 'SMITTY Move':'#a8e6cf',
};

function makeTypeahead(input, fetchUrl, onSelect, localData) {
    const wrap = input.parentNode;
    wrap.style.position = 'relative';
    const dd = document.createElement('div');
    dd.className = 'pokemon-typeahead-dropdown';
    wrap.appendChild(dd);

    let debounce = null, activeIdx = -1, lastResults = [];

    function close() { dd.innerHTML = ''; dd.style.display = 'none'; activeIdx = -1; }

    function render(results) {
        lastResults = results;
        if (!results.length) { close(); return; }
        dd.innerHTML = '';
        dd.style.display = 'block';
        results.slice(0, 12).forEach((r, i) => {
            const item = document.createElement('div');
            item.className = 'pokemon-typeahead-item';
            const color = CATEGORY_COLORS[r.category] || 'var(--dim)';
            item.innerHTML = `<span class="pokemon-typeahead-name">${r.label}</span><span class="pokemon-typeahead-cat" style="color:${color}">${r.category}</span>`;
            item.addEventListener('mousedown', e => { e.preventDefault(); onSelect(r); close(); });
            dd.appendChild(item);
        });
        activeIdx = -1;
    }

    function filterLocal(q) {
        const ql = q.toLowerCase();
        return (localData || []).filter(r => r.label.toLowerCase().includes(ql))
            .sort((a, b) => {
                const ai = a.label.toLowerCase().indexOf(ql);
                const bi = b.label.toLowerCase().indexOf(ql);
                return ai - bi;
            });
    }

    input.addEventListener('input', () => {
        clearTimeout(debounce);
        const q = input.value.trim();
        if (q.length < 1) { close(); return; }
        if (localData) {
            render(filterLocal(q));
            return;
        }
        if (q.length < 2) { close(); return; }
        debounce = setTimeout(() => {
            fetch(`${fetchUrl}?q=${encodeURIComponent(q)}`)
                .then(r => r.json()).then(render).catch(close);
        }, 180);
    });

    input.addEventListener('keydown', e => {
        const items = dd.querySelectorAll('.pokemon-typeahead-item');
        if (!items.length) return;
        if (e.key === 'ArrowDown') { e.preventDefault(); activeIdx = Math.min(activeIdx+1, items.length-1); items.forEach((el,i) => el.classList.toggle('active', i===activeIdx)); }
        else if (e.key === 'ArrowUp') { e.preventDefault(); activeIdx = Math.max(activeIdx-1, 0); items.forEach((el,i) => el.classList.toggle('active', i===activeIdx)); }
        else if (e.key === 'Enter' && activeIdx >= 0) { e.preventDefault(); onSelect(lastResults[activeIdx]); close(); }
        else if (e.key === 'Escape') close();
    });

    input.addEventListener('blur', () => setTimeout(close, 150));
}

function initSpeciesTypeahead(input) {
    if (!input) return;
    const slot = () => parseInt(input.closest('.build-slot-editor').dataset.slot);
    makeTypeahead(input, '/pokemon-search.json', r => {
        input.value = r.value;
        const el = input.closest('.build-slot-editor');
        const dexInput = el.querySelector('.build-dex-input');
        const ab1Input = el.querySelector('[name$="[ability]"]');
        const ab2Input = el.querySelector('[name$="[passive_ability]"]');
        if (dexInput) dexInput.value = (r.dex !== undefined && r.dex !== null) ? r.dex : '';
        if (ab1Input && !ab1Input.value && r.ability1) ab1Input.value = r.ability1;
        if (ab2Input && !ab2Input.value && r.abilityH) ab2Input.value = r.abilityH;
        // Show rank field for alt builds
        const rankField = input.closest('.build-slot-editor').querySelector('.build-altbuild-rank');
        if (rankField) rankField.style.display = r.category === 'Alt Build' ? '' : 'none';
    });
}

function initMoveTypeahead(input) {
    if (!input) return;
    makeTypeahead(input, '/move-search.json', r => { input.value = r.value; });
}

function initAbilityTypeahead(input) {
    if (!input) return;
    makeTypeahead(input, '/ability-search.json', r => { input.value = r.value; });
}

// ── Init ──────────────────────────────────────────────────────────
// Restore from old() on validation failure, otherwise start with 1 slot
@if(old('team'))
    @foreach(old('team', []) as $i => $slot)
    addSlot({!! json_encode($slot) !!});
    @endforeach
@else
    addSlot();
@endif
</script>
