<?php

namespace App\Models\DataInpection;

use App\Models\DataCar\CarDetail;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Inspection extends Model
{
     use HasFactory;
    use SoftDeletes;

    protected $table = 'inspections';

    protected $fillable = [
        'user_id',
        'car_id',
        'inspection_date',
        'status',
        'notes',
    ];

    protected $casts = [
        'inspection_date' => 'datetime',
    ];

    /**
     * Get the user that created the inspection.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the car associated with the inspection.
     */
    public function car()
    {
        return $this->belongsTo(CarDetail::class);
    }

     public function category()
    {
        return $this->belongsTo(Categorie::class, 'category_id');
    }
}
