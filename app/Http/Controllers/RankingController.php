<?php

namespace App\Http\Controllers;

use App\Models\WeeklyRanking;
use Illuminate\Http\JsonResponse;

class RankingController extends Controller
{
    /**
     * Get the weekly top 10 ranking.
     * GET /api/ranking/weekly
     */
    public function weekly(): JsonResponse
    {
        $ranking = WeeklyRanking::currentWeekTop10();
        $weekKey = now()->format('Y-\\WW');

        return response()->json([
            'week' => $weekKey,
            'ranking' => $ranking,
        ]);
    }
}
