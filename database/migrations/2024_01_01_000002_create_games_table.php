<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('games', function (Blueprint $table) {
            $table->id();
            $table->foreignId('host_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('code', 6)->unique()->comment('Código curto para entrar na partida');
            $table->enum('status', ['waiting', 'playing', 'paused', 'finished', 'cancelled'])->default('waiting');
            $table->enum('mode', ['arena', 'vs_computer', 'online_1v1', 'daily_challenge'])->default('arena');
            $table->unsignedTinyInteger('max_players')->default(10);
            $table->unsignedTinyInteger('total_rounds')->default(1);
            $table->unsignedSmallInteger('round_duration_seconds')->default(60);
            $table->json('settings')->nullable()->comment('Configurações extras da partida');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('mode');
            $table->index('code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('games');
    }
};
