<?php

namespace App\Models\DataCar;

use App\Models\DataInspection\Inspection;
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
        'production_period',
        'description',
        'engine_code',
        'segment',
    ];

    protected $casts = [
        'year' => 'integer',
    ];

    public function brand()
    {
        return $this->belongsTo(Brand::class, 'brand_id');
    }

    public function model()
    {
        return $this->belongsTo(CarModel::class, 'car_model_id');
    }

     public function type()
    {
        return $this->belongsTo(CarType::class, 'car_type_id');
    }

    public function inspection()
    {
        return $this->hasMany(Inspection::class, 'car_id');
    }

    public function images()
{
    return $this->hasMany(ImageCar::class, 'car_id');
}


}
