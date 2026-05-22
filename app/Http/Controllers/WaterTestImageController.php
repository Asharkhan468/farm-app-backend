<?php

namespace App\Http\Controllers;

use App\Models\Farm;
use App\Models\WaterTest;
use App\Models\WaterTestImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class WaterTestImageController extends Controller
{
    // ── Upload image(s) to a water test ───────────────────────────────────────

    public function store(Request $request, WaterTest $waterTest)
    {
        $farm = Farm::findOrFail($waterTest->farm_id);
        if ($farm->user_id !== $request->user()->id) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'image'         => 'required|image|max:5120',
            'caption'       => 'nullable|string|max:255',
            'display_order' => 'nullable|integer|min:0|max:255',
        ]);

        $path = $request->file('image')->store('water-test-images', 'public');

        $image = WaterTestImage::create([
            'water_test_id' => $waterTest->id,
            'image_path'    => $path,
            'caption'       => $request->caption,
            'display_order' => $request->input('display_order', 0),
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Image uploaded',
            'data'    => $image,
        ], 201);
    }

    // ── Delete ────────────────────────────────────────────────────────────────

    public function destroy(Request $request, WaterTestImage $waterTestImage)
    {
        $farm = Farm::findOrFail(
            WaterTest::findOrFail($waterTestImage->water_test_id)->farm_id
        );
        if ($farm->user_id !== $request->user()->id) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }

        Storage::disk('public')->delete($waterTestImage->image_path);
        $waterTestImage->delete();

        return response()->json(['status' => 'success', 'message' => 'Image deleted']);
    }
}
