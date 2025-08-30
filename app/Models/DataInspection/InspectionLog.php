<?php

namespace App\Models\DataInspection;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InspectionLog extends Model
{
      use HasFactory;

    protected $fillable = [
        'inspection_id',
        'user_id',
        'action',
        'description',
    ];

    public function inspection()
    {
        return $this->belongsTo(Inspection::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
