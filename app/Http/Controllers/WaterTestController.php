<?php

namespace App\Http\Controllers;

use App\Models\Farm;
use App\Models\Pond;
use App\Models\WaterTest;
use App\Models\WaterQualityThreshold;
use Illuminate\Http\Request;

class WaterTestController extends Controller
{
    // ── List by farm ──────────────────────────────────────────────────────────

    public function indexByFarm(Request $request, Farm $farm)
    {
        if ($farm->user_id !== $request->user()->id) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }

        $tests = WaterTest::where('farm_id', $farm->id)
            ->with(['images', 'pond:id,pond_identifier'])
            ->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json(['status' => 'success', 'data' => $tests]);
    }

    // ── List by pond ──────────────────────────────────────────────────────────

    public function indexByPond(Request $request, Pond $pond)
    {
        $farm = Farm::findOrFail($pond->farm_id);
        if ($farm->user_id !== $request->user()->id) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }

        $tests = WaterTest::where('pond_id', $pond->id)
            ->with(['images', 'pond:id,pond_identifier'])
            ->orderBy('date', 'desc')
            ->get();

        return response()->json(['status' => 'success', 'data' => $tests]);
    }

    // ── Create ────────────────────────────────────────────────────────────────

    public function store(Request $request, Farm $farm)
    {
        if ($farm->user_id !== $request->user()->id) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'pond_id'             => 'nullable|exists:ponds,id',
            'culture_id'          => 'nullable|string|max:100',
            'agent_name'          => 'nullable|string|max:255',
            // Core
            'ph'                  => 'required|numeric|min:0|max:14',
            'oxygen'              => 'required|numeric|min:0',
            'temperature'         => 'required|numeric',
            'ammonia'             => 'nullable|numeric|min:0',
            'nitrite'             => 'nullable|numeric|min:0',
            'nitrate'             => 'nullable|numeric|min:0',
            // Extended
            'alkalinity'          => 'nullable|numeric|min:0',
            'hardness'            => 'nullable|numeric|min:0',
            'turbidity'           => 'nullable|numeric|min:0',
            'salinity'            => 'nullable|numeric|min:0',
            'tds'                 => 'nullable|numeric|min:0',
            'co2'                 => 'nullable|numeric|min:0',
            'hydrogen_sulfide'    => 'nullable|numeric|min:0',
            'phosphate'           => 'nullable|numeric|min:0',
            // Flexible
            'custom_tests'        => 'nullable|array',
            'custom_tests.*.testName'  => 'required_with:custom_tests|string',
            'custom_tests.*.testValue' => 'required_with:custom_tests|string',
            // Diagnosis
            'parameter_reasons'   => 'nullable|array',
            'parameter_direction' => 'nullable|in:up,down',
            // Meta
            'note'                => 'nullable|string',
            'time_recorded'       => 'nullable|string',
            'date'                => 'required|date',
        ]);

        $validated['farm_id'] = $farm->id;

        // Auto-compute severity against the best-matching threshold
        $test = new WaterTest($validated);
        $threshold = WaterQualityThreshold::findFor(
            $farm->id,
            $validated['pond_id'] ?? null
        );
        $validated['overall_status'] = $test->computeStatus($threshold);

        $test = WaterTest::create($validated);

        return response()->json([
            'status'  => 'success',
            'message' => 'Water test recorded',
            'data'    => $test->load('images'),
        ], 201);
    }

    // ── Delete ────────────────────────────────────────────────────────────────

    public function destroy(Request $request, WaterTest $waterTest)
    {
        $farm = Farm::findOrFail($waterTest->farm_id);
        if ($farm->user_id !== $request->user()->id) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }

        $waterTest->delete();

        return response()->json(['status' => 'success', 'message' => 'Water test deleted']);
    }
}
