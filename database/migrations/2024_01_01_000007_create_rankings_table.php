<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rankings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('period', ['global', 'weekly', 'monthly']);
            $table->string('period_key', 10)->comment('Ex: 2024-W01, 2024-01, global');
            $table->unsignedInteger('total_score')->default(0);
            $table->unsignedSmallInteger('games_played')->default(0);
            $table->unsignedSmallInteger('games_won')->default(0);
            $table->unsignedSmallInteger('total_words')->default(0);
            $table->unsignedInteger('xp')->default(0);
            $table->unsignedSmallInteger('level')->default(1);
            $table->unsignedInteger('position')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'period', 'period_key']);
            $table->index(['period', 'period_key', 'total_score']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rankings');
    }
};
