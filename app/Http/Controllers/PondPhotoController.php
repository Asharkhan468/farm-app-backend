<?php

namespace App\Http\Controllers;

use App\Models\Farm;
use App\Models\Pond;
use App\Models\PondPhoto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PondPhotoController extends Controller
{
    public function store(Request $request, Pond $pond)
    {
        $farm = Farm::findOrFail($pond->farm_id);
        if ($farm->user_id !== $request->user()->id) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'photo' => 'required|image|max:5120',
        ]);

        $path = $request->file('photo')->store('pond-photos', 'public');

        $photo = PondPhoto::create([
            'farm_id'     => $farm->id,
            'pond_id'     => $pond->id,
            'image_path'  => $path,
            'captured_at' => now(),
        ]);

        return response()->json([
            'status' => 'success',
            'data'   => $photo,
            'url'    => Storage::url($path),
        ], 201);
    }

    public function destroy(Request $request, PondPhoto $pondPhoto)
    {
        $farm = Farm::findOrFail($pondPhoto->farm_id);
        if ($farm->user_id !== $request->user()->id) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }

        Storage::disk('public')->delete($pondPhoto->image_path);
        $pondPhoto->delete();

        return response()->json(['status' => 'success', 'message' => 'Photo deleted']);
    }
}
