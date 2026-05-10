{{-- Shared form partial for create and edit --}}
<div class="editor-row">
    <label for="title">Title</label>
    <input type="text" id="title" name="title"
           value="{{ old('title', $article?->title) }}"
           class="editor-input" required>
</div>

<div class="editor-row editor-row--half">
    <div>
        <label for="category">Category</label>
        <select id="category" name="category" class="editor-input">
            @foreach($categories as $cat)
            <option value="{{ $cat }}"
                {{ old('category', $article?->category) === $cat ? 'selected' : '' }}>
                {{ $cat }}
            </option>
            @endforeach
            <option value="__custom__">Custom…</option>
        </select>
        <input type="text" id="category_custom" name="category_custom"
               placeholder="Custom category name"
               class="editor-input editor-input--hidden" style="margin-top:6px">
    </div>
    <div>
        <label for="order">Display Order</label>
        <input type="number" id="order" name="order" min="0"
               value="{{ old('order', $article?->order ?? 0) }}"
               class="editor-input editor-input--sm">
    </div>
</div>

<div class="editor-row">
    <label for="content">Content <span class="muted">(Markdown)</span></label>
    <div class="editor-toolbar">
        <button type="button" onclick="wrapSel('**','**')">B</button>
        <button type="button" onclick="wrapSel('*','*')"><em>I</em></button>
        <button type="button" onclick="insertPrefix('## ')">H2</button>
        <button type="button" onclick="insertPrefix('### ')">H3</button>
        <button type="button" onclick="insertPrefix('- ')">List</button>
        <button type="button" onclick="insertTable()">Table</button>
        <button type="button" onclick="insertPrefix('> ')">Quote</button>
    </div>
    <textarea id="content" name="content" rows="30"
              class="editor-textarea">{{ old('content', $article?->content) }}</textarea>
</div>

<script>
// Custom category toggle
document.getElementById('category').addEventListener('change', function() {
    const custom = document.getElementById('category_custom');
    if (this.value === '__custom__') {
        custom.classList.remove('editor-input--hidden');
        custom.required = true;
        custom.name = 'category';
        this.name = '';
    } else {
        custom.classList.add('editor-input--hidden');
        custom.required = false;
        custom.name = 'category_custom';
        this.name = 'category';
    }
});

function wrapSel(before, after) {
    const ta = document.getElementById('content');
    const s = ta.selectionStart, e = ta.selectionEnd;
    const sel = ta.value.substring(s, e);
    ta.value = ta.value.substring(0, s) + before + sel + after + ta.value.substring(e);
    ta.focus();
    ta.setSelectionRange(s + before.length, e + before.length);
}

function insertPrefix(prefix) {
    const ta = document.getElementById('content');
    const s = ta.selectionStart;
    const lineStart = ta.value.lastIndexOf('\n', s - 1) + 1;
    ta.value = ta.value.substring(0, lineStart) + prefix + ta.value.substring(lineStart);
    ta.focus();
    ta.setSelectionRange(s + prefix.length, s + prefix.length);
}

function insertTable() {
    const tbl = '\n| Column 1 | Column 2 | Column 3 |\n|----------|----------|----------|\n| Cell     | Cell     | Cell     |\n';
    const ta = document.getElementById('content');
    const s = ta.selectionStart;
    ta.value = ta.value.substring(0, s) + tbl + ta.value.substring(s);
    ta.focus();
}
</script>
