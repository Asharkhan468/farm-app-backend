<?php

namespace App\Http\Controllers;

use App\Models\Farm;
use App\Models\Pond;
use App\Models\WaterQualityThreshold;
use Illuminate\Http\Request;

class WaterQualityThresholdController extends Controller
{
    // ── List thresholds for a farm ────────────────────────────────────────────

    public function index(Request $request, Farm $farm)
    {
        if ($farm->user_id !== $request->user()->id) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }

        $thresholds = WaterQualityThreshold::where('farm_id', $farm->id)
            ->with('pond:id,pond_identifier')
            ->orderBy('is_default', 'desc')
            ->orderBy('pond_id')
            ->get();

        return response()->json(['status' => 'success', 'data' => $thresholds]);
    }

    // ── Create / update (upsert by farm+pond+species) ─────────────────────────

    public function store(Request $request, Farm $farm)
    {
        if ($farm->user_id !== $request->user()->id) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'pond_id'              => 'nullable|exists:ponds,id',
            'species_name'         => 'nullable|string|max:100',
            'is_default'           => 'boolean',
            'ph_min'               => 'nullable|numeric|min:0|max:14',
            'ph_max'               => 'nullable|numeric|min:0|max:14',
            'oxygen_min'           => 'nullable|numeric|min:0',
            'temperature_min'      => 'nullable|numeric',
            'temperature_max'      => 'nullable|numeric',
            'ammonia_max'          => 'nullable|numeric|min:0',
            'nitrite_max'          => 'nullable|numeric|min:0',
            'nitrate_max'          => 'nullable|numeric|min:0',
            'turbidity_max'        => 'nullable|numeric|min:0',
            'alkalinity_min'       => 'nullable|numeric|min:0',
            'alkalinity_max'       => 'nullable|numeric|min:0',
            'hardness_min'         => 'nullable|numeric|min:0',
            'hardness_max'         => 'nullable|numeric|min:0',
            'hydrogen_sulfide_max' => 'nullable|numeric|min:0',
            'alert_on_breach'      => 'boolean',
        ]);

        $validated['farm_id'] = $farm->id;

        $threshold = WaterQualityThreshold::updateOrCreate(
            [
                'farm_id'      => $farm->id,
                'pond_id'      => $validated['pond_id'] ?? null,
                'species_name' => $validated['species_name'] ?? null,
            ],
            $validated,
        );

        $status = $threshold->wasRecentlyCreated ? 201 : 200;

        return response()->json([
            'status'  => 'success',
            'message' => $threshold->wasRecentlyCreated ? 'Threshold created' : 'Threshold updated',
            'data'    => $threshold,
        ], $status);
    }

    // ── Delete ────────────────────────────────────────────────────────────────

    public function destroy(Request $request, WaterQualityThreshold $threshold)
    {
        $farm = Farm::findOrFail($threshold->farm_id);
        if ($farm->user_id !== $request->user()->id) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }

        $threshold->delete();

        return response()->json(['status' => 'success', 'message' => 'Threshold deleted']);
    }
}
