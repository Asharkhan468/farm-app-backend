<?php

namespace App\Http\Controllers;

use App\Models\Farm;
use App\Models\Pond;
use Illuminate\Http\Request;

class PondController extends Controller
{
    public function index(Request $request, Farm $farm)
    {
        if ($farm->user_id !== $request->user()->id) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }

        $ponds = Pond::where('farm_id', $farm->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json(['status' => 'success', 'data' => $ponds]);
    }

    public function store(Request $request, Farm $farm)
    {
        if ($farm->user_id !== $request->user()->id) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'pond_identifier'                    => 'required|string|max:255',
            'length'                             => 'required|numeric|min:0',
            'width'                              => 'required|numeric|min:0',
            'depth'                              => 'required|numeric|min:0',
            'water_capacity'                     => 'nullable|numeric|min:0',
            'hatchery_reference'                 => 'nullable|string|max:255',
            'species_stocked'                    => 'nullable|array',
            'species_stocked.*.species_name'     => 'required_with:species_stocked|string',
            'species_stocked.*.quantity'         => 'required_with:species_stocked|string',
            'species_stocked.*.per_piece_weight' => 'required_with:species_stocked|string',
            'species_stocked.*.total_biomass'    => 'required_with:species_stocked|string',
            'stocking_date'                      => 'required|date',
            'status'                             => 'nullable|in:active,harvested',
        ]);

        $validated['farm_id']   = $farm->id;
        $validated['culture_id'] = 'CULT-' . time() . '-' . uniqid();
        $validated['status']    = $validated['status'] ?? 'active';

        $pond = Pond::create($validated);

        return response()->json(['status' => 'success', 'message' => 'Pond added', 'data' => $pond], 201);
    }

    public function show(Request $request, Pond $pond)
    {
        $farm = Farm::findOrFail($pond->farm_id);
        if ($farm->user_id !== $request->user()->id) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }

        return response()->json(['status' => 'success', 'data' => $pond]);
    }

    public function update(Request $request, Pond $pond)
    {
        $farm = Farm::findOrFail($pond->farm_id);
        if ($farm->user_id !== $request->user()->id) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'pond_identifier'  => 'sometimes|string|max:255',
            'length'           => 'sometimes|numeric|min:0',
            'width'            => 'sometimes|numeric|min:0',
            'depth'            => 'sometimes|numeric|min:0',
            'water_capacity'   => 'nullable|numeric|min:0',
            'hatchery_reference' => 'nullable|string|max:255',
            'species_stocked'  => 'nullable|array',
            'stocking_date'    => 'sometimes|date',
            'status'           => 'nullable|in:active,harvested',
        ]);

        $pond->update($validated);

        return response()->json(['status' => 'success', 'message' => 'Pond updated', 'data' => $pond]);
    }

    public function destroy(Request $request, Pond $pond)
    {
        $farm = Farm::findOrFail($pond->farm_id);
        if ($farm->user_id !== $request->user()->id) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }

        $pond->delete();

        return response()->json(['status' => 'success', 'message' => 'Pond deleted']);
    }
}
