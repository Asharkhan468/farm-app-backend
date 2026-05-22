<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeedingRecord extends Model
{
    protected $fillable = [
        'farm_id',
        'pond_id',
        'agent_name',
        'feeding_type',
        'feed_reference',
        'protein_percentage',
        'feeding_weight',
        'note',
        'feeding_time',
        'frequency',
        'date',
    ];

    protected $casts = [
        'date'          => 'date:Y-m-d',
        'feeding_weight' => 'float',
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
