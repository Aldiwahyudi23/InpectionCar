<?php

namespace App\Models\DataInspection;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\EloquentSortable\SortableTrait;

class AppMenu extends Model
{
     use SoftDeletes, SortableTrait, HasFactory;

    protected $fillable = [
        'category_id',
        'name',
        'input_type', // 'menu' or 'damage'
        'order',
        'is_active'
    ];

    public $sortable = [
        'order_column_name' => 'order',
        'sort_when_creating' => true,
    ];

    public function category()
    {
        return $this->belongsTo(Categorie::class, 'category_id'); // Spesifikkan foreign key
    }

        public function menu_point()
    {
        return $this->hasMany(MenuPoint::class);
    }



    public function getRowNumberAttribute()
    {
        return static::where('order', '<=', $this->order)->count();
    }

    
}
