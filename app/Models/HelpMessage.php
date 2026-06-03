<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class HelpMessage extends Model
{
    protected $fillable = [
        'order',
        'name',
        'slug',
        'header',
        'body',
        'created_by_discord_id',
    ];

    /** Generate a URL-safe slug from a name string. */
    public static function slugFor(string $name): string
    {
        return Str::slug($name);
    }

    public function edits()
    {
        return $this->hasMany(HelpMessageEdit::class);
    }
}
