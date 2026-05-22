<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WaterTestImage extends Model
{
    protected $fillable = [
        'water_test_id',
        'image_path',
        'caption',
        'display_order',
    ];

    protected $casts = [
        'display_order' => 'integer',
    ];

    public function waterTest()
    {
        return $this->belongsTo(WaterTest::class);
    }
}
