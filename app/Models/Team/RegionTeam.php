<?php

namespace App\Models\Team;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RegionTeam extends Model
{
     use HasFactory;
     use SoftDeletes;

    // fillable/hidden/casts ... default Jetstream

    /**
     * Regions where this user is a member.
     */
    protected $fillable = [
        'region_id',
        'user_id',
        'status',
        'description',
        'settings',
    ];

     protected $casts = [
        'settings' => 'array',
        'deleted_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function regions()
    {
        return $this->belongsTo(Region::class, 'region_id');
    }

    public function region()
{
    return $this->belongsTo(Region::class);
}


}
