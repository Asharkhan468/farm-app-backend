<?php

namespace App\Http\Controllers;

use App\Models\Farm;
use App\Models\Pond;
use App\Models\FeedingRecord;
use App\Models\Harvest;
use App\Models\WaterTest;
use Illuminate\Http\Request;

class PondSummaryController extends Controller
{
    public function show(Request $request, Pond $pond)
    {
        $farm = Farm::findOrFail($pond->farm_id);
        if ($farm->user_id !== $request->user()->id) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }

        $feedings   = FeedingRecord::where('pond_id', $pond->id)->orderBy('date', 'desc')->get();
        $harvests   = Harvest::where('pond_id', $pond->id)->orderBy('date', 'desc')->get();
        $waterTests = WaterTest::where('pond_id', $pond->id)->orderBy('date', 'desc')->take(5)->get();

        // Total feed
        $totalFeed = $feedings->sum('feeding_weight');

        // Stocking data from pond's species_stocked JSON
        $speciesStocked  = $pond->species_stocked ?? [];
        $totalStocked    = 0;
        $initialBiomass  = 0;
        foreach ($speciesStocked as $sp) {
            $totalStocked   += (float) ($sp['quantity'] ?? 0);
            $initialBiomass += (float) ($sp['total_biomass'] ?? 0);
        }

        // Current stock
        $totalHarvestedQty = $harvests->sum('total_calculated_quantity');
        $currentStock      = max(0, $totalStocked - $totalHarvestedQty);

        // Avg weight in grams
        $avgWeight = $totalStocked > 0 ? ($initialBiomass * 1000) / $totalStocked : 0;
        $biomass   = $currentStock * ($avgWeight / 1000);

        // Harvest totals split by type
        $partialHarvests = $harvests->where('harvest_type', 'partial');
        $finalHarvests   = $harvests->where('harvest_type', 'final');

        $partialTotal = [
            'quantity' => (float) $partialHarvests->sum('total_calculated_quantity'),
            'weight'   => (float) $partialHarvests->sum('total_calculated_weight'),
            'count'    => $partialHarvests->count(),
        ];
        $finalTotal = [
            'quantity' => (float) $finalHarvests->sum('total_calculated_quantity'),
            'weight'   => (float) $finalHarvests->sum('total_calculated_weight'),
            'count'    => $finalHarvests->count(),
        ];
        $grandTotal = [
            'quantity' => $partialTotal['quantity'] + $finalTotal['quantity'],
            'weight'   => $partialTotal['weight'] + $finalTotal['weight'],
        ];

        // FCR calculations
        $partialHarvestFCR = $partialTotal['weight'] > 0 ? round($totalFeed / $partialTotal['weight'], 2) : 0;
        $finalHarvestFCR   = $finalTotal['weight'] > 0   ? round($totalFeed / $finalTotal['weight'], 2)   : 0;
        $overallFCR        = $grandTotal['weight'] > 0   ? round($totalFeed / $grandTotal['weight'], 2)   : 0;

        // Classic FCR using only commercial feed vs weight gained
        $commercialFeed = $feedings->where('feeding_type', 'commercial')->sum('feeding_weight');
        $weightGained   = $grandTotal['weight'] - $initialBiomass;
        $classicFCR     = $weightGained > 0 ? round($commercialFeed / $weightGained, 2) : 0;

        // Mortality rate
        $mortalityRate = ($totalStocked > 0 && $totalHarvestedQty > 0)
            ? round((($totalStocked - $totalHarvestedQty) / $totalStocked) * 100, 1)
            : 0;

        return response()->json([
            'status' => 'success',
            'data'   => [
                'pond'          => $pond,
                'totalFeed'     => round($totalFeed, 2),
                'totalBiomass'  => round($biomass, 2),
                'currentStock'  => (int) $currentStock,
                'avgWeight'     => round($avgWeight, 2),
                'mortalityRate' => $mortalityRate,
                'classicFCR'    => $classicFCR,
                'harvestTotals' => [
                    'partialHarvest' => $partialTotal,
                    'finalHarvest'   => $finalTotal,
                    'grandTotal'     => $grandTotal,
                ],
                'fcrData' => [
                    'partialHarvestFCR' => $partialHarvestFCR,
                    'finalHarvestFCR'   => $finalHarvestFCR,
                    'overallFCR'        => $overallFCR,
                ],
                'feedingHistory'  => $feedings->take(10)->values(),
                'stockingHistory' => $speciesStocked,
                'harvestHistory'  => $harvests->values(),
                'waterTests'      => $waterTests->values(),
            ],
        ]);
    }
}
