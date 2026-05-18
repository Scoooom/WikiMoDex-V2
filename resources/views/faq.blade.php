@extends('layouts.app')
@section('title', 'FAQ — PokéVoid Wiki')
@section('meta_description', 'WikiMoDex — the PokéVoid fan game wiki. Guides, builds, items, rivals, and more.')
@push('meta')
<meta property="og:image" content="{{ asset('og/smittom.png') }}">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:image" content="{{ asset('og/smittom.png') }}">
@endpush

@push('meta')
<?php
$faqJsonLd = json_encode([
    '@context' => 'https://schema.org',
    '@type'    => 'FAQPage',
    'mainEntity' => collect($grouped)->flatten(1)->map(fn($faq) => [
        '@type' => 'Question',
        'name'  => $faq['question'],
        'acceptedAnswer' => [
            '@type' => 'Answer',
            'text'  => $faq['answer_plain'],
        ],
    ])->values()->all(),
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
?>
<script type="application/ld+json">{!! $faqJsonLd !!}</script>
@endpush

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
            <div class="faq-question-row">
                <button class="faq-question" onclick="toggleFaqBySlug('{{ $faq['slug'] }}')">
                    {{ $faq['question'] }}
                    <span class="faq-chevron">▾</span>
                </button>
                <a class="faq-anchor" href="#faq-{{ $faq['slug'] }}" aria-label="Link to this question">#</a>
            </div>
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
    const raw = window.location.hash.slice(1);
    const slug = raw.startsWith('faq-') ? raw.slice(4) : raw;
    const el = document.getElementById('faq-' + slug);
    if (el) {
        el.classList.add('open');
        setTimeout(() => el.scrollIntoView({ behavior: 'instant', block: 'start' }), 100);
    }
}
</script>
@endsection
