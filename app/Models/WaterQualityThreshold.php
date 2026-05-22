<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WaterQualityThreshold extends Model
{
    protected $fillable = [
        'farm_id',
        'pond_id',
        'species_name',
        'is_default',
        'ph_min',
        'ph_max',
        'oxygen_min',
        'temperature_min',
        'temperature_max',
        'ammonia_max',
        'nitrite_max',
        'nitrate_max',
        'turbidity_max',
        'alkalinity_min',
        'alkalinity_max',
        'hardness_min',
        'hardness_max',
        'hydrogen_sulfide_max',
        'alert_on_breach',
    ];

    protected $casts = [
        'is_default'           => 'boolean',
        'alert_on_breach'      => 'boolean',
        'ph_min'               => 'float',
        'ph_max'               => 'float',
        'oxygen_min'           => 'float',
        'temperature_min'      => 'float',
        'temperature_max'      => 'float',
        'ammonia_max'          => 'float',
        'nitrite_max'          => 'float',
        'nitrate_max'          => 'float',
        'turbidity_max'        => 'float',
        'alkalinity_min'       => 'float',
        'alkalinity_max'       => 'float',
        'hardness_min'         => 'float',
        'hardness_max'         => 'float',
        'hydrogen_sulfide_max' => 'float',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function farm()
    {
        return $this->belongsTo(Farm::class);
    }

    public function pond()
    {
        return $this->belongsTo(Pond::class);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /**
     * Find the best-matching threshold for a farm + pond + optional species.
     * Priority: pond-specific > farm-wide default > null.
     */
    public static function findFor(int $farmId, ?int $pondId, ?string $species = null): ?self
    {
        $query = self::where('farm_id', $farmId);

        if ($pondId) {
            // Try pond + species match first
            $threshold = (clone $query)
                ->where('pond_id', $pondId)
                ->where('species_name', $species)
                ->first();

            if ($threshold) {
                return $threshold;
            }

            // Try pond-only match
            $threshold = (clone $query)
                ->where('pond_id', $pondId)
                ->whereNull('species_name')
                ->first();

            if ($threshold) {
                return $threshold;
            }
        }

        // Fall back to farm-wide default
        return (clone $query)
            ->where('is_default', true)
            ->whereNull('pond_id')
            ->first();
    }
}
