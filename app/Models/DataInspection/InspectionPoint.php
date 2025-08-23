<?php

namespace App\Models\DataInspection;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\EloquentSortable\SortableTrait;

class InspectionPoint extends Model
{
    use HasFactory;
    use SoftDeletes;
    use SortableTrait;

    protected $table = 'inspection_points';

    protected $fillable = [
        'component_id',
        'app_menu_id',
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

     public $sortable = [
        'order_column_name' => 'order',
        'sort_when_creating' => true,
    ];

    public function getTableRowNumber()
    {
        return $this->component->points()
            ->where('order', '<=', $this->order)
            ->count();
    }



    /**
     * Get the category that owns the inspection point.
     */
    public function component()
    {
        return $this->belongsTo(Component::class, 'component_id', 'id');
    }
    public function app_menu()
    {
        return $this->belongsTo(AppMenu::class, 'app_menu_id', 'id');
    }

    // di InspectionPoint.php

    public function results()
    {
        return $this->hasMany(InspectionResult::class, 'inspection_id', 'id');
    }

    public function images()
    {
        return $this->hasMany(InspectionImage::class, 'point_id', 'id');
    }


}
