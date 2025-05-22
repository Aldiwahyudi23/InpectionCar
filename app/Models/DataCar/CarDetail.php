<?php

namespace App\Models\DataCar;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CarDetail extends Model
{
     use SoftDeletes;

    protected $fillable = [
        'brand_id',
        'car_model_id',
        'car_type_id',
        'year',
        'cc',
        'transmission',
        'fuel_type',
        'production_period'
    ];

    protected $casts = [
        'year' => 'integer',
    ];

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function model()
    {
        return $this->belongsTo(CarModel::class, 'car_model_id');
    }

     public function type()
    {
        return $this->belongsTo(CarType::class, 'car_type_id');
    }

}
