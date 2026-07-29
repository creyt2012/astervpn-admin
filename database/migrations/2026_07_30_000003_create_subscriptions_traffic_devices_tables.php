<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('token')->unique();
            $table->timestamp('expires_at')->nullable();
            $table->unsignedInteger('rate_limit')->default(10);
            $table->unsignedInteger('request_count')->default(0);
            $table->timestamp('last_request_at')->nullable();
            $table->timestamp('rate_limit_reset_at')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'expires_at']);
        });

        Schema::create('traffic_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vmess_config_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('bytes_up')->default(0);
            $table->unsignedBigInteger('bytes_down')->default(0);
            $table->date('date');
            $table->timestamps();
            
            $table->unique(['vmess_config_id', 'date']);
            $table->index(['user_id', 'date']);
        });

        Schema::create('user_devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('device_id');
            $table->string('device_name')->nullable();
            $table->string('platform')->nullable();
            $table->string('app_version')->nullable();
            $table->ipAddress('last_ip')->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamps();
            
            $table->unique(['user_id', 'device_id']);
            $table->index(['user_id', 'last_seen_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_devices');
        Schema::dropIfExists('traffic_logs');
        Schema::dropIfExists('subscriptions');
    }
};