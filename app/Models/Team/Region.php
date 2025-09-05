<?php

namespace App\Models\Team;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Region extends Model
{
     use HasFactory;
     use SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'address',
        'city',
        'province',
        'is_active',
        'settings',
    ];

     protected $casts = [
        'settings' => 'array',
        'is_active' => 'boolean',
        'deleted_at' => 'datetime',
    ];
    
    /**
     * Coordinator of this region (one-to-one).
     */

    public function teams()
    {
        return $this->hasMany(RegionTeam::class);
    }

    public function teamMembers()
    {
        return $this->belongsToMany(User::class, 'region_teams')
            ->withPivot('role', 'status')
            ->withTimestamps();
    }

}
