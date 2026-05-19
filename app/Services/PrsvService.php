<?php

namespace App\Services;

class PrsvService
{
    private string $raw;
    public $decrypted = null;

    public function __construct($raw, $decrypted)
    {
        $this->raw = $raw;
        $this->decrypted = json_decode(base64_decode($decrypted));
    }

    public function getSave()
    {
        return $this->raw;
    }

    private static function evpBytesToKey(string $password, string $salt, int $keyLen, int $ivLen): array
    {
        $derived = '';
        $block = '';
        while (strlen($derived) < $keyLen + $ivLen) {
            $block = md5($block . $password . $salt, true);
            $derived .= $block;
        }
        return [
            'key' => substr($derived, 0, $keyLen),
            'iv'  => substr($derived, $keyLen, $ivLen),
        ];
    }

    public static function decrypt($file,$returnRaw = false)
    {
        $raw = base64_decode(file_get_contents($file));

        $salt = substr($raw, 8, 8);
        $encrypted = substr($raw, 16);

        $derived = self::evpBytesToKey(env('PRSV_ENCRYPTION_KEY'), $salt, 32, 16);

        $decrypted = openssl_decrypt(
            $encrypted,
            'aes-256-cbc',
            $derived['key'],
            OPENSSL_RAW_DATA,
            $derived['iv']
        );
        if ($returnRaw) return $decrypted;
        $data = json_decode($decrypted);
        $data->systemData = json_decode($data->systemData);

        for ($i = 0; $i < count($data->sessionData); $i++) {
            $data->sessionData[$i] = json_decode($data->sessionData[$i]);
        }

        return $data;
    }

    public function getSystemData()
    {
        return $this->decrypted->systemData ?? null;
    }

    public function getDefeatedRivals()
    {
        $defeated = $this->getSystemData()->defeatedRivals;
        $rivals = RivalService::getRivals();
        $return = [];
        foreach ($rivals as $key => $name) {
            $defeat = in_array($key, $defeated) ? 'true' : 'false';
            $return[] = ['name' => $name, 'defeated' => $defeat, 'key' => $key];
        }
        if (count($defeated) == 28) $return['allDefeated'] = 'true';
        else $return['allDefeated'] = 'false';
        return $return;
    }

    public function getGlitchUnlocks()
    {
        $quests = $this->getSystemData()->questUnlockables;
        $questIDs = [];
        $completed = [];
        foreach ($quests as $quest) {
            if ($quest->state == 2 && $this->isGlitchQuest($quest)) {
                $k = BuiltInService::loadCoreGlitchByMonID($quest->questUnlockData->rewardId);
                if ($k !== null && !in_array($k->name, $questIDs)) {
                    $completed[] = $k;
                    $questIDs[] = $k->name;
                }
            }
        }
        return $completed;
    }

    public function getSmittyUnlocks()
    {
        $quests = $this->getSystemData()->questUnlockables;
        $questIDs = [];
        $completed = [];
        foreach ($quests as $quest) {
            if ($quest->state == 2 && $this->isSmittyQuest($quest)) {
                $k = BuiltInService::loadCoreSmittyByMonID($quest->questUnlockData->rewardId);
                if ($k !== null && !in_array($k->name, $questIDs)) {
                    $completed[] = $k;
                    $questIDs[] = $k->name;
                }
            }
        }
        return $completed;
    }

    public function getFormUnlocks()
    {
        return [
            'uniSmittyUnlocks'  => $this->getSystemData()->uniSmittyUnlocks,
            'modFormsUnlocked'  => $this->getSystemData()->modFormsUnlocked,
        ];
    }

    public function getDecrypted()
    {
        return $this->decrypted;
    }

    public function getSessionData()
    {
        return $this->decrypted->sessionData;
    }

    private function isGlitchQuest($quest): bool
    {
        $rewardType = $quest->questUnlockData->rewardType;
        return ($rewardType == 0 || ($rewardType >= 4 && $rewardType <= 7));
    }

    private function isSmittyQuest($quest): bool
    {
        $rewardType = $quest->questUnlockData->rewardType;
        return ($rewardType == 8 || $rewardType == 9);
    }
}
