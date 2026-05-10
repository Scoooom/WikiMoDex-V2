<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WikiArticle extends Model
{
    use HasFactory;

    protected $fillable = ['slug', 'title', 'content', 'category', 'order'];

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
}
