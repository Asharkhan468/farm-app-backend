<?php

namespace App\Http\Controllers;

use App\Models\Farm;
use Illuminate\Http\Request;

class FarmController extends Controller
{
    public function index(Request $request)
    {
        $farms = Farm::where('user_id', $request->user()->id)
            ->withCount('ponds')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json(['status' => 'success', 'data' => $farms]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $farm = Farm::create([
            'user_id' => $request->user()->id,
            'name'    => $validated['name'],
        ]);

        return response()->json(['status' => 'success', 'message' => 'Farm created', 'data' => $farm], 201);
    }

    public function show(Request $request, Farm $farm)
    {
        if ($farm->user_id !== $request->user()->id) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }

        $farm->load([
            'ponds',
            'waterTests'     => fn($q) => $q->with('pond:id,pond_identifier')->orderBy('date', 'desc'),
            'feedingRecords' => fn($q) => $q->with('pond:id,pond_identifier')->orderBy('date', 'desc'),
            'harvests'       => fn($q) => $q->with('pond:id,pond_identifier')->orderBy('date', 'desc'),
            'pondPhotos',
        ]);

        return response()->json(['status' => 'success', 'data' => $farm]);
    }

    public function update(Request $request, Farm $farm)
    {
        if ($farm->user_id !== $request->user()->id) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $farm->update($validated);

        return response()->json(['status' => 'success', 'message' => 'Farm updated', 'data' => $farm]);
    }

    public function destroy(Request $request, Farm $farm)
    {
        if ($farm->user_id !== $request->user()->id) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }

        $farm->delete();

        return response()->json(['status' => 'success', 'message' => 'Farm deleted']);
    }
}
