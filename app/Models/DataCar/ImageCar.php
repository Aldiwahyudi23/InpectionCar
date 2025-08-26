<?php

namespace App\Models\DataCar;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ImageCar extends Model
{
     use HasFactory;

    protected $fillable = [
        'car_id',
        'name',
        'file_path',
        'note',
    ];

    public function car()
    {
        return $this->belongsTo(CarDetail::class, 'car_id');
    }
}
