<?php

namespace App\Services;

class ScoreResult
{
    public function __construct(
        public readonly int $points,
        public readonly int $comboMultiplier,
        public readonly int $totalPoints,
        public readonly bool $isPerfectWord,
        public readonly bool $isLongWord,
        public readonly int $newCombo = 1,
    ) {}
}
