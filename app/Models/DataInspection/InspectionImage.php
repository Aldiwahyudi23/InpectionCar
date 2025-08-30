<?php

namespace App\Models\DataInspection;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class InspectionImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'point_id',
        'inspection_id',
        'image_path',
        'caption'
    ];

    /**
     * Get the inspection point that owns the image.
     */
    public function inspection()
    {
        return $this->belongsTo(Inspection::class);
    }

    public function point()
    {
        return $this->belongsTo(InspectionPoint::class, 'point_id');
    }

    /**
     * Get the full URL for the image.
     */
    public function getImageUrlAttribute()
    {
        return asset('storage/' . ltrim($this->image_path, 'storage/'));
    }

    protected static function booted()
    {
        static::deleting(function ($image) {
            if ($image->image_path && Storage::exists($image->image_path)) {
                Storage::delete($image->image_path);
            }
        });
    }
}
