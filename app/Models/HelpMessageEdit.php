<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HelpMessageEdit extends Model
{
    protected $fillable = [
        'help_message_id',
        'editor_discord_id',
        'name_diff',
        'header_diff',
        'body_diff',
    ];

    protected $casts = [
        'name_diff'   => 'array',
        'header_diff' => 'array',
        'body_diff'   => 'array',
    ];

    public function helpMessage()
    {
        return $this->belongsTo(HelpMessage::class);
    }
}
