<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PondPhoto extends Model
{
    protected $fillable = ['farm_id', 'pond_id', 'image_path', 'captured_at'];

    protected $casts = [
        'captured_at' => 'datetime',
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
