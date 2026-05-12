<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserLike;
use App\Services\PrsvService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    private function isOwner(User $user): bool
    {
        if (!Auth::check()) return false;
        $authUser = Auth::user();
        return $authUser->id === $user->id || $authUser->isAdmin();
    }

    public function profile($username)
    {
        $user = User::where('username', $username)->firstOrFail();
        $isOwner = $this->isOwner($user);
        $likes = UserLike::where('creatorID', $user->id)->count();
        $glitches = $user->glitches;

        return view('profile', compact('user', 'isOwner', 'likes', 'glitches'));
    }

    public function uploadSave(Request $request, $username)
    {
        $user = User::where('username', $username)->firstOrFail();

        if (!$this->isOwner($user)) {
            return redirect('/');
        }

        $file = $request->file('saveFile');

        try {
            $decrypt = PrsvService::decrypt($file->getRealPath());
        } catch (\Throwable $e) {
            return redirect('/u:' . $username . '.html')->with('error', 'Invalid save file — could not decrypt.');
        }

        if (empty($decrypt->systemData)) {
            return redirect('/u:' . $username . '.html')->with('error', 'Invalid save file — this looks like a session save, not a system save. Please upload your system save file.');
        }

        $user->raw_prsv = file_get_contents($file->getRealPath());
        $user->b64_prsv = base64_encode(json_encode($decrypt));
        $user->save();

        $imgPath = storage_path('app/trainer-cards/' . $username . '.png');
        if (file_exists($imgPath)) unlink($imgPath);

        \Illuminate\Support\Facades\Artisan::call('cf:purge', [
            '--url' => [
                rtrim(config('services.cloudflare.base_url'), '/') . '/trainercard:' . $username . '.html',
                rtrim(config('services.cloudflare.base_url'), '/') . '/trainercard-img:' . $username . '.png',
            ],
        ]);

        return redirect('/u:' . $username . '.html')->with('success', 'Save File Uploaded');
    }

    public function deleteSave($username)
    {
        $user = User::where('username', $username)->firstOrFail();

        if (!$this->isOwner($user)) {
            return redirect('/');
        }

        $user->raw_prsv = null;
        $user->b64_prsv = null;
        $user->save();

        $imgPath = storage_path('app/trainer-cards/' . $username . '.png');
        if (file_exists($imgPath)) unlink($imgPath);

        \Illuminate\Support\Facades\Artisan::call('cf:purge', [
            '--url' => [
                rtrim(config('services.cloudflare.base_url'), '/') . '/trainercard:' . $username . '.html',
                rtrim(config('services.cloudflare.base_url'), '/') . '/trainercard-img:' . $username . '.png',
            ],
        ]);

        return redirect('/u:' . $username . '.html')->with('success', 'Save File Deleted');
    }

    public function downloadSave($username)
    {
        $user = User::where('username', $username)->firstOrFail();

        if (!$this->isOwner($user)) {
            return redirect('/');
        }

        return response()->streamDownload(function () use ($user) {
            echo $user->raw_prsv;
        }, $user->username . '.prsv', [
            'Content-Type' => 'application/octet-stream',
        ]);
    }

    public function trainerCard($username)
    {
        $user = User::where('username', $username)->firstOrFail();
        return view('trainercard', compact('user'));
    }

    public function handleProfilePost(Request $request, $username)
    {
        $action = $request->input('action');

        switch ($action) {
            case 'uploadNew':
                return $this->uploadSave($request, $username);
            case 'delSave':
                return $this->deleteSave($username);
            case 'dlSave':
                return $this->downloadSave($username);
            case 'setTcColor':
                return $this->setTcColor($request, $username);
            default:
                return redirect('/u:' . $username . '.html');
        }
    }

    public function setTcColor(Request $request, $username)
    {
        $user = User::where('username', $username)->firstOrFail();
        if (!$this->isOwner($user)) return redirect('/');

        $allowed = ['blue', 'red', 'green', 'gold', 'purple', 'black', 'maroon'];
        $color = $request->input('tc_color');
        if (!in_array($color, $allowed)) $color = 'maroon';

        $user->tc_color = $color;
        $user->save();

        // Purge cached image
        $imgPath = storage_path('app/trainer-cards/' . $username . '.png');
        if (file_exists($imgPath)) unlink($imgPath);

        \Illuminate\Support\Facades\Artisan::call('cf:purge', [
            '--url' => [
                rtrim(config('services.cloudflare.base_url'), '/') . '/trainercard:' . $username . '.html',
                rtrim(config('services.cloudflare.base_url'), '/') . '/trainercard-img:' . $username . '.png',
            ],
        ]);

        return redirect('/u:' . $username . '.html')->with('success', 'Trainer card color updated!');
    }

    public function trainerCardImage($username)
    {
        $user = User::where('username', $username)->firstOrFail();

        $outDir = storage_path('app/trainer-cards');
        if (!is_dir($outDir)) mkdir($outDir, 0755, true);
        $outPath = $outDir . '/' . $username . '.png';

        if (!file_exists($outPath)) {
            $save = $user->getSave();
            if (is_array($save) || $save->getSystemData() === null) {
                abort(404);
            }

            $defeatedRivals = $save->getDefeatedRivals();
            $glitchUnlocks  = $save->getGlitchUnlocks();
            $smittyUnlocks  = $save->getSmittyUnlocks();
            $formUnlocks    = $save->getFormUnlocks();

            $totalRivals  = count(array_filter($defeatedRivals, fn($r) => !is_string(array_search($r, $defeatedRivals))));
            $beatenRivals = count(array_filter($defeatedRivals, fn($r) => !is_string(array_search($r, $defeatedRivals)) && $r['defeated'] === 'true'));

            $modCount = collect($formUnlocks['modFormsUnlocked'])->map(function($unlock) {
                $name = preg_replace('/(.*)_(.*)/', '$2', $unlock);
                return \App\Models\Glitch::where('name', str_replace(' ', '', $name))->first();
            })->filter()->count();

            $uniSmittyCount = collect($formUnlocks['uniSmittyUnlocks'])->filter()->map(function($unlock) {
                $name = preg_replace('/(.*?)_(.*)/', '$2', $unlock);
                return \App\Services\BuiltInService::loadSmitty(str_replace(' ', '', $name));
            })->filter()->count();

            $baseUrl = rtrim(config('services.cloudflare.base_url'), '/');

            $rivals = array_values(array_filter($defeatedRivals, fn($r) => !is_string(array_search($r, $defeatedRivals))));
            $rivals = array_map(fn($r) => [
                'name'   => $r['name'],
                'beaten' => $r['defeated'] === 'true',
                'imgUrl' => $baseUrl . '/rivals/' . strtolower(str_replace(' ', '_', $r['name'])) . '.png',
            ], $rivals);

            $cardData = json_encode([
                'username'      => $user->username,
                'userId'        => $user->id,
                'avatarUrl'     => $user->getAvatarURL(),
                'color'         => $user->tc_color ?? 'maroon',
                'glitchCount'   => count($glitchUnlocks) + $modCount,
                'smittyCount'   => count($smittyUnlocks) + $uniSmittyCount,
                'submittedCount'=> $user->glitches()->count(),
                'beatenRivals'  => $beatenRivals,
                'totalRivals'   => $totalRivals,
                'rivals'        => $rivals,
            ]);

            $script = base_path('scripts/render_trainer_card.js');
            $cmd = 'node ' . escapeshellarg($script)
                 . ' ' . escapeshellarg($username)
                 . ' ' . escapeshellarg($outPath)
                 . ' ' . escapeshellarg($cardData)
                 . ' 2>&1';

            exec($cmd, $output, $exit);

            if ($exit !== 0 || !file_exists($outPath)) {
                \Illuminate\Support\Facades\Log::error('Trainer card render failed for ' . $username . ': ' . implode("\n", $output));
                abort(500);
            }
        }

        return response()->file($outPath, [
            'Content-Type'  => 'image/png',
            'Cache-Control' => 'public, max-age=0, s-maxage=86400, stale-while-revalidate=60',
        ]);
    }
}
