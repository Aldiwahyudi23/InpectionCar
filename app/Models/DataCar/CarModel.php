<?php

namespace App\Models\DataCar;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CarModel extends Model
{
     use SoftDeletes;

    protected $fillable = [
        'brand_id',
        'name',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }
}
