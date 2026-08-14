<?php

namespace Tests\Unit;

use App\Models\DictionaryWord;
use App\Services\LetterSetGenerator;
use App\Services\LetterSetResult;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LetterSetGeneratorTest extends TestCase
{
    use RefreshDatabase;

    private LetterSetGenerator $generator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->generator = new LetterSetGenerator();
    }

    public function test_generate_returns_letter_set_result(): void
    {
        $this->seedBaseWord('MARTES');
        $this->seedFormableWords('MARTES', 12);

        $result = $this->generator->generate();

        $this->assertInstanceOf(LetterSetResult::class, $result);
        $this->assertEquals('MARTES', $result->letters);
        $this->assertEquals('MARTES', $result->baseWord);
        $this->assertGreaterThanOrEqual(10, $result->validWordCount);
    }

    public function test_generate_selects_base_word_between_5_and_12_chars(): void
    {
        // Seed a valid base word (6 chars)
        $this->seedBaseWord('CASTOR');
        $this->seedFormableWords('CASTOR', 12);

        // Seed invalid length words (too short and too long)
        DictionaryWord::create([
            'word' => 'GATO',
            'length' => 4,
            'is_valid' => true,
            'is_inappropriate' => false,
        ]);
        DictionaryWord::create([
            'word' => 'EXTRAORDINARIO',
            'length' => 14,
            'is_valid' => true,
            'is_inappropriate' => false,
        ]);

        $result = $this->generator->generate();

        $this->assertGreaterThanOrEqual(5, mb_strlen($result->letters));
        $this->assertLessThanOrEqual(12, mb_strlen($result->letters));
    }

    public function test_generate_excludes_inappropriate_words(): void
    {
        // Seed an inappropriate word that is the only one with valid length
        DictionaryWord::create([
            'word' => 'BADWORD',
            'length' => 7,
            'is_valid' => true,
            'is_inappropriate' => true,
        ]);

        // Seed a valid base word
        $this->seedBaseWord('PLANTAS');
        $this->seedFormableWords('PLANTAS', 12);

        $result = $this->generator->generate();

        $this->assertNotEquals('BADWORD', $result->baseWord);
    }

    public function test_generate_excludes_invalid_words(): void
    {
        // Seed an invalid word
        DictionaryWord::create([
            'word' => 'XYZWVK',
            'length' => 6,
            'is_valid' => false,
            'is_inappropriate' => false,
        ]);

        // Seed a valid base word
        $this->seedBaseWord('PLANTAS');
        $this->seedFormableWords('PLANTAS', 12);

        $result = $this->generator->generate();

        $this->assertNotEquals('XYZWVK', $result->baseWord);
    }

    public function test_generate_retries_when_valid_word_count_below_threshold(): void
    {
        // First word: few formable words (will fail threshold)
        DictionaryWord::create([
            'word' => 'ZZXQW',
            'length' => 5,
            'is_valid' => true,
            'is_inappropriate' => false,
        ]);

        // Second word: enough formable words (will pass)
        $this->seedBaseWord('MARTES');
        $this->seedFormableWords('MARTES', 12);

        $result = $this->generator->generate();

        // Eventually the generator should find the good word
        $this->assertGreaterThanOrEqual(10, $result->validWordCount);
    }

    public function test_generate_uses_last_result_after_max_attempts(): void
    {
        // Seed only words that won't generate enough formable words
        DictionaryWord::create([
            'word' => 'ZZXQW',
            'length' => 5,
            'is_valid' => true,
            'is_inappropriate' => false,
        ]);

        $result = $this->generator->generate();

        // Should still return a result (the last attempt)
        $this->assertInstanceOf(LetterSetResult::class, $result);
        $this->assertNotEmpty($result->letters);
        $this->assertLessThan(10, $result->validWordCount);
    }

    public function test_count_valid_words_respects_letter_quantities(): void
    {
        // "MARTE" has one 'M'
        DictionaryWord::create([
            'word' => 'MA',
            'length' => 2,
            'is_valid' => true,
            'is_inappropriate' => false,
        ]);

        // "MM" requires two M's but letter set "MARTE" only has one
        DictionaryWord::create([
            'word' => 'MM',
            'length' => 2,
            'is_valid' => true,
            'is_inappropriate' => false,
        ]);

        $count = $this->generator->countValidWords('MARTE');

        // Only "MA" should be valid (MM requires 2 M's)
        $this->assertEquals(1, $count);
    }

    public function test_count_valid_words_minimum_length_is_2(): void
    {
        DictionaryWord::create([
            'word' => 'A',
            'length' => 1,
            'is_valid' => true,
            'is_inappropriate' => false,
        ]);
        DictionaryWord::create([
            'word' => 'AR',
            'length' => 2,
            'is_valid' => true,
            'is_inappropriate' => false,
        ]);

        $count = $this->generator->countValidWords('ARTES');

        // Only "AR" counts (single-char "A" is below min length)
        $this->assertEquals(1, $count);
    }

    public function test_get_valid_words_for_letters_returns_collection(): void
    {
        DictionaryWord::create([
            'word' => 'MAR',
            'length' => 3,
            'is_valid' => true,
            'is_inappropriate' => false,
        ]);
        DictionaryWord::create([
            'word' => 'ARTE',
            'length' => 4,
            'is_valid' => true,
            'is_inappropriate' => false,
        ]);
        DictionaryWord::create([
            'word' => 'ZZZ',
            'length' => 3,
            'is_valid' => true,
            'is_inappropriate' => false,
        ]);

        $words = $this->generator->getValidWordsForLetters('MARTES');

        $wordList = $words->pluck('word')->toArray();
        $this->assertContains('MAR', $wordList);
        $this->assertContains('ARTE', $wordList);
        $this->assertNotContains('ZZZ', $wordList);
    }

    public function test_get_valid_words_excludes_words_longer_than_letter_set(): void
    {
        DictionaryWord::create([
            'word' => 'MARTELOS',
            'length' => 8,
            'is_valid' => true,
            'is_inappropriate' => false,
        ]);

        $words = $this->generator->getValidWordsForLetters('MARTE');

        $this->assertEmpty($words);
    }

    public function test_letters_are_uppercase(): void
    {
        $this->seedBaseWord('PLANTAS');
        $this->seedFormableWords('PLANTAS', 12);

        $result = $this->generator->generate();

        $this->assertEquals(strtoupper($result->letters), $result->letters);
    }

    /**
     * Helper: seed a valid base word.
     */
    private function seedBaseWord(string $word): void
    {
        DictionaryWord::create([
            'word' => strtoupper($word),
            'length' => mb_strlen($word),
            'is_valid' => true,
            'is_inappropriate' => false,
        ]);
    }

    /**
     * Helper: seed formable words from a letter set to exceed threshold.
     */
    private function seedFormableWords(string $letters, int $count): void
    {
        $upperLetters = strtoupper($letters);
        $chars = str_split($upperLetters);

        // Generate 2-character combinations from available letters
        $created = 0;
        for ($i = 0; $i < count($chars) && $created < $count; $i++) {
            for ($j = 0; $j < count($chars) && $created < $count; $j++) {
                if ($i === $j) {
                    continue;
                }
                $word = $chars[$i] . $chars[$j];

                // Avoid duplicate words
                if (DictionaryWord::where('word', $word)->exists()) {
                    continue;
                }

                DictionaryWord::create([
                    'word' => $word,
                    'length' => 2,
                    'is_valid' => true,
                    'is_inappropriate' => false,
                ]);
                $created++;
            }
        }
    }
}
