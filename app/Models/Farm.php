<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Farm extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'name'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function ponds()
    {
        return $this->hasMany(Pond::class);
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
