<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Services\RivalService;
use App\Services\PokemonService;

class Glitch extends Model
{
    protected $table = 'glitches';
    protected $guarded = [];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function likes()
    {
        return $this->hasMany(GlitchLike::class, 'glitchID');
    }

    public function dislikes()
    {
        return $this->hasMany(GlitchDislike::class, 'glitchID');
    }

    public function getRating()
    {
        return $this->likes()->count() - $this->dislikes()->count();
    }

    public function getJsonData()
    {
        return json_decode($this->json_data);
    }

    public function getRivals($returnString = false)
    {
        $data = $this->getJsonData();
        $rivals = $data->unlockConditions->rivalTrainerTypes;
        if ($returnString) {
            $string = RivalService::getRival($rivals[0]);
            unset($rivals[0]);
            foreach ($rivals as $rival) {
                $string .= ', ' . RivalService::getRival($rival);
            }
            return $string;
        }
        return $rivals;
    }

    public function getStatBoostType()
    {
        $type = $this->getJsonData();
        return $type->stats->distributionType;
    }

    public function getStatBoostEn()
    {
        switch ($this->getStatBoostType()) {
            case 'twoPriority':
                return 'Two Priority (1st: 40%; 2nd: 40%; 3rd: 20%)';
            case 'even':
                return 'Even (All: 33%)';
            case 'scaling':
                return 'Scaling (1st: 45%; 2nd: 35%; 3rd: 20%)';
            case 'topPriority':
                return 'Top Priority (1st: 40%; 2nd: 30%; 3rd: 30%)';
            default:
                return 'Unknown...';
        }
    }

    public function getAbilityOne()
    {
        $data = $this->getJsonData();
        return PokemonService::getAbility($data->abilities[0], 1);
    }

    public function getAbilityTwo()
    {
        $data = $this->getJsonData();
        return PokemonService::getAbility($data->abilities[1], 1);
    }

    public function getAbilityHA()
    {
        $data = $this->getJsonData();
        return PokemonService::getAbility($data->abilities[2], 1);
    }

    public function getOGMon()
    {
        $data = $this->getJsonData();
        return PokemonService::getMon($data->speciesId);
    }

    public function getOGStats()
    {
        $ogMon = $this->getOGMon();
        $ogStats = [];
        foreach ($ogMon->stats as $stat) {
            $indice = match($stat->stat->name) {
                'hp' => 0,
                'attack' => 1,
                'defense' => 2,
                'special-attack' => 3,
                'special-defense' => 4,
                'speed' => 5,
                default => 0
            };
            $ogStats[$indice] = [
                'value' => $stat->base_stat,
                'percent' => floor(($stat->base_stat / 255) * 100)
            ];
        }
        return $ogStats;
    }

    public function calculateTotalIncrease($bst)
    {
        $newTotal = $bst;
        $increase = 0;
        do {
            $currentIncrease = $newTotal * 0.2;
            $newTotal += $currentIncrease;
            $increase += $currentIncrease;
        } while ($newTotal < 500);
        return $increase;
    }

    public function adjustStats($ogStats, $totalIncrease)
    {
        $data = $this->getJsonData();
        $boosted = $ogStats;
        $boost = $totalIncrease;

        switch ($this->getStatBoostType()) {
            case 'twoPriority':
                $s1 = $data->stats->statsToBoost[0];
                $s2 = $data->stats->statsToBoost[1];
                $s3 = $data->stats->statsToBoost[2];
                $boosted[$s1]['value'] = round($boost * 0.4) + $boosted[$s1]['value'];
                $boosted[$s1]['percent'] = floor(($boosted[$s1]['value'] / 255) * 100);
                $boosted[$s2]['value'] = round($boost * 0.4) + $boosted[$s2]['value'];
                $boosted[$s2]['percent'] = floor(($boosted[$s2]['value'] / 255) * 100);
                $boosted[$s3]['value'] = round($boost * 0.2) + $boosted[$s3]['value'];
                $boosted[$s3]['percent'] = floor(($boosted[$s3]['value'] / 255) * 100);
                break;
            case 'even':
                $s1 = $data->stats->statsToBoost[0];
                $s2 = $data->stats->statsToBoost[1];
                $s3 = $data->stats->statsToBoost[2];
                $boosted[$s1]['value'] = round($boost * 0.33) + $boosted[$s1]['value'];
                $boosted[$s1]['percent'] = floor(($boosted[$s1]['value'] / 255) * 100);
                $boosted[$s2]['value'] = round($boost * 0.33) + $boosted[$s2]['value'];
                $boosted[$s2]['percent'] = floor(($boosted[$s2]['value'] / 255) * 100);
                $boosted[$s3]['value'] = round($boost * 0.33) + $boosted[$s3]['value'];
                $boosted[$s3]['percent'] = floor(($boosted[$s3]['value'] / 255) * 100);
                break;
            case 'scaling':
                $s1 = $data->stats->statsToBoost[0];
                $s2 = $data->stats->statsToBoost[1];
                $s3 = $data->stats->statsToBoost[2];
                $boosted[$s1]['value'] = round($boost * 0.45) + $boosted[$s1]['value'];
                $boosted[$s1]['percent'] = floor(($boosted[$s1]['value'] / 255) * 100);
                $boosted[$s2]['value'] = round($boost * 0.35) + $boosted[$s2]['value'];
                $boosted[$s2]['percent'] = floor(($boosted[$s2]['value'] / 255) * 100);
                $boosted[$s3]['value'] = round($boost * 0.20) + $boosted[$s3]['value'];
                $boosted[$s3]['percent'] = floor(($boosted[$s3]['value'] / 255) * 100);
                break;
            case 'topPriority':
                $s1 = $data->stats->statsToBoost[0];
                $s2 = $data->stats->statsToBoost[1];
                $s3 = $data->stats->statsToBoost[2];
                $boosted[$s1]['value'] = round($boost * 0.4) + $boosted[$s1]['value'];
                $boosted[$s1]['percent'] = floor(($boosted[$s1]['value'] / 255) * 100);
                $boosted[$s2]['value'] = round($boost * 0.3) + $boosted[$s2]['value'];
                $boosted[$s2]['percent'] = floor(($boosted[$s2]['value'] / 255) * 100);
                $boosted[$s3]['value'] = round($boost * 0.3) + $boosted[$s3]['value'];
                $boosted[$s3]['percent'] = floor(($boosted[$s3]['value'] / 255) * 100);
                break;
            default:
                return $ogStats;
        }

        return $boosted;
    }

}
