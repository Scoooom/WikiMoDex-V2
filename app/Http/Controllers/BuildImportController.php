<?php

namespace App\Http\Controllers;

use App\Models\CommunityBuild;
use App\Services\ImportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BuildImportController extends Controller
{
    /**
     * Show the import page (file upload form).
     */
    public function index()
    {
        abort_unless(Auth::check(), 403, 'Login required');
        return view('build-import');
    }

    /**
     * Accept a PRSV upload, decode it, return slot previews.
     * Stores the decoded data in session temporarily.
     */
    public function preview(Request $request)
    {
        abort_unless(Auth::check(), 403, 'Login required');

        $request->validate([
            'prsv' => 'required|file|max:10240', // 10MB max
        ]);

        try {
            $path  = $request->file('prsv')->getRealPath();
            $slots = ImportService::decodeFile($path);

            // Store raw session data in PHP session for the create step
            $content = file_get_contents($path);
            session(['prsv_data' => $content]);

            $nonEmpty = array_filter($slots);
            if (empty($nonEmpty)) {
                return back()->withErrors(['prsv' => 'No active save slots found in this file.']);
            }

            return view('build-import-preview', compact('slots'));
        } catch (\Exception $e) {
            return back()->withErrors(['prsv' => 'Could not read save file: ' . $e->getMessage()]);
        }
    }

    /**
     * Create a build from a selected slot.
     */
    public function create(Request $request)
    {
        abort_unless(Auth::check(), 403, 'Login required');

        $request->validate([
            'slot'  => 'required|integer|min:0|max:4',
            'title' => 'required|string|max:80',
        ]);

        $prsvData = session('prsv_data');
        if (!$prsvData) {
            return redirect('/builds/import.html')
                ->withErrors(['prsv' => 'Session expired. Please upload your save file again.']);
        }

        // Write to temp file for ImportService
        $tmpPath = tempnam(sys_get_temp_dir(), 'prsv_');
        file_put_contents($tmpPath, $prsvData);

        try {
            $team = ImportService::parseSlot($tmpPath, (int)$request->slot);
        } catch (\Exception $e) {
            unlink($tmpPath);
            return redirect('/builds/import.html')
                ->withErrors(['prsv' => 'Failed to parse slot: ' . $e->getMessage()]);
        } finally {
            @unlink($tmpPath);
        }

        $user = Auth::user();
        $slug = CommunityBuild::generateSlug($request->title, $user->username);

        $build = CommunityBuild::create([
            'slug'        => $slug,
            'title'       => $request->title,
            'description' => 'Imported from save file.',
            'user_id'     => $user->id,
            'team'        => $team,
        ]);

        session()->forget('prsv_data');

        return redirect("/build/{$build->slug}.html")
            ->with('success', 'Build imported successfully!');
    }
}
