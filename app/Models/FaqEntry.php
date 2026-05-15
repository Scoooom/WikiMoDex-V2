<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class FaqEntry extends Model
{
    protected $fillable = [
        'group',
        'group_order',
        'order',
        'question',
        'answer_html',
        'answer_plain',
        'slug',
        'open_by_default',
    ];

    protected $casts = [
        'open_by_default' => 'boolean',
    ];

    /** All entries grouped and ordered, ready for the FAQ page. */
    public static function grouped(): array
    {
        return static::orderBy('group_order')
            ->orderBy('order')
            ->get()
            ->groupBy('group')
            ->toArray();
    }

    /** Generate a URL-safe slug from a question string. */
    public static function slugFor(string $question): string
    {
        return Str::slug($question);
    }
}
