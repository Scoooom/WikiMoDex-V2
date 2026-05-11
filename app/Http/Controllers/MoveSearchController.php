<?php

namespace App\Http\Controllers;

use App\Models\CoreMove;
use Illuminate\Http\Request;

class MoveSearchController extends Controller
{
    public function search(Request $request)
    {
        $q = trim($request->input('q', ''));
        if (strlen($q) < 2) {
            return response()->json([]);
        }

        $results = CoreMove::where('name', 'like', "%{$q}%")
            ->orderByRaw("CASE WHEN name LIKE ? THEN 0 ELSE 1 END", ["{$q}%"])
            ->orderBy('is_smitty')
            ->orderBy('name')
            ->limit(20)
            ->get()
            ->map(fn($m) => [
                'label'          => $m->name,
                'value'          => $m->name,
                'is_smitty'      => $m->is_smitty,
                'category'       => $m->is_smitty ? 'SMITTY Move' : 'Move',
                'type'           => $m->type,
                'type_name'      => $m->type_name,
                'move_category'  => $m->category,
                'power'          => $m->power,
                'accuracy'       => $m->accuracy,
                'pp'             => $m->pp,
                'is_dynamic_type'=> $m->is_dynamic_type,
            ]);

        return response()->json($results)->header('Cache-Control', 'no-store');
    }
}
