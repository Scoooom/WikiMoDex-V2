<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CoreMove extends Model
{
    protected $table = 'core_moves';
    protected $guarded = [];
    protected $casts = ['is_smitty' => 'boolean'];
}
