<?php

namespace App\Models\DataInspection;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InspectionResult extends Model
{
     use HasFactory;

    protected $fillable = [
        'inspection_id',
        'point_id',
        'status',
        'note'
    ];

    /**
     * Get the inspection that owns the result.
     */

    public function inspection()
    {
        return $this->belongsTo(Inspection::class,'inspection_id');
    }

    public function point()
    {
        return $this->belongsTo(InspectionPoint::class, 'point_id');
    }
      /**
     * Get all images for this result.
     */
    public function images()
    {
        return $this->hasMany(InspectionImage::class, 'point_id', 'point_id');
    }
}
