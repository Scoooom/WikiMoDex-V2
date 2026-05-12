@extends('layouts.app')
@section('title', 'Import Build from Save')
@section('content')
<div class="wiki-page">
    <div class="wiki-page-header">
        <div>
            <a href="/builds.html" class="build-back-link">← All Builds</a>
            <h1 class="wiki-page-title">Import from Save File</h1>
            <p class="wiki-page-lead">Upload your <code>.prsv</code> save file to import your team as a community build.</p>
        </div>
    </div>

    @if($errors->any())
    <div class="flash-error">
        @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
    </div>
    @endif

    <div class="build-form-section">
        <form method="POST" action="/builds/import" enctype="multipart/form-data">
            @csrf
            <label class="build-form-label">Save File (.prsv)</label>
            <div class="build-import-drop" id="dropZone">
                <div class="build-import-drop-inner">
                    <div class="build-import-drop-icon">📁</div>
                    <div class="build-import-drop-text">Drop your <code>.prsv</code> file here, or click to browse</div>
                    <input type="file" name="prsv" id="prsvInput" accept=".prsv" class="build-import-file-input">
                </div>
                <div class="build-import-selected" id="selectedFile" style="display:none"></div>
            </div>
            <div class="build-form-submit-row" style="margin-top:1rem">
                <button type="submit" class="btn-primary">Upload & Preview Slots</button>
                <a href="/builds.html" class="btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<script>
const drop = document.getElementById('dropZone');
const input = document.getElementById('prsvInput');
const selected = document.getElementById('selectedFile');

drop.addEventListener('click', () => input.click());
input.addEventListener('click', e => e.stopPropagation());
drop.addEventListener('dragover', e => { e.preventDefault(); drop.classList.add('dragging'); });
drop.addEventListener('dragleave', () => drop.classList.remove('dragging'));
drop.addEventListener('drop', e => {
    e.preventDefault();
    drop.classList.remove('dragging');
    if (e.dataTransfer.files[0]) {
        input.files = e.dataTransfer.files;
        showFile(e.dataTransfer.files[0].name);
    }
});
input.addEventListener('change', () => {
    if (input.files[0]) showFile(input.files[0].name);
});
function showFile(name) {
    selected.textContent = '✓ ' + name;
    selected.style.display = '';
}
</script>

@endsection
