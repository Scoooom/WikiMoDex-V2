<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class CommunityBuild extends Model
{
    protected $fillable = [
        'slug', 'title', 'description', 'user_id', 'team', 'votes',
    ];

    protected $casts = [
        'team' => 'array',
    ];

    // ── Relationships ─────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function buildVotes(): HasMany
    {
        return $this->hasMany(CommunityBuildVote::class, 'build_id');
    }

    // ── Helpers ───────────────────────────────────────────────────

    /**
     * Generate a unique slug from a title + username.
     * e.g. "Sun Team" + "scooom" → "scooom-sun-team", then "scooom-sun-team-2" if taken.
     */
    public static function generateSlug(string $title, string $username): string
    {
        $base = Str::slug($username . '-' . $title);
        $slug = $base;
        $i = 2;
        while (static::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $i++;
        }
        return $slug;
    }

    /**
     * All Pokémon species in the team (for display/search).
     */
    public function getSpeciesListAttribute(): string
    {
        return collect($this->team ?? [])
            ->pluck('species')
            ->filter()
            ->join(', ');
    }

    /**
     * How many filled slots are in this team.
     */
    public function getTeamSizeAttribute(): int
    {
        return collect($this->team ?? [])->filter(fn($slot) => !empty($slot['species']))->count();
    }

    /**
     * Has the given user voted on this build?
     */
    public function hasVotedBy(?int $userId): bool
    {
        if (!$userId) return false;
        return $this->buildVotes()->where('user_id', $userId)->exists();
    }
}
