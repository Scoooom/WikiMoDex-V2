<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CoreMove extends Model
{
    protected $table = 'core_moves';
    protected $guarded = [];
    protected $casts = [
        'is_smitty'       => 'boolean',
        'is_dynamic_type' => 'boolean',
    ];

    const TYPE_NAMES = [
        -1=>'Unknown',0=>'Normal',1=>'Fighting',2=>'Flying',3=>'Poison',
        4=>'Ground',5=>'Rock',6=>'Bug',7=>'Ghost',8=>'Steel',9=>'Fire',
        10=>'Water',11=>'Grass',12=>'Electric',13=>'Psychic',14=>'Ice',
        15=>'Dragon',16=>'Dark',17=>'Fairy',18=>'Stellar',20=>'SMITTY',21=>'Glitch',
    ];
}
