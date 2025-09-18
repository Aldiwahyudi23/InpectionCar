<?php

namespace App\Models\DataInspection;

use App\Models\Customer;
use App\Models\DataCar\CarDetail;
use App\Models\Finance\Transaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Spatie\EloquentSortable\SortableTrait;

class Inspection extends Model
{
     use HasFactory;
    use SoftDeletes;

    protected $table = 'inspections';

    protected $fillable = [
        'submitted_by',
        'submitted_at',
        'user_id',
        'customer_id',
        'category_id',
        'car_id',
        'car_name',
        'plate_number',
        'km',
        'color',
        'noka',
        'nosin',
        'inspection_date',
        'status',
        'settings',
        'notes',
        'file',
        'code',
    ];

    protected $casts = [
        'inspection_date' => 'datetime',
        'approved_at' => 'datetime',
        'submitted_at' => 'datetime',
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
    

    public function addLog($action, $description = null, $userId = null)
    {
        return $this->logs()->create([
            'user_id' => $userId ?? Auth::user()->id,
            'action' => $action,
            'description' => $description,
        ]);
    }


    public function categories()
    {
        return $this->belongsToMany(Categorie::class);
    }


    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function canBeEdited()
    {
        return $this->status === 'draft';
    }

    /**
     * Get the user that created the inspection.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function submitted()
    {
        return $this->belongsTo(User::class,'submitted_by');
    }

    /**
     * Get the car associated with the inspection.
     */
    public function car()
    {
        return $this->belongsTo(CarDetail::class);
    }

     public function category()
    {
        return $this->belongsTo(Categorie::class, 'category_id');
    }
     public function appMenu()
    {
        return $this->belongsTo(Categorie::class, 'app_menu_id');
    }

     public function results()
    {
        return $this->hasMany(InspectionResult::class);
    }

    public function images()
    {
        return $this->hasMany(InspectionImage::class,'inspection_id');
    }

    public function logs()
    {
        return $this->hasMany(InspectionLog::class)->with('user')->latest();
    }




    // app/Models/Inspection.php
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function transaction()
    {
        return $this->hasOne(Transaction::class, 'inspection_id');
    }
}
