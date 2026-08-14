<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('submitted_words', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_round_id')->constrained('game_rounds')->cascadeOnDelete();
            $table->foreignId('game_player_id')->constrained('game_players')->cascadeOnDelete();
            $table->string('word', 50);
            $table->boolean('is_valid')->default(false);
            $table->string('rejection_reason', 50)->nullable()->comment('Motivo da rejeição: not_in_dictionary, invalid_letters, duplicate, time_expired');
            $table->unsignedSmallInteger('points')->default(0);
            $table->unsignedTinyInteger('combo_multiplier')->default(1);
            $table->unsignedSmallInteger('total_points')->default(0)->comment('points * combo_multiplier');
            $table->boolean('is_perfect_word')->default(false)->comment('Usou todas as letras disponíveis');
            $table->boolean('is_rare_word')->default(false)->comment('Palavra incomum');
            $table->boolean('is_long_word')->default(false)->comment('7+ letras');
            $table->timestamp('submitted_at')->useCurrent();
            $table->timestamps();

            $table->index(['game_round_id', 'game_player_id']);
            $table->index(['game_round_id', 'game_player_id', 'word']);
            $table->index('is_valid');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('submitted_words');
    }
};
