<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GameItem extends Model
{
    protected $fillable = ['key', 'name', 'description', 'tier', 'pool', 'conditional', 'spawn_condition'];

    protected $casts = ['conditional' => 'boolean'];

    public const TIER_ORDER = ['MEH', 'COMMON', 'GREAT', 'ULTRA', 'ROGUE', 'MASTER', 'LUXURY', 'OMEGA'];

    public const TIER_LABELS = [
        'MEH'    => 'Meh',
        'COMMON' => 'Common',
        'GREAT'  => 'Great',
        'ULTRA'  => 'Ultra',
        'ROGUE'  => 'Rogue',
        'MASTER' => 'Master',
        'LUXURY' => 'Luxury',
        'OMEGA'  => 'Ω Items',
    ];

    public const TIER_COLORS = [
        'MEH'    => '#888888',
        'COMMON' => '#9db8e8',
        'GREAT'  => '#5aabff',
        'ULTRA'  => '#c9a8ff',
        'ROGUE'  => '#e8824a',
        'MASTER' => '#f5d76e',
        'LUXURY' => '#ff6eef',
        'OMEGA'  => '#7c5cbf',
    ];
}

    // Maps DB item key -> icon filename (without .png)
    // Items atlas (items.png): vitamins, plates, drives, soul_dew
    // Smitems atlas (smitems_32.png): switchers, sacrifices, alt_build
    const ICON_MAP = [
        // Vitamins
        'HP_UP'                  => 'hp_up',
        'PROTEIN'                => 'protein',
        'IRON'                   => 'iron',
        'CALCIUM'                => 'calcium',
        'ZINC'                   => 'zinc',
        'CARBOS'                 => 'carbos',
        // Soul Dew
        'SOUL_DEW'               => 'soul_dew',
        // Plates
        'FIST_PLATE'             => 'fist_plate',
        'SKY_PLATE'              => 'sky_plate',
        'TOXIC_PLATE'            => 'toxic_plate',
        'EARTH_PLATE'            => 'earth_plate',
        'STONE_PLATE'            => 'stone_plate',
        'INSECT_PLATE'           => 'insect_plate',
        'SPOOKY_PLATE'           => 'spooky_plate',
        'IRON_PLATE'             => 'iron_plate',
        'FLAME_PLATE'            => 'flame_plate',
        'SPLASH_PLATE'           => 'splash_plate',
        'MEADOW_PLATE'           => 'meadow_plate',
        'ZAP_PLATE'              => 'zap_plate',
        'MIND_PLATE'             => 'mind_plate',
        'ICICLE_PLATE'           => 'icicle_plate',
        'DRACO_PLATE'            => 'draco_plate',
        'DREAD_PLATE'            => 'dread_plate',
        'PIXIE_PLATE'            => 'pixie_plate',
        'BLANK_PLATE'            => 'blank_plate',
        // Drives
        'SHOCK_DRIVE'            => 'shock_drive',
        'BURN_DRIVE'             => 'burn_drive',
        'CHILL_DRIVE'            => 'chill_drive',
        'DOUSE_DRIVE'            => 'douse_drive',
        // Switchers / sacrifices (smitems atlas)
        'STAT_SWITCHER'          => 'glitchStatSwitch',
        'RANDOM_STAT_SWITCHER'   => 'glitchStatSwitch',
        'STAT_SACRIFICE'         => 'glitchStatSwitch',
        'TYPE_SWITCHER'          => 'glitchTypeSwitch',
        'PRIMARY_TYPE_SWITCHER'  => 'glitchTypeSwitch',
        'SECONDARY_TYPE_SWITCHER'=> 'glitchTypeSwitch',
        'TYPE_SACRIFICE'         => 'glitchTypeSwitch',
        'POKEMON_ALT_BUILD'      => 'glitchMasterParts',
    ];

    public function getIconUrl(): ?string
    {
        $filename = self::ICON_MAP[$this->key] ?? null;
        return $filename ? "/item-icon/{$filename}.png" : null;
    }
}
