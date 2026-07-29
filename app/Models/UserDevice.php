<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserDevice extends Model
{
    use HasFactory;

    protected $table = 'user_devices';

    protected $fillable = [
        'user_id',
        'device_id',
        'device_name',
        'platform',
        'app_version',
        'last_ip',
        'last_seen_at',
    ];

    protected $casts = [
        'last_seen_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('last_seen_at', '>=', now()->subDays($days));
    }

    public static function registerDevice(int $userId, string $deviceId, array $data = []): self
    {
        return self::updateOrCreate(
            ['user_id' => $userId, 'device_id' => $deviceId],
            array_merge($data, [
                'last_seen_at' => now(),
                'last_ip' => request()->ip(),
            ])
        );
    }

    public function markSeen(): void
    {
        $this->update([
            'last_seen_at' => now(),
            'last_ip' => request()->ip(),
        ]);
    }
}