<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\FarmController;
use App\Http\Controllers\PondController;
use App\Http\Controllers\WaterTestController;
use App\Http\Controllers\WaterTestImageController;
use App\Http\Controllers\WaterQualityThresholdController;
use App\Http\Controllers\FeedingRecordController;
use App\Http\Controllers\HarvestController;
use App\Http\Controllers\PondPhotoController;
use App\Http\Controllers\PondSummaryController;

// ─── Public ──────────────────────────────────────────────────────────────────
Route::post('/login', [AuthController::class, 'login']);

// ─── Protected ───────────────────────────────────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);

    // Profile
    Route::get('/profile',           [ProfileController::class, 'show']);
    Route::put('/profile/password',  [ProfileController::class, 'changePassword']);

    // User management (Admin portal)
    Route::get('/users',           [UserController::class, 'index']);
    Route::post('/users',          [UserController::class, 'store']);
    Route::put('/users/{user}',    [UserController::class, 'update']);
    Route::delete('/users/{user}', [UserController::class, 'destroy']);

    // ── Farms ────────────────────────────────────────────────────────────────
    Route::get('/farms',          [FarmController::class, 'index']);
    Route::post('/farms',         [FarmController::class, 'store']);
    Route::get('/farms/{farm}',   [FarmController::class, 'show']);
    Route::put('/farms/{farm}',   [FarmController::class, 'update']);
    Route::delete('/farms/{farm}',[FarmController::class, 'destroy']);

    // Ponds nested under farm
    Route::get('/farms/{farm}/ponds',   [PondController::class, 'index']);
    Route::post('/farms/{farm}/ponds',  [PondController::class, 'store']);

    // Water tests nested under farm (and optional pond filter)
    Route::get('/farms/{farm}/water-tests',  [WaterTestController::class, 'indexByFarm']);
    Route::post('/farms/{farm}/water-tests', [WaterTestController::class, 'store']);

    // Feeding records nested under farm
    Route::get('/farms/{farm}/feeding-records',  [FeedingRecordController::class, 'indexByFarm']);
    Route::post('/farms/{farm}/feeding-records', [FeedingRecordController::class, 'store']);

    // Harvests nested under farm
    Route::get('/farms/{farm}/harvests',  [HarvestController::class, 'indexByFarm']);
    Route::post('/farms/{farm}/harvests', [HarvestController::class, 'store']);

    // ── Ponds (standalone) ───────────────────────────────────────────────────
    Route::get('/ponds/{pond}',    [PondController::class, 'show']);
    Route::put('/ponds/{pond}',    [PondController::class, 'update']);
    Route::delete('/ponds/{pond}', [PondController::class, 'destroy']);

    // Pond summary (calculated analytics)
    Route::get('/ponds/{pond}/summary', [PondSummaryController::class, 'show']);

    // Pond-scoped sub-resources
    Route::get('/ponds/{pond}/water-tests',      [WaterTestController::class, 'indexByPond']);
    Route::get('/ponds/{pond}/feeding-records',  [FeedingRecordController::class, 'indexByPond']);
    Route::get('/ponds/{pond}/harvests',         [HarvestController::class, 'indexByPond']);
    Route::post('/ponds/{pond}/photos',          [PondPhotoController::class, 'store']);

    // Water test image gallery
    Route::post('/water-tests/{waterTest}/images',              [WaterTestImageController::class, 'store']);
    Route::delete('/water-test-images/{waterTestImage}',        [WaterTestImageController::class, 'destroy']);

    // Water quality thresholds (per farm)
    Route::get('/farms/{farm}/thresholds',  [WaterQualityThresholdController::class, 'index']);
    Route::post('/farms/{farm}/thresholds', [WaterQualityThresholdController::class, 'store']);
    Route::delete('/thresholds/{threshold}', [WaterQualityThresholdController::class, 'destroy']);

    // ── Standalone delete endpoints ──────────────────────────────────────────
    Route::delete('/water-tests/{waterTest}',          [WaterTestController::class, 'destroy']);
    Route::delete('/feeding-records/{feedingRecord}',  [FeedingRecordController::class, 'destroy']);
    Route::delete('/harvests/{harvest}',               [HarvestController::class, 'destroy']);
    Route::delete('/pond-photos/{pondPhoto}',          [PondPhotoController::class, 'destroy']);
});
