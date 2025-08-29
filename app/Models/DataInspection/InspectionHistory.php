<?php

namespace App\Models\DataInspection;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InspectionHistory extends Model
{
    use SoftDeletes;
    protected $table = 'inspection_histories';

    protected $fillable = [
        'user_id',
        'plate_number',
        'car_id',
        'car_name',
        'category_id',
        'inspection_date',
        'status',
        'settings',
        'notes',
        'file',
        'code',
    ];

    protected $casts = [
        'inspection_date' => 'datetime',
    ];
     public function getSettingsAttribute($value)
    {
        if (is_string($value)) {
            return json_decode($value, true) ?? [];
        }
        
        return $value ?? [];
    }

    public function setSettingsAttribute($value)
    {
        if (is_array($value)) {
            $this->attributes['settings'] = json_encode($value);
        } else {
            $this->attributes['settings'] = $value;
        }
    }
}
