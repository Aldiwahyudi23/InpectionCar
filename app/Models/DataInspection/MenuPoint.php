<?php

namespace App\Models\DataInspection;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\EloquentSortable\SortableTrait;

class MenuPoint extends Model
{
    use HasFactory;
    use SoftDeletes;
    use SortableTrait;

    protected $table = 'menu_points';

    protected $fillable = [
        'inspection_point_id',
        'app_menu_id',
        'input_type',
        'settings',
        'order',
        'is_active',
        'is_default',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'settings' => 'array',
        'is_active' => 'boolean',
        'is_default' => 'boolean',
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
        'is_default' => true,
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

        public function inspection_point()
    {
        return $this->belongsTo(InspectionPoint::class, 'inspection_point_id', 'id');
    }

     public function app_menu()
    {
        return $this->belongsTo(AppMenu::class, 'app_menu_id', 'id');
    }
     public function app_menus()
    {
        return $this->belongsTo(AppMenu::class, 'app_menu_id', 'id');
    }

    // di InspectionPoint.php

    public function results()
    {
        return $this->hasMany(InspectionResult::class, 'point_id', 'id');
    }

    public function images()
    {
        return $this->hasMany(InspectionImage::class, 'point_id', 'id');
    }

    public function getShowImageUploadAttribute()
    {
        return $this->settings['show_image_upload'] ?? false;
    }

    public function getShowTextareaAttribute()
    {
        return $this->settings['show_textarea'] ?? false;
    }
}
