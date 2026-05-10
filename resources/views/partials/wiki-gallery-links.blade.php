@if(isset($galleryLinks) && count($galleryLinks))
<div class="wiki-gallery-links">
    <h3 class="wiki-gallery-links-title">Browse in WikiMoDex</h3>
    <div class="wiki-gallery-links-grid">
        @foreach($galleryLinks as $link)
        <a href="{{ $link['url'] }}" class="wiki-gallery-link">
            <span class="wiki-gallery-link-icon">{{ $link['icon'] }}</span>
            <span class="wiki-gallery-link-label">{{ $link['label'] }}</span>
            <span class="wiki-gallery-link-sub">{{ $link['sub'] }}</span>
        </a>
        @endforeach
    </div>
</div>
@endif
