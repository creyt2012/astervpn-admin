<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('servers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('host');
            $table->unsignedInteger('port');
            $table->string('remark')->nullable();
            $table->json('settings')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('country_code', 2)->nullable();
            $table->string('location')->nullable();
            $table->unsignedInteger('max_users')->default(100);
            $table->unsignedInteger('current_users')->default(0);
            $table->timestamps();
            
            $table->index(['is_active', 'country_code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('servers');
    }
};