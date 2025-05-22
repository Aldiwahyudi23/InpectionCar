<?php

namespace App\Models\DataInpection;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InspectionPoint extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'inspection_points';

    protected $fillable = [
        'category_id',
        'name',
        'input_type',
        'settings',
        'order',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'settings' => 'array',
        'is_active' => 'boolean',
        'deleted_at' => 'datetime',
    ];

    /**
     * The default values for attributes.
     *
     * @var array
     */
    protected $attributes = [
        'input_type' => 'text',
        'is_active' => true,
    ];

    /**
     * Get the category that owns the inspection point.
     */
    public function category()
    {
        return $this->belongsTo(Categorie::class);
    }

}
