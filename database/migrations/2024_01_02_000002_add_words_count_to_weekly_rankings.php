<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('weekly_rankings', function (Blueprint $table) {
            $table->unsignedSmallInteger('best_words_count')->default(0)->after('best_score');
        });
    }

    public function down(): void
    {
        Schema::table('weekly_rankings', function (Blueprint $table) {
            $table->dropColumn('best_words_count');
        });
    }
};
