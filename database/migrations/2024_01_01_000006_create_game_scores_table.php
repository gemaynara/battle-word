<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('game_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_id')->constrained('games')->cascadeOnDelete();
            $table->foreignId('game_round_id')->constrained('game_rounds')->cascadeOnDelete();
            $table->foreignId('game_player_id')->constrained('game_players')->cascadeOnDelete();
            $table->unsignedInteger('round_score')->default(0);
            $table->unsignedSmallInteger('words_submitted')->default(0);
            $table->unsignedSmallInteger('valid_words')->default(0);
            $table->unsignedSmallInteger('invalid_words')->default(0);
            $table->unsignedTinyInteger('best_combo')->default(0);
            $table->unsignedTinyInteger('longest_word_length')->default(0);
            $table->string('longest_word', 50)->nullable();
            $table->boolean('had_perfect_word')->default(false);
            $table->unsignedTinyInteger('position_in_round')->nullable();
            $table->timestamps();

            $table->unique(['game_round_id', 'game_player_id']);
            $table->index(['game_id', 'game_player_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_scores');
    }
};
