<?php

namespace App\Services;

class LetterSetResult
{
    public function __construct(
        public readonly string $letters,
        public readonly string $baseWord,
        public readonly int $validWordCount,
    ) {}
}
