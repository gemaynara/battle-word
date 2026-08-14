<?php

namespace App\Services;

class ValidationResult
{
    public function __construct(
        public readonly bool $isValid,
        public readonly ?string $rejectionReason = null,
        public readonly int $points = 0,
        public readonly int $comboMultiplier = 1,
        public readonly int $totalPoints = 0,
        public readonly bool $isPerfectWord = false,
        public readonly bool $isLongWord = false,
    ) {}

    public static function valid(int $points = 0, int $comboMultiplier = 1, int $totalPoints = 0, bool $isPerfectWord = false, bool $isLongWord = false): self
    {
        return new self(
            isValid: true,
            rejectionReason: null,
            points: $points,
            comboMultiplier: $comboMultiplier,
            totalPoints: $totalPoints,
            isPerfectWord: $isPerfectWord,
            isLongWord: $isLongWord,
        );
    }

    public static function invalid(string $rejectionReason): self
    {
        return new self(isValid: false, rejectionReason: $rejectionReason);
    }
}
