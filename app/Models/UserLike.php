<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserLike extends Model
{
    protected $table = 'userLikes';
    protected $guarded = [];
    public $timestamps = false;

    public function creator()
    {
        return $this->belongsTo(User::class, 'creatorID');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'userID');
    }
}
