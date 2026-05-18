<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WikiArticle extends Model
{
    use HasFactory;

    protected $fillable = ['slug', 'title', 'content', 'category', 'order'];

    protected static function booted(): void
    {
        // Re-index sections whenever an article is created or updated
        static::saved(function (WikiArticle $article) {
            WikiSection::indexArticle($article);
        });

        static::deleted(function (WikiArticle $article) {
            WikiSection::where('article_slug', $article->slug)->delete();
        });
    }

    /** Re-index every article — call after bulk seeding (which bypasses model events). */
    public static function reindexAll(): void
    {
        WikiSection::truncate();
        foreach (static::all() as $article) {
            WikiSection::indexArticle($article);
        }
    }

    public static function categories(): array
    {
        return self::distinct()
            ->orderBy('category')
            ->pluck('category')
            ->toArray();
    }

    public static function byCategory(): array
    {
        return self::orderBy('order')->orderBy('title')
            ->get()
            ->groupBy('category')
            ->toArray();
    }

    public static function categoryOrder(): array
    {
        return [
            'Getting Started',
            'Champions',
            'Game Modes',
            'Glitch System',
            'Smitty Pokémon',
            'Rivals & Quests',
            'Omega Features',
            'Items & Shop',
        ];
    }

    /** Strip markdown and HTML, return plain-text excerpt for meta description. */
    public function plainTextExcerpt(int $length = 155): string
    {
        $text = $this->content;
        $text = preg_replace('/^#{1,6}\s+/m', '', $text);
        $text = preg_replace('/!\[.*?\]\(.*?\)/', '', $text);
        $text = preg_replace('/\[([^\]]+)\]\([^\)]+\)/', '$1', $text);
        $text = preg_replace('/[*_]{1,3}([^*_]+)[*_]{1,3}/', '$1', $text);
        $text = preg_replace('/`{1,3}[^`]*`{1,3}/', '', $text);
        $text = preg_replace('/^\|.*\|$/m', '', $text);
        $text = preg_replace('/^[-*+]\s+/m', '', $text);
        $text = strip_tags($text);
        $text = preg_replace('/\s+/', ' ', $text);
        return \Illuminate\Support\Str::limit(trim($text), $length);
    }

    /** Return the best OG image URL for this article. */
    public function ogImageUrl(): string
    {
        if ($this->category === 'Champions' &&
            file_exists(public_path("og/rivals/{$this->slug}.png"))) {
            return asset("og/rivals/{$this->slug}.png");
        }
        return asset('og/smittom.png');
    }
}
