<?php

namespace App\Http\Controllers;

use App\Models\Ability;
use Illuminate\Http\Request;

class AbilitySearchController extends Controller
{
    public function search(Request $request)
    {
        $q = trim($request->input('q', ''));
        if (strlen($q) < 2) {
            return response()->json([]);
        }

        $results = Ability::where('name', 'like', "%{$q}%")
            ->orderByRaw("CASE WHEN name LIKE ? THEN 0 ELSE 1 END", ["{$q}%"])
            ->limit(15)
            ->get()
            ->map(fn($a) => [
                'label'    => $a->name,
                'value'    => $a->name,
                'category' => 'Ability',
            ]);

        return response()->json($results)->header('Cache-Control', 'no-store');
    }
}
