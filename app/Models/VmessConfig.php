<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VmessConfig extends Model
{
    use HasFactory;

    protected $table = 'vmess_configs';

    protected $fillable = [
        'server_id',
        'user_id',
        'uuid',
        'alter_id',
        'security',
        'network',
        'network_settings',
        'tls',
        'tls_settings',
        'remark',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'network_settings' => 'array',
        'tls_settings' => 'array',
        'is_active' => 'boolean',
        'alter_id' => 'integer',
        'sort_order' => 'integer',
    ];

    public function server(): BelongsTo
    {
        return $this->belongsTo(Server::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function trafficLogs(): HasMany
    {
        return $this->hasMany(TrafficLog::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeValid($query)
    {
        return $query->active()
            ->whereHas('server', fn ($q) => $q->active());
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function toVmessUri(): string
    {
        $config = [
            'v' => '2',
            'ps' => $this->remark ?? $this->server->name ?? 'AsterVPN',
            'add' => $this->server->host,
            'port' => $this->server->port,
            'id' => $this->uuid,
            'aid' => $this->alter_id,
            'scy' => $this->security,
            'net' => $this->network,
            'type' => $this->network_settings['type'] ?? 'none',
            'host' => $this->network_settings['host'] ?? '',
            'path' => $this->network_settings['path'] ?? '',
            'tls' => $this->tls ?? 'none',
            'sni' => $this->tls_settings['sni'] ?? '',
            'alpn' => $this->tls_settings['alpn'] ?? '',
            'fp' => $this->tls_settings['fingerprint'] ?? '',
            'allowInsecure' => $this->tls_settings['allow_insecure'] ?? false,
        ];

        $json = json_encode($config, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        return 'vmess://' . base64_encode($json);
    }

    public function toClashConfig(): array
    {
        return [
            'name' => $this->remark ?? $this->server->name ?? 'AsterVPN',
            'type' => 'vmess',
            'server' => $this->server->host,
            'port' => $this->server->port,
            'uuid' => $this->uuid,
            'alterId' => $this->alter_id,
            'cipher' => $this->security,
            'network' => $this->network,
            'tls' => $this->tls !== 'none',
            'skip-cert-verify' => $this->tls_settings['allow_insecure'] ?? false,
            'servername' => $this->tls_settings['sni'] ?? '',
            'client-fingerprint' => $this->tls_settings['fingerprint'] ?? '',
        ];
    }
}