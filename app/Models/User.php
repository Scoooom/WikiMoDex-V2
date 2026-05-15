<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $table = 'users';
    protected $guarded = [];
    public $timestamps = false;

    public function isAdmin(): bool
    {
        return (bool) $this->is_admin || $this->isSuperAdmin();
    }

    public function isSuperAdmin(): bool
    {
        return (bool) in_array($this->user_id , ["356260100064673814","1339032924170879118"]);
    }

    public function isWikiEditor(): bool
    {
        // Admins inherit editor privileges
        return (bool) $this->is_admin || (bool) $this->is_wiki_editor;
    }

    public function canAccessPanel(): bool
    {
        return ($this->isAdmin() || $this->isWikiEditor()) && (bool) $this->mfa_enabled;
    }

    protected $casts = [
        'tc_sections' => 'array',
    ];

    public function getTcSections(): array
    {
        $defaults = [
            'rivals'    => true,
            'core'      => true,
            'mod'       => true,
            'smitty'    => true,
            'unismitty' => true,
            'submitted' => true,
        ];
        return array_merge($defaults, $this->tc_sections ?? []);
    }

    public function getDisplayName(): string
    {
        return $this->display_name ?? $this->username;
    }

    public function glitches()
    {
        return $this->hasMany(Glitch::class, 'created_by');
    }

    public function likes()
    {
        return $this->hasMany(UserLike::class, 'creatorID');
    }

    public function getAvatarURL(): string
    {
        // Prefer locally cached avatar (written on every login)
        $local = public_path("avatars/{$this->user_id}.png");
        if (file_exists($local)) {
            return "/avatars/{$this->user_id}.png";
        }

        // Fall back to default avatar — never a broken image
        return '/avatars/default.svg';
    }

    public function getUploadCount()
    {
        return $this->glitches()->count();
    }

    public function getLikeCount()
    {
        return $this->likes()->count();
    }

    public function likesGlitch($glitchId)
    {
        return GlitchLike::where('glitchID', $glitchId)
            ->where('userID', $this->user_id)
            ->exists();
    }

    public function dislikesGlitch($glitchId)
    {
        return GlitchDislike::where('glitchID', $glitchId)
            ->where('userID', $this->user_id)
            ->exists();
    }

    public function likesUser($userId)
    {
        return UserLike::where('creatorID', $userId)
            ->where('userID', $this->user_id)
            ->exists();
    }

    public function getSave()
    {
        if ($this->raw_prsv === null) {
            return ['er' => 'Save not on file'];
        }
        return new \App\Services\PrsvService($this->raw_prsv, $this->b64_prsv);
    }
}
