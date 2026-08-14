<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('game_rounds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_id')->constrained('games')->cascadeOnDelete();
            $table->unsignedTinyInteger('round_number');
            $table->string('letters', 20)->comment('Letras disponíveis na rodada (ex: MARTES)');
            $table->string('base_word', 50)->nullable()->comment('Palavra-base usada para gerar as letras');
            $table->unsignedSmallInteger('duration_seconds')->default(60);
            $table->enum('status', ['waiting', 'playing', 'finished'])->default('waiting');
            $table->unsignedSmallInteger('total_valid_words')->default(0)->comment('Total de palavras possíveis com as letras');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();

            $table->unique(['game_id', 'round_number']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_rounds');
    }
};
