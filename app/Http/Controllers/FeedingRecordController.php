<?php

namespace App\Http\Controllers;

use App\Models\Farm;
use App\Models\Pond;
use App\Models\FeedingRecord;
use Illuminate\Http\Request;

class FeedingRecordController extends Controller
{
    public function indexByFarm(Request $request, Farm $farm)
    {
        if ($farm->user_id !== $request->user()->id) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }

        $records = FeedingRecord::where('farm_id', $farm->id)
            ->with('pond:id,pond_identifier')
            ->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json(['status' => 'success', 'data' => $records]);
    }

    public function indexByPond(Request $request, Pond $pond)
    {
        $farm = Farm::findOrFail($pond->farm_id);
        if ($farm->user_id !== $request->user()->id) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }

        $records = FeedingRecord::where('pond_id', $pond->id)
            ->with('pond:id,pond_identifier')
            ->orderBy('date', 'desc')
            ->get();

        return response()->json(['status' => 'success', 'data' => $records]);
    }

    public function store(Request $request, Farm $farm)
    {
        if ($farm->user_id !== $request->user()->id) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'pond_id'            => 'required|exists:ponds,id',
            'agent_name'         => 'nullable|string|max:255',
            'feeding_type'       => 'required|in:commercial,natural',
            'feed_reference'     => 'nullable|string|max:255',
            'protein_percentage' => 'nullable|string|max:10',
            'feeding_weight'     => 'required|numeric|min:0',
            'note'               => 'nullable|string',
            'feeding_time'       => 'required|string',
            'frequency'          => 'required|in:once,twice,thrice',
            'date'               => 'required|date',
        ]);

        $validated['farm_id'] = $farm->id;

        $record = FeedingRecord::create($validated);

        return response()->json(['status' => 'success', 'message' => 'Feeding record added', 'data' => $record], 201);
    }

    public function destroy(Request $request, FeedingRecord $feedingRecord)
    {
        $farm = Farm::findOrFail($feedingRecord->farm_id);
        if ($farm->user_id !== $request->user()->id) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }

        $feedingRecord->delete();

        return response()->json(['status' => 'success', 'message' => 'Feeding record deleted']);
    }
}
