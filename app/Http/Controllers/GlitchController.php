<?php

namespace App\Http\Controllers;

use App\Models\Glitch;
use App\Models\User;
use App\Services\PokemonService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GlitchController extends Controller
{
    public function index()
    {
        $glitches = Glitch::all();
        return view('gallery', compact('glitches'));
    }

    public function show($form, $id)
    {
        $glitch = Glitch::findOrFail($id);
        $mon2 = $glitch->getJsonData();
        $rivals = $glitch->getRivals(true);
        $ogMon = $glitch->getOGMon();

        unset($ogMon->moves);
        unset($ogMon->game_indices);
        unset($ogMon->held_items);
        unset($ogMon->past_abilities);
        $ogMon->name = ucwords($ogMon->name);

        $ogStats = $glitch->getOGStats();
        $ogBST = array_sum(array_column($ogStats, 'value'));
        $boostedStats = $glitch->adjustStats($ogStats, $glitch->calculateTotalIncrease($ogBST));
        $boostedBST = array_sum(array_column($boostedStats, 'value'));

        $creator = User::find($glitch->created_by);
        $rating = $glitch->getRating();
        $abilityOne = $glitch->getAbilityOne();
        $abilityTwo = $glitch->getAbilityTwo();
        $abilityHA = $glitch->getAbilityHA();
        $statBalance = $glitch->getStatBoostEn();

        $userLikesGlitch = Auth::check() ? Auth::user()->likesGlitch($glitch->id) : false;

        return view('glitch', compact(
            'glitch', 'mon2', 'rivals', 'ogMon', 'ogStats', 'ogBST',
            'boostedStats', 'boostedBST', 'creator', 'rating',
            'abilityOne', 'abilityTwo', 'abilityHA', 'statBalance',
            'userLikesGlitch'
        ));
    }

    public function create()
    {
        if (!Auth::check()) {
            return redirect('/');
        }
        return view('create');
    }

    public function store(Request $request)
    {
        if (!Auth::check()) {
            return redirect('/');
        }

        $request->validate([
            'pokeData' => 'required|file|max:300|mimetypes:application/json',
        ]);

        $file = $request->file('pokeData');
        $glitchRaw = file_get_contents($file->getRealPath());
        $glitch = json_decode($glitchRaw);

        if (!$glitch) {
            return back()->with('error', 'Corrupt file. Please try again!');
        }

        $existing = Glitch::where('name', $glitch->formName)->first();
        if ($existing) {
            $creator = User::find($existing->created_by);
            return back()->with('error', 'This Glitch is already uploaded by ' . $creator->username . '!');
        }

        $sprites = $glitch->sprites;
        $newGlitch = new Glitch();
        $newGlitch->json_data = json_encode($glitch);
        $newGlitch->created_by = Auth::user()->id;
        $newGlitch->name = $glitch->formName;
        $newGlitch->front = $sprites->front;
        $newGlitch->back = $sprites->back;
        $newGlitch->icon = $sprites->icon;
        $newGlitch->filename = $file->getClientOriginalName();
        $newGlitch->save();

        return redirect('/g:' . urlencode(str_replace(' ', '', $glitch->formName)) . ':' . $newGlitch->id . '.html');
    }

    public function download($id)
    {
        $glitch = Glitch::findOrFail($id);
        return response()->streamDownload(function () use ($glitch) {
            echo $glitch->json_data;
        }, $glitch->filename, [
            'Content-Type' => 'application/octet-stream',
        ]);
    }

    public function galleryCore()
    {
        $glitches = \App\Services\BuiltInService::load();
        return view('galleryCore', ['glitches' => (array) $glitches]);
    }

    public function gallerySmitty()
    {
        $glitches = \App\Services\BuiltInService::smittyLoad();
        return view('gallerySmitty', ['glitches' => (array) $glitches]);
    }

    public function gallerySmittyForm()
    {
        $glitches = \App\Services\BuiltInService::smittyFormLoad();
        return view('gallerySmittyForm', ['glitches' => (array) $glitches]);
    }

    public function coreMon($form)
    {
        $lowerName = strtolower(str_replace('Ω', '_omega', $form));
        $mon = \App\Services\BuiltInService::loadCore($lowerName);
        if (!$mon) abort(404);

        $mons = '';
        if (!empty($mon->og_mon)) {
            $og = explode(',', $mon->og_mon);
            foreach ($og as $ogMonId) {
                try {
                    $ogMon = \App\Services\PokemonService::getMon(trim($ogMonId));
                    $mons .= ucwords(str_replace('-', ' ', $ogMon->name)) . ', ';
                } catch (\Exception $e) {
                    $mons .= 'Unknown, ';
                }
            }
            $mons = rtrim(trim($mons), ',');
        }

        return view('coreMon', compact('mon', 'mons'));
    }

    public function smittyMon($form)
    {
        $lowerName = strtolower($form);
        $mon = \App\Services\BuiltInService::loadSmitty($lowerName);
        if (!$mon) abort(404);

        $mons = '';
        if (!empty($mon->og_mon)) {
            $og = explode(',', $mon->og_mon);
            foreach ($og as $ogMonId) {
                try {
                    $ogMon = \App\Services\PokemonService::getMon(trim($ogMonId));
                    $mons .= ucwords(str_replace('-', ' ', $ogMon->name)) . ', ';
                } catch (\Exception $e) {
                    $mons .= 'Unknown, ';
                }
            }
            $mons = rtrim(trim($mons), ',');
        }

        $items = \App\Services\BuiltInService::getSmittyItems($form);
        $items = $items !== false ? implode(', ', $items) : 'Unknown! Please contact scooom on Discord!';
        $code = $mon->form_code ?? '';

        return view('smittyMon', compact('mon', 'mons', 'items', 'code'));
    }

    public function smittyFormMon($form)
    {
        $lowerName = strtolower($form);
        $mon = \App\Services\BuiltInService::loadSmittyForm($lowerName);
        if (!$mon) abort(404);

        $mons = '';
        if (!empty($mon->og_mon)) {
            $og = explode(',', $mon->og_mon);
            foreach ($og as $ogMonId) {
                try {
                    $ogMon = \App\Services\PokemonService::getMon(trim($ogMonId));
                    $mons .= ucwords(str_replace('-', ' ', $ogMon->name)) . ', ';
                } catch (\Exception $e) {
                    $mons .= 'Unknown, ';
                }
            }
            $mons = rtrim(trim($mons), ',');
        }

        $items = \App\Services\BuiltInService::getSmittyItems($form);
        $items = $items !== false ? implode(', ', $items) : 'Unknown! Please contact scooom on Discord!';
        $code = $mon->form_code ?? '';

        return view('smittyFormMon', compact('mon', 'mons', 'items', 'code'));
    }
}
