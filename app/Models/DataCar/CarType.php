<?php

namespace App\Models\DataCar;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CarType extends Model
{
     use SoftDeletes;

    protected $fillable = [
        'car_model_id',
        'name',
        'description',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    public function model()
    {
        return $this->belongsTo(CarModel::class, 'car_model_id');
    }

    public function cars()
    {
        return $this->hasMany(CarDetail::class);
    }

        public function carDetails()
    {
        return $this->hasMany(CarDetail::class);
    }
}
