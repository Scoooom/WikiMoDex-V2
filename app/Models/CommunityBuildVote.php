<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommunityBuildVote extends Model
{
    protected $fillable = ['build_id', 'user_id'];

    public function build()
    {
        return $this->belongsTo(CommunityBuild::class, 'build_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
