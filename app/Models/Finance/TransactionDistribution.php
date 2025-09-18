<?php

namespace App\Models\Finance;

use App\Models\Team\Region;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransactionDistribution extends Model
{
    use HasFactory;
     protected $fillable = [
        'transaction_id',
        'user_id',
        'region_id',
        'role_type',
        'amount',
        'percentage',
        'calculation_note',
        'is_released',
        'released_at',
        'released_by',
    ];

    /**
     * Relasi ke Transaction
     */
    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    /**
     * Relasi ke User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function released()
    {
        return $this->belongsTo(User::class, 'released_by');
    }

    /**
     * Relasi ke Region
     */
    public function region()
    {
        return $this->belongsTo(Region::class);
    }
}
