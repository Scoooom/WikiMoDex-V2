<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BuiltinForm extends Model
{
    protected $table = 'builtin_forms';
    protected $guarded = [];

    public function ab1()
    {
        return $this->belongsTo(Ability::class, 'ab1_id');
    }

    public function ab2()
    {
        return $this->belongsTo(Ability::class, 'ab2_id');
    }

    public function ha()
    {
        return $this->belongsTo(Ability::class, 'ha_id');
    }

    public function getAbilityOne(): array
    {
        return ['name' => $this->ab1->name, 'desc' => $this->ab1->description];
    }

    public function getAbilityTwo(): array
    {
        return ['name' => $this->ab2->name, 'desc' => $this->ab2->description];
    }

    public function getAbilityHA(): array
    {
        return ['name' => $this->ha->name, 'desc' => $this->ha->description];
    }

    public function getOgMonNames(): string
    {
        if (empty($this->og_mon)) return '';
        $ids = explode(',', $this->og_mon);
        $names = [];
        foreach ($ids as $id) {
            try {
                $mon = \App\Services\PokemonService::getMon(trim($id));
                $names[] = ucwords(str_replace('-', ' ', $mon->name));
            } catch (\Exception $e) {
                $names[] = 'Unknown';
            }
        }
        return implode(', ', $names);
    }

    public function scopeCore($query)
    {
        return $query->where('form_type', 'core');
    }

    public function scopeSmitty($query)
    {
        return $query->where('form_type', 'smitty');
    }

    public function scopeSmittyForm($query)
    {
        return $query->where('form_type', 'smitty_form');
    }
}
