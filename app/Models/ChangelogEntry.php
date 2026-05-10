<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChangelogEntry extends Model
{
    protected $fillable = ['hash', 'version', 'title', 'body', 'committed_at'];
    protected $casts    = ['committed_at' => 'datetime'];
}
