<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vmess_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('server_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('uuid')->unique();
            $table->unsignedInteger('alter_id')->default(0);
            $table->string('security')->default('auto');
            $table->string('network')->default('tcp');
            $table->json('network_settings')->nullable();
            $table->string('tls')->nullable();
            $table->json('tls_settings')->nullable();
            $table->string('remark')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            
            $table->index(['server_id', 'is_active']);
            $table->index(['user_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vmess_configs');
    }
};