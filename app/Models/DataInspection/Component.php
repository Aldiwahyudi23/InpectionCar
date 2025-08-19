<?php

namespace App\Models\DataInspection;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\EloquentSortable\SortableTrait;

class Component extends Model
{
    use SoftDeletes, SortableTrait, HasFactory;

    protected $fillable = [
        'name',
        'description', // 'menu' or 'damage'
        'order',
        'is_active'
    ];

    public $sortable = [
        'order_column_name' => 'order',
        'sort_when_creating' => true,
    ];


        public function points()
    {
        return $this->hasMany(InspectionPoint::class, 'component_id');
    }



    public function getRowNumberAttribute()
    {
        return static::where('order', '<=', $this->order)->count();
    }

    
}
