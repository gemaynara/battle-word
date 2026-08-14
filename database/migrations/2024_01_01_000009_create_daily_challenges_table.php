<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_challenges', function (Blueprint $table) {
            $table->id();
            $table->date('challenge_date')->unique();
            $table->string('letters', 20);
            $table->string('base_word', 50)->nullable();
            $table->unsignedSmallInteger('duration_seconds')->default(60);
            $table->unsignedSmallInteger('total_valid_words')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('challenge_date');
        });

        Schema::create('daily_challenge_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('daily_challenge_id')->constrained('daily_challenges')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedInteger('score')->default(0);
            $table->unsignedSmallInteger('words_found')->default(0);
            $table->unsignedTinyInteger('best_combo')->default(0);
            $table->unsignedTinyInteger('longest_word_length')->default(0);
            $table->json('words_submitted')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->unique(['daily_challenge_id', 'user_id']);
            $table->index(['daily_challenge_id', 'score']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_challenge_entries');
        Schema::dropIfExists('daily_challenges');
    }
};
