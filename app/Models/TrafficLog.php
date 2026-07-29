<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrafficLog extends Model
{
    use HasFactory;

    protected $table = 'traffic_logs';

    protected $fillable = [
        'vmess_config_id',
        'user_id',
        'bytes_up',
        'bytes_down',
        'date',
    ];

    protected $casts = [
        'bytes_up' => 'integer',
        'bytes_down' => 'integer',
        'date' => 'date',
    ];

    public function vmessConfig(): BelongsTo
    {
        return $this->belongsTo(VmessConfig::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getTotalBytesAttribute(): int
    {
        return $this->bytes_up + $this->bytes_down;
    }

    public function getTotalGBAttribute(): float
    {
        return round($this->total_bytes / (1024 * 1024 * 1024), 3);
    }

    public function scopeByDate($query, $date)
    {
        return $query->where('date', $date);
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByConfig($query, $configId)
    {
        return $query->where('vmess_config_id', $configId);
    }

    public static function logTraffic(int $configId, int $bytesUp, int $bytesDown, ?string $date = null): void
    {
        $date = $date ?? now()->toDateString();
        
        self::updateOrCreate(
            ['vmess_config_id' => $configId, 'date' => $date],
            [
                'bytes_up' => \DB::raw("bytes_up + {$bytesUp}"),
                'bytes_down' => \DB::raw("bytes_down + {$bytesDown}"),
            ]
        );
    }

    public static function getUserTraffic(int $userId, ?string $startDate = null, ?string $endDate = null): array
    {
        $query = self::byUser($userId);
        
        if ($startDate) $query->where('date', '>=', $startDate);
        if ($endDate) $query->where('date', '<=', $endDate);
        
        $logs = $query->get();
        
        return [
            'total_up' => $logs->sum('bytes_up'),
            'total_down' => $logs->sum('bytes_down'),
            'total' => $logs->sum(fn ($log) => $log->total_bytes),
            'daily' => $logs->mapWithKeys(fn ($log) => [
                $log->date => [
                    'up' => $log->bytes_up,
                    'down' => $log->bytes_down,
                    'total' => $log->total_bytes,
                ]
            ]),
        ];
    }
}