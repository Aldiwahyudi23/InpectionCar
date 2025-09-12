<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class UserSession extends Model
{
       protected $table = 'sessions';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;
    protected $guarded = [];

    protected $casts = [
        'last_activity' => 'integer',
        
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // === Detect Browser ===
    public function getBrowserAttribute(): string
    {
        $ua = $this->user_agent;

        if (!$ua) return 'Unknown';

        if (stripos($ua, 'Edge') !== false) return 'Edge';
        if (stripos($ua, 'Chrome') !== false) return 'Chrome';
        if (stripos($ua, 'Safari') !== false && stripos($ua, 'Chrome') === false) return 'Safari';
        if (stripos($ua, 'Firefox') !== false) return 'Firefox';
        if (stripos($ua, 'MSIE') !== false || stripos($ua, 'Trident') !== false) return 'Internet Explorer';

        return 'Other';
    }

    // === Detect Platform / OS ===
    public function getPlatformAttribute(): string
    {
        $ua = $this->user_agent;

        if (!$ua) return 'Unknown';

        if (stripos($ua, 'Windows') !== false) return 'Windows';
        if (stripos($ua, 'Mac') !== false) return 'MacOS';
        if (stripos($ua, 'Linux') !== false) return 'Linux';
        if (stripos($ua, 'Android') !== false) return 'Android';
        if (stripos($ua, 'iPhone') !== false || stripos($ua, 'iPad') !== false) return 'iOS';

        return 'Other';
    }

    // Helper cek online
    public function getIsOnlineAttribute()
    {
        // Anggap online jika aktif dalam 15 menit terakhir
        return $this->last_activity >= now()->subMinutes(15)->timestamp;
    }
}
