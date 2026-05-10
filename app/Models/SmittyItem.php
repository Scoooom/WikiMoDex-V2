<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SmittyItem extends Model
{
    protected $table = 'smitty_items';
    protected $guarded = [];

    public function form()
    {
        return $this->belongsTo(BuiltinForm::class, 'form_name', 'name');
    }
}
