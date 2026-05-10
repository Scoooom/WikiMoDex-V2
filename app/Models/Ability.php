<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ability extends Model
{
    protected $table = 'abilities';
    protected $guarded = [];

    public function forms()
    {
        return $this->hasMany(BuiltinForm::class, 'ab1_id')
            ->orWhere('ab2_id', $this->id)
            ->orWhere('ha_id', $this->id);
    }
}
