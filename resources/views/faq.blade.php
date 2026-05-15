@extends('layouts.app')
@section('title', 'FAQ — PokéVoid Wiki')

@section('content')
<div class="container">
    <div class="section-header mt-2 mb-3">
        <span class="section-title">Frequently Asked Questions</span>
    </div>

    @foreach($grouped as $groupName => $faqs)
    <div class="faq-group">
        <div class="faq-group-label">{{ $groupName }}</div>
        @foreach($faqs as $faq)
        <div class="faq-item {{ $faq['open_by_default'] ? 'open' : '' }}" id="faq-{{ $faq['slug'] }}">
            <button class="faq-question" onclick="toggleFaqBySlug('{{ $faq['slug'] }}')">
                {{ $faq['question'] }}
                <span class="faq-chevron">▾</span>
            </button>
            <div class="faq-answer">
                <div class="faq-answer-inner">
                    {!! $faq['answer_html'] !!}
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @endforeach
</div>

<script>
function toggleFaqBySlug(slug) {
    const item = document.getElementById('faq-' + slug);
    if (!item) return;
    const isOpen = item.classList.contains('open');
    document.querySelectorAll('.faq-item.open').forEach(el => el.classList.remove('open'));
    if (!isOpen) item.classList.add('open');
}

// Open + scroll to item if URL hash matches
if (window.location.hash) {
    const slug = window.location.hash.slice(1);
    const el = document.getElementById('faq-' + slug);
    if (el) {
        el.classList.add('open');
        setTimeout(() => {
            const navH = parseInt(getComputedStyle(document.documentElement).getPropertyValue('--nav-h') || '54');
            const top = el.getBoundingClientRect().top + window.scrollY - navH - 24;
            window.scrollTo({ top, behavior: 'instant' });
        }, 50);
    }
}
</script>
@endsection
