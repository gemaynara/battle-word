<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('weekly_rankings', function (Blueprint $table) {
            $table->id();
            $table->string('nickname', 30);
            $table->string('week_key', 10)->comment('Ex: 2026-W33');
            $table->unsignedInteger('best_score')->default(0);
            $table->string('game_code', 6)->nullable();
            $table->timestamps();

            $table->index(['week_key', 'best_score']);
            $table->unique(['nickname', 'week_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('weekly_rankings');
    }
};
