<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WaterTest extends Model
{
    protected $fillable = [
        'farm_id',
        'pond_id',
        'culture_id',
        'agent_name',
        // Core chemistry
        'ph',
        'oxygen',
        'temperature',
        'ammonia',
        'nitrite',
        'nitrate',
        // Extended chemistry
        'alkalinity',
        'hardness',
        'turbidity',
        'salinity',
        'tds',
        'co2',
        'hydrogen_sulfide',
        'phosphate',
        // Flexible / custom
        'custom_tests',
        // Trend & diagnosis
        'parameter_reasons',
        'parameter_direction',
        'overall_status',
        // Meta
        'note',
        'time_recorded',
        'date',
        'image_path',
    ];

    protected $casts = [
        'custom_tests'      => 'array',
        'parameter_reasons' => 'array',
        'date'              => 'date:Y-m-d',
        // Numeric casts for clean JSON serialization
        'ph'                => 'float',
        'oxygen'            => 'float',
        'temperature'       => 'float',
        'ammonia'           => 'float',
        'nitrite'           => 'float',
        'nitrate'           => 'float',
        'alkalinity'        => 'float',
        'hardness'          => 'float',
        'turbidity'         => 'float',
        'salinity'          => 'float',
        'tds'               => 'float',
        'co2'               => 'float',
        'hydrogen_sulfide'  => 'float',
        'phosphate'         => 'float',
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

    public function images()
    {
        return $this->hasMany(WaterTestImage::class)->orderBy('display_order');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /**
     * Compute overall_status by comparing readings against the farm/pond thresholds.
     * Returns 'normal', 'warning', or 'critical'.
     */
    public function computeStatus(?WaterQualityThreshold $threshold): string
    {
        if (! $threshold) {
            return 'normal';
        }

        $critical = false;
        $warning  = false;

        $checks = [
            ['val' => $this->ph,               'min' => $threshold->ph_min,               'max' => $threshold->ph_max],
            ['val' => $this->oxygen,            'min' => $threshold->oxygen_min,           'max' => null],
            ['val' => $this->temperature,       'min' => $threshold->temperature_min,      'max' => $threshold->temperature_max],
            ['val' => $this->ammonia,           'min' => null,                             'max' => $threshold->ammonia_max],
            ['val' => $this->nitrite,           'min' => null,                             'max' => $threshold->nitrite_max],
            ['val' => $this->nitrate,           'min' => null,                             'max' => $threshold->nitrate_max],
            ['val' => $this->turbidity,         'min' => null,                             'max' => $threshold->turbidity_max],
            ['val' => $this->alkalinity,        'min' => $threshold->alkalinity_min,       'max' => $threshold->alkalinity_max],
            ['val' => $this->hardness,          'min' => $threshold->hardness_min,         'max' => $threshold->hardness_max],
            ['val' => $this->hydrogen_sulfide,  'min' => null,                             'max' => $threshold->hydrogen_sulfide_max],
        ];

        foreach ($checks as $check) {
            if ($check['val'] === null) {
                continue;
            }
            if ($check['max'] !== null && $check['val'] > $check['max']) {
                // Critical if more than 50% over limit, otherwise warning
                $check['val'] > $check['max'] * 1.5 ? ($critical = true) : ($warning = true);
            }
            if ($check['min'] !== null && $check['val'] < $check['min']) {
                $check['val'] < $check['min'] * 0.5 ? ($critical = true) : ($warning = true);
            }
        }

        return $critical ? 'critical' : ($warning ? 'warning' : 'normal');
    }
}
