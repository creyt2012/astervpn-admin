<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles, LogsActivity;

    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
        'status',
        'traffic_limit',
        'traffic_used',
        'device_limit',
        'expires_at',
        'subscription_token',
        'subscription_token_expires_at',
        'last_login_at',
        'last_login_ip',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'subscription_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'status' => 'boolean',
        'traffic_limit' => 'integer',
        'traffic_used' => 'integer',
        'device_limit' => 'integer',
        'expires_at' => 'datetime',
        'subscription_token_expires_at' => 'datetime',
        'last_login_at' => 'datetime',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function vmessConfigs()
    {
        return $this->hasMany(VmessConfig::class);
    }

    public function activeVmessConfigs()
    {
        return $this->hasMany(VmessConfig::class)->valid();
    }

    public function trafficLogs()
    {
        return $this->hasManyThrough(TrafficLog::class, VmessConfig::class);
    }

    public function devices()
    {
        return $this->hasMany(UserDevice::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<', now());
    }

    public function scopeValidSubscription($query)
    {
        return $query->active()
            ->where(function ($q) {
                $q->whereNull('expires_at')
                  ->orWhere('expires_at', '>', now());
            })
            ->where(function ($q) {
                $q->where('traffic_limit', 0)
                  ->orWhereRaw('traffic_used < traffic_limit');
            });
    }

    public function generateSubscriptionToken(): string
    {
        $token = \Ramsey\Uuid\Uuid::uuid4()->toString();
        $this->update([
            'subscription_token' => $token,
            'subscription_token_expires_at' => now()->addDay(),
        ]);
        return $token;
    }

    public function getSubscriptionUrl(): string
    {
        $token = $this->subscription_token ?? $this->generateSubscriptionToken();
        return config('app.url') . "/api/v1/subscription/{$token}";
    }

    public function incrementTraffic(int $bytes): void
    {
        $this->increment('traffic_used', $bytes);
    }

    public function hasTrafficRemaining(): bool
    {
        if ($this->traffic_limit === 0) return true;
        return $this->traffic_used < $this->traffic_limit;
    }

    public function canAddDevice(): bool
    {
        if ($this->device_limit === 0) return true;
        return $this->devices()->count() < $this->device_limit;
    }
}