<?php

namespace App\Http\Controllers;

use App\Models\Farm;
use App\Models\Pond;
use App\Models\Harvest;
use Illuminate\Http\Request;

class HarvestController extends Controller
{
    public function indexByFarm(Request $request, Farm $farm)
    {
        if ($farm->user_id !== $request->user()->id) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }

        $harvests = Harvest::where('farm_id', $farm->id)
            ->with('pond:id,pond_identifier')
            ->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json(['status' => 'success', 'data' => $harvests]);
    }

    public function indexByPond(Request $request, Pond $pond)
    {
        $farm = Farm::findOrFail($pond->farm_id);
        if ($farm->user_id !== $request->user()->id) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }

        $harvests = Harvest::where('pond_id', $pond->id)
            ->with('pond:id,pond_identifier')
            ->orderBy('date', 'desc')
            ->get();

        return response()->json(['status' => 'success', 'data' => $harvests]);
    }

    public function store(Request $request, Farm $farm)
    {
        if ($farm->user_id !== $request->user()->id) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'pond_id'                    => 'required|exists:ponds,id',
            'culture_id'                 => 'nullable|string|max:255',
            'harvest_type'               => 'required|in:partial,final',
            'species'                    => 'required|string|max:255',
            'size_entries'               => 'required|array|min:1',
            'size_entries.*.size'        => 'required|string',
            'size_entries.*.quantity'    => 'required|string',
            'total_calculated_quantity'  => 'nullable|numeric|min:0',
            'total_calculated_weight'    => 'nullable|numeric|min:0',
            'calculated_avg_size'        => 'nullable|numeric|min:0',
            'total_feed'                 => 'nullable|string',
            'date'                       => 'required|date',
        ]);

        $validated['farm_id'] = $farm->id;

        $harvest = Harvest::create($validated);

        return response()->json(['status' => 'success', 'message' => 'Harvest recorded', 'data' => $harvest], 201);
    }

    public function destroy(Request $request, Harvest $harvest)
    {
        $farm = Farm::findOrFail($harvest->farm_id);
        if ($farm->user_id !== $request->user()->id) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }

        $harvest->delete();

        return response()->json(['status' => 'success', 'message' => 'Harvest deleted']);
    }
}
