<?php

namespace App\Services;

use App\Models\BuiltinForm;
use App\Models\Ability;
use Illuminate\Support\Facades\Cache;

class BuiltInService
{
    public static function loadCore(string $name): ?BuiltinForm
    {
        return BuiltinForm::with(['ab1', 'ab2', 'ha'])
            ->where('form_type', 'core')
            ->whereRaw('LOWER(name) = ?', [strtolower($name)])
            ->first();
    }

    public static function loadSmitty(string $name): ?BuiltinForm
    {
        return BuiltinForm::with(['ab1', 'ab2', 'ha'])
            ->where('form_type', 'smitty')
            ->whereRaw('LOWER(name) = ?', [strtolower($name)])
            ->first();
    }

    public static function loadSmittyForm(string $name): ?BuiltinForm
    {
        return BuiltinForm::with(['ab1', 'ab2', 'ha'])
            ->where('form_type', 'smitty_form')
            ->whereRaw('LOWER(name) = ?', [strtolower($name)])
            ->first();
    }

    public static function loadCoreGlitchByMonID($id): ?BuiltinForm
    {
        return BuiltinForm::with(['ab1', 'ab2', 'ha'])
            ->where('form_type', 'core')
            ->where('og_mon', $id)
            ->first();
    }

    public static function loadCoreSmittyByMonID($id): ?BuiltinForm
    {
        if (is_array($id)) $id = $id[0];
        return BuiltinForm::with(['ab1', 'ab2', 'ha'])
            ->where('form_type', 'smitty_form')
            ->where('og_mon', $id)
            ->first();
    }

    public static function load(): object
    {
        $forms = BuiltinForm::with(['ab1', 'ab2', 'ha'])
            ->where('form_type', 'core')
            ->get()
            ->keyBy(fn($f) => strtolower($f->name));
        return (object) $forms->all();
    }

    public static function smittyLoad(): object
    {
        $forms = BuiltinForm::with(['ab1', 'ab2', 'ha'])
            ->where('form_type', 'smitty')
            ->get()
            ->keyBy(fn($f) => strtolower($f->name));
        return (object) $forms->all();
    }

    public static function smittyFormLoad(): object
    {
        $forms = BuiltinForm::with(['ab1', 'ab2', 'ha'])
            ->where('form_type', 'smitty_form')
            ->get()
            ->keyBy(fn($f) => strtolower($f->name));
        return (object) $forms->all();
    }

    public static function getSmittyItems(string $form)
    {
        $items = \App\Models\SmittyItem::where('form_name', strtolower($form))
            ->orderBy('sort_order')
            ->pluck('item_name');

        if ($items->isEmpty()) return false;
        return $items->toArray();
    }

    public static function getSmittyItemsWithIcons(string $form)
    {
        $items = \App\Models\SmittyItem::where('form_name', strtolower($form))
            ->orderBy('sort_order')
            ->get(['item_name', 'enum_name']);

        if ($items->isEmpty()) return false;

        return $items->map(function ($item) {
            // SMITTY_METAL -> smittyMetal
            $icon = lcfirst(str_replace('_', '', ucwords(strtolower($item->enum_name), '_')));
            return ['name' => $item->item_name, 'icon' => $icon];
        })->toArray();
    }

    public static function getNumType(string $id): int
    {
        return match(strtolower($id)) {
            'normal'   => 0,
            'fighting' => 1,
            'flying'   => 2,
            'poison'   => 3,
            'ground'   => 4,
            'rock'     => 5,
            'bug'      => 6,
            'ghost'    => 7,
            'steel'    => 8,
            'fire'     => 9,
            'water'    => 10,
            'grass'    => 11,
            'electric' => 12,
            'psychic'  => 13,
            'ice'      => 14,
            'dragon'   => 15,
            'dark'     => 16,
            'fairy'    => 17,
            default    => 0,
        };
    }

}
