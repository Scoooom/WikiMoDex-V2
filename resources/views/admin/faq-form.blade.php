{{-- Shared form partial for FAQ create and edit --}}

<div class="editor-row editor-row--half">
    <div>
        <label for="group">Group</label>
        <select id="group" name="group" class="editor-input">
            @foreach($groups as $g)
            <option value="{{ $g }}" {{ old('group', $entry?->group) === $g ? 'selected' : '' }}>{{ $g }}</option>
            @endforeach
            <option value="__custom__">Custom…</option>
        </select>
        <input type="text" id="group_custom" name="group_custom"
               placeholder="Custom group name"
               class="editor-input editor-input--hidden" style="margin-top:6px">
    </div>
    <div>
        <label for="group_order">Group Order</label>
        <input type="number" id="group_order" name="group_order" min="0"
               value="{{ old('group_order', $entry?->group_order ?? 0) }}"
               class="editor-input editor-input--sm">
    </div>
</div>

<div class="editor-row editor-row--half">
    <div>
        <label for="order">Entry Order <span class="muted">(within group)</span></label>
        <input type="number" id="order" name="order" min="0"
               value="{{ old('order', $entry?->order ?? 0) }}"
               class="editor-input editor-input--sm">
    </div>
    <div style="display:flex;align-items:center;gap:10px;padding-top:26px">
        <input type="checkbox" id="open_by_default" name="open_by_default" value="1"
               {{ old('open_by_default', $entry?->open_by_default) ? 'checked' : '' }}>
        <label for="open_by_default" style="margin:0;font-weight:400">Open by default</label>
    </div>
</div>

<div class="editor-row">
    <label for="question">Question</label>
    <input type="text" id="question" name="question"
           value="{{ old('question', $entry?->question) }}"
           class="editor-input" required>
</div>

<div class="editor-row">
    <label for="answer_html">Answer <span class="muted">(HTML — shown on web)</span></label>
    <div class="editor-toolbar">
        <button type="button" onclick="wrapFaqSel('<strong>','</strong>')">B</button>
        <button type="button" onclick="wrapFaqSel('<em>','</em>')"><em>I</em></button>
        <button type="button" onclick="wrapFaqSel('<code>','</code>')">Code</button>
        <button type="button" onclick="wrapFaqSel('<a href=\"\">', '</a>')">Link</button>
        <button type="button" onclick="insertFaqSnippet('<ul>\n<li></li>\n</ul>')">UL</button>
        <button type="button" onclick="insertFaqSnippet('<ol>\n<li></li>\n</ol>')">OL</button>
        <button type="button" onclick="insertFaqSnippet('<p></p>')">P</button>
    </div>
    <textarea id="answer_html" name="answer_html" rows="12"
              class="editor-textarea">{{ old('answer_html', $entry?->answer_html) }}</textarea>
</div>

<div class="editor-row">
    <label for="answer_plain">Answer <span class="muted">(plain text — shown in Discord)</span></label>
    <textarea id="answer_plain" name="answer_plain" rows="6"
              class="editor-textarea" style="font-family:monospace;font-size:13px">{{ old('answer_plain', $entry?->answer_plain) }}</textarea>
</div>

<script>
document.getElementById('group').addEventListener('change', function() {
    const custom = document.getElementById('group_custom');
    if (this.value === '__custom__') {
        custom.classList.remove('editor-input--hidden');
        custom.required = true;
        custom.name = 'group';
        this.name = '';
    } else {
        custom.classList.add('editor-input--hidden');
        custom.required = false;
        custom.name = 'group_custom';
        this.name = 'group';
    }
});

function wrapFaqSel(before, after) {
    const ta = document.getElementById('answer_html');
    const s = ta.selectionStart, e = ta.selectionEnd;
    const sel = ta.value.substring(s, e);
    ta.value = ta.value.substring(0, s) + before + sel + after + ta.value.substring(e);
    ta.focus();
    ta.setSelectionRange(s + before.length, e + before.length);
}

function insertFaqSnippet(snippet) {
    const ta = document.getElementById('answer_html');
    const s = ta.selectionStart;
    ta.value = ta.value.substring(0, s) + snippet + ta.value.substring(s);
    ta.focus();
    ta.setSelectionRange(s + snippet.length, s + snippet.length);
}
</script>
