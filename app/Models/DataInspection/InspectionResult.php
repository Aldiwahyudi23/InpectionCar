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
        return $this->belongsTo(Inspection::class);
    }

    /**
     * Get the inspection point that owns the result.
     */
    public function point()
    {
        return $this->belongsTo(InspectionPoint::class);
    }

      /**
     * Get all images for this result.
     */
    public function images()
    {
        return $this->hasMany(InspectionImage::class, 'point_id', 'point_id');
    }
}
