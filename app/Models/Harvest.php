<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Harvest extends Model
{
    protected $fillable = [
        'farm_id',
        'pond_id',
        'culture_id',
        'harvest_type',
        'species',
        'size_entries',
        'total_calculated_quantity',
        'total_calculated_weight',
        'calculated_avg_size',
        'total_feed',
        'date',
        'image_path',
    ];

    protected $casts = [
        'size_entries'               => 'array',
        'date'                       => 'date:Y-m-d',
        'total_calculated_quantity'  => 'float',
        'total_calculated_weight'    => 'float',
        'calculated_avg_size'        => 'float',
    ];

    public function farm()
    {
        return $this->belongsTo(Farm::class);
    }

    public function pond()
    {
        return $this->belongsTo(Pond::class);
    }
}
