<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Pond extends Model
{
    use HasFactory;

    protected $fillable = [
        'farm_id',
        'culture_id',
        'pond_identifier',
        'length',
        'width',
        'depth',
        'water_capacity',
        'hatchery_reference',
        'species_stocked',
        'stocking_date',
        'status',
    ];

    protected $casts = [
        'species_stocked' => 'array',
        'stocking_date'   => 'date:Y-m-d',
        'length'          => 'float',
        'width'           => 'float',
        'depth'           => 'float',
        'water_capacity'  => 'float',
    ];

    public function farm()
    {
        return $this->belongsTo(Farm::class);
    }

    public function waterTests()
    {
        return $this->hasMany(WaterTest::class);
    }

    public function feedingRecords()
    {
        return $this->hasMany(FeedingRecord::class);
    }

    public function harvests()
    {
        return $this->hasMany(Harvest::class);
    }

    public function pondPhotos()
    {
        return $this->hasMany(PondPhoto::class);
    }
}
