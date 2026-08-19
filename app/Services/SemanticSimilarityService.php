<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SemanticSimilarityService
{
    private const MODEL = 'text-embedding-3-small';
    private const CACHE_TTL = 60 * 60 * 24 * 7; // 7 days

    /**
     * Calculate semantic similarity between two words (0.0 to 1.0).
     */
    public function calculateSimilarity(string $wordA, string $wordB): float
    {
        $embeddingA = $this->getEmbedding($wordA);
        $embeddingB = $this->getEmbedding($wordB);

        if (empty($embeddingA) || empty($embeddingB)) {
            return 0.0;
        }

        return $this->cosineSimilarity($embeddingA, $embeddingB);
    }

    /**
     * Get the embedding for a word (cached).
     *
     * @return float[]
     */
    private function getEmbedding(string $word): array
    {
        $cacheKey = 'embedding:' . mb_strtolower($word);

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($word) {
            return $this->fetchEmbedding($word);
        });
    }

    /**
     * Fetch embedding from OpenAI API.
     *
     * @return float[]
     */
    private function fetchEmbedding(string $word): array
    {
        $apiKey = config('services.openai.api_key');

        if (!$apiKey) {
            Log::warning('OpenAI API key not configured');
            return [];
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$apiKey}",
                'Content-Type' => 'application/json',
            ])->timeout(10)->post('https://api.openai.com/v1/embeddings', [
                'model' => self::MODEL,
                'input' => mb_strtolower($word),
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return $data['data'][0]['embedding'] ?? [];
            }

            Log::error('OpenAI API error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return [];
        } catch (\Exception $e) {
            Log::error('OpenAI API exception', ['message' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Calculate cosine similarity between two vectors.
     */
    private function cosineSimilarity(array $a, array $b): float
    {
        $dotProduct = 0.0;
        $normA = 0.0;
        $normB = 0.0;

        $length = min(count($a), count($b));

        for ($i = 0; $i < $length; $i++) {
            $dotProduct += $a[$i] * $b[$i];
            $normA += $a[$i] * $a[$i];
            $normB += $b[$i] * $b[$i];
        }

        $normA = sqrt($normA);
        $normB = sqrt($normB);

        if ($normA == 0 || $normB == 0) {
            return 0.0;
        }

        return $dotProduct / ($normA * $normB);
    }

    /**
     * Convert similarity score (0.0-1.0) to game points (0-100).
     * Uses a threshold: below 0.3 similarity = 0 points.
     * Scale: 0.3-1.0 mapped to 0-100 points.
     */
    public function similarityToPoints(float $similarity): int
    {
        $threshold = 0.30;

        if ($similarity < $threshold) {
            return 0;
        }

        // Map 0.3-1.0 to 0-100
        $normalized = ($similarity - $threshold) / (1.0 - $threshold);

        return (int) round($normalized * 100);
    }
}
