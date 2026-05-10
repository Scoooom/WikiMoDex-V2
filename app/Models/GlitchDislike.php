<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GlitchDislike extends Model
{
    protected $table = 'glitchDislikes';
    protected $guarded = [];
    public $timestamps = false;

    public function glitch()
    {
        return $this->belongsTo(Glitch::class, 'glitchID');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'userID');
    }
}
