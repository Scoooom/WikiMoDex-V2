<?php

namespace App\Http\Controllers;

use App\Models\Glitch;

class SpriteController extends Controller
{
    public function front($id)
    {
        return $this->serveSprite($id, 'front');
    }

    public function back($id)
    {
        return $this->serveSprite($id, 'back');
    }

/* */
    private function serveSprite($id, $type)
    {
        $glitch = Glitch::findOrFail($id);
        $raw = $type === 'front' ? $glitch->front : $glitch->back;

        // Strip the data URL prefix if present
        if (str_contains($raw, ',')) {
            $raw = explode(',', $raw, 2)[1];
        }

        $data = base64_decode($raw);

        return response($data, 200, [
            'Content-Type' => 'image/png',
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }
/* */
    public function coreFront($name)
    {
        return $this->serveCoreSprite($name, false);
    }

    public function coreBack($name)
    {
        return $this->serveCoreSprite($name, true);
    }

    public function pokevoidSprite($file)
    {
        // Only allow safe filenames — dex numbers, optional suffixes, .png
        if (!preg_match('/^[\w\-]+\.png$/', $file)) {
            abort(404);
        }

        $path = base_path("pokevoid/public/images/pokemon/{$file}");

        if (!file_exists($path)) {
            abort(404);
        }

        return response()->file($path, [
            'Content-Type'                => 'image/png',
            'Access-Control-Allow-Origin' => '*',
        ]);
    }

    private function serveCoreSprite($name, $back = false)
    {
        $name = strtolower(str_replace('Ω', 'ω', $name));
        $suffix = $back ? '_back' : '';
        $path = storage_path('app/glitchimgs/' . $name . $suffix . '.png');

        if (!file_exists($path)) {
            abort(404);
        }

        return response()->file($path, [
            'Content-Type'  => 'image/png',
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }


}
