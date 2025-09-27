<?php

namespace App\Models\DataInspection;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\EloquentSortable\SortableTrait;

class Categorie extends Model
{
    use HasFactory;
    use SoftDeletes;
  use SortableTrait;

    protected $table = 'categories';

    protected $fillable = [
        'name',
        'settings',
        'order',
        'is_active',
    ];

    protected $casts = [
        'settings' => 'array',
        'is_active' => 'boolean',
    ];

    public function appMenu()
    {
        return $this->hasMany(AppMenu::class, 'category_id');
    }

    // app/Models/DataInpection/Categorie.php

 public $sortable = [
        'order_column_name' => 'order',
        'sort_when_creating' => true,
    ];

    // Method untuk mendapatkan nomor urut
    public function getRowNumberAttribute()
    {
       return static::ordered()->where('order', '<=', $this->order)->count();
    }
}
