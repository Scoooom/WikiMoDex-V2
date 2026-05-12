@extends('layouts.app')
@section('title', 'Choose Save Slot')
@section('content')
<div class="wiki-page">
    <div class="wiki-page-header">
        <div>
            <a href="/builds/import.html" class="build-back-link">← Upload Again</a>
            <h1 class="wiki-page-title">Choose a Save Slot</h1>
            <p class="wiki-page-lead">Select which run you'd like to import, then give it a name.</p>
        </div>
    </div>

    <form method="POST" action="/builds/import/create">
        @csrf

        <div class="build-import-slots">
            @foreach($slots as $i => $slot)
                @if($slot === null)
                <div class="build-import-slot build-import-slot--empty">
                    <div class="build-import-slot-num">Slot {{ $i + 1 }}</div>
                    <div class="build-import-slot-empty">Empty</div>
                </div>
                @else
                <label class="build-import-slot build-import-slot--active" for="slot_{{ $i }}">
                    <div class="build-import-slot-header">
                        <input type="radio" name="slot" id="slot_{{ $i }}" value="{{ $slot['index'] }}"
                               class="build-import-slot-radio" {{ $loop->first && $slot ? 'checked' : '' }}>
                        <div class="build-import-slot-num">Slot {{ $i + 1 }}</div>
                        <div class="build-import-slot-meta">
                            Wave {{ $slot['wave'] ?? '?' }}
                            @if($slot['timestamp'])
                                · {{ \Carbon\Carbon::createFromTimestampMs($slot['timestamp'])->diffForHumans() }}
                            @endif
                        </div>
                    </div>
                    <div class="build-import-party">
                        @foreach($slot['preview'] as $mon)
                        <div class="build-import-party-mon">
                            <span class="build-import-mon-name">{{ $mon['species'] }}</span>
                            @if($mon['nickname'])
                                <span class="build-import-mon-nickname">&ldquo;{{ $mon['nickname'] }}&rdquo;</span>
                            @endif
                            <span class="build-import-mon-level">Lv.{{ $mon['level'] }}</span>
                        </div>
                        @endforeach
                    </div>
                </label>
                @endif
            @endforeach
        </div>

        <div class="build-form-section" style="margin-top:1.5rem">
            <label class="build-form-label" for="title">Build Title <span class="req">*</span></label>
            <input type="text" id="title" name="title" class="build-form-input"
                   placeholder="e.g. Wave 764 Rayquaza Run" maxlength="80" required>
        </div>

        <div class="build-form-submit-row">
            <button type="submit" class="btn-primary">Import Build</button>
            <a href="/builds/import.html" class="btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<script>
const waveBySlot = @json(collect($slots)->mapWithKeys(fn($s, $i) => [$s['index'] ?? $i => $s['wave'] ?? null])->filter());
const titleInput = document.getElementById('title');

function updateTitle() {
    const checked = document.querySelector('input[name="slot"]:checked');
    if (!checked) return;
    const wave = waveBySlot[checked.value];
    if (wave && !titleInput.dataset.userEdited) {
        titleInput.value = 'Wave ' + wave + ' Run';
    }
}

titleInput.addEventListener('input', () => { titleInput.dataset.userEdited = '1'; });
document.querySelectorAll('input[name="slot"]').forEach(r => r.addEventListener('change', updateTitle));
updateTitle();
</script>
