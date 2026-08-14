<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dictionary_words', function (Blueprint $table) {
            $table->id();
            $table->string('word', 50)->unique();
            $table->unsignedTinyInteger('length');
            $table->unsignedInteger('frequency')->default(0)->comment('Frequência de uso (maior = mais comum)');
            $table->boolean('is_valid')->default(true);
            $table->boolean('is_inappropriate')->default(false);
            $table->string('category', 50)->nullable()->comment('Categoria gramatical: substantivo, verbo, etc.');
            $table->timestamps();

            $table->index('length');
            $table->index('is_valid');
            $table->index(['is_valid', 'length']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dictionary_words');
    }
};
