<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Server extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'name',
        'host',
        'port',
        'remark',
        'settings',
        'is_active',
        'sort_order',
        'country_code',
        'location',
        'max_users',
        'current_users',
    ];

    protected $casts = [
        'settings' => 'array',
        'is_active' => 'boolean',
        'port' => 'integer',
        'max_users' => 'integer',
        'current_users' => 'integer',
        'sort_order' => 'integer',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function vmessConfigs(): HasMany
    {
        return $this->hasMany(VmessConfig::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeAvailable($query)
    {
        return $query->active()
            ->whereRaw('current_users < max_users');
    }
}