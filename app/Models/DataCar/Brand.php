<?php

namespace App\Models\DataCar;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Brand extends Model
{
    use HasFactory, SoftDeletes;
     protected $fillable = [
        'name',
        'description',
        'logo',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];
    public function cars()
    {
        return $this->hasMany(CarDetail::class, 'brand_id');
    }

        public function models()
    {
        return $this->hasMany(CarModel::class);
    }

    public function carDetails()
    {
        return $this->hasMany(CarDetail::class);
    }
}
