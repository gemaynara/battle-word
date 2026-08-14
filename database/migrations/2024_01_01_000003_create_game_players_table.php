<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('game_players', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_id')->constrained('games')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('nickname', 30);
            $table->string('avatar', 50)->nullable();
            $table->boolean('is_host')->default(false);
            $table->boolean('is_bot')->default(false);
            $table->boolean('is_connected')->default(true);
            $table->unsignedInteger('total_score')->default(0);
            $table->unsignedSmallInteger('total_words')->default(0);
            $table->unsignedSmallInteger('best_combo')->default(0);
            $table->unsignedTinyInteger('longest_word_length')->default(0);
            $table->unsignedTinyInteger('position')->nullable()->comment('Posição final no ranking da partida');
            $table->timestamp('joined_at')->useCurrent();
            $table->timestamp('disconnected_at')->nullable();
            $table->timestamps();

            $table->unique(['game_id', 'nickname']);
            $table->index(['game_id', 'total_score']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_players');
    }
};
