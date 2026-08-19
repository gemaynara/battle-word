<?php

namespace App\Console\Commands;

use App\Models\DictionaryWord;
use Illuminate\Console\Command;

class ImportDictionary extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'dictionary:import
        {file : Path to word list file}
        {--min-length=2 : Minimum word length}
        {--max-length=15 : Maximum word length}';

    /**
     * The console command description.
     */
    protected $description = 'Import words from a text file into the dictionary_words table (removes accents, filters by length)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $filePath = $this->argument('file');
        $minLength = (int) $this->option('min-length');
        $maxLength = (int) $this->option('max-length');

        if (!file_exists($filePath)) {
            $this->error("File not found: {$filePath}");
            return self::FAILURE;
        }

        $this->info("Importing words from: {$filePath}");
        $this->info("Filter: {$minLength}-{$maxLength} characters");

        $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        if ($lines === false) {
            $this->error("Could not read file: {$filePath}");
            return self::FAILURE;
        }

        $totalLines = count($lines);
        $this->info("Found {$totalLines} lines to process.");

        $bar = $this->output->createProgressBar($totalLines);
        $bar->start();

        $imported = 0;
        $skipped = 0;
        $chunk = [];
        $chunkSize = 500;
        $seen = [];

        foreach ($lines as $line) {
            $original = trim($line);

            // Remove accents and convert to uppercase
            $word = $this->normalizeWord($original);

            $length = mb_strlen($word);

            // Skip empty, too short, too long, or non-alpha words
            if ($word === '' || $length < $minLength || $length > $maxLength || !$this->isAlphaOnly($word)) {
                $skipped++;
                $bar->advance();
                continue;
            }

            // Skip duplicates within this import (same normalized form)
            if (isset($seen[$word])) {
                $skipped++;
                $bar->advance();
                continue;
            }
            $seen[$word] = true;

            $chunk[] = [
                'word' => $word,
                'length' => $length,
                'is_valid' => true,
                'is_inappropriate' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            if (count($chunk) >= $chunkSize) {
                DictionaryWord::upsert($chunk, ['word'], ['length', 'updated_at']);
                $imported += count($chunk);
                $chunk = [];
            }

            $bar->advance();
        }

        // Insert remaining chunk
        if (!empty($chunk)) {
            DictionaryWord::upsert($chunk, ['word'], ['length', 'updated_at']);
            $imported += count($chunk);
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("Import complete!");
        $this->info("  Imported/Updated: {$imported}");
        $this->info("  Skipped: {$skipped}");
        $this->info("  Duplicates removed: " . ($totalLines - $imported - $skipped));

        return self::SUCCESS;
    }

    /**
     * Normalize a word: remove accents and convert to uppercase.
     */
    private function normalizeWord(string $word): string
    {
        // Transliterate accented characters to ASCII equivalents
        $word = $this->removeAccents($word);

        return mb_strtoupper($word);
    }

    /**
     * Remove accents/diacritics from a string.
     */
    private function removeAccents(string $str): string
    {
        $map = [
            'á' => 'a', 'à' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a',
            'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e',
            'í' => 'i', 'ì' => 'i', 'î' => 'i', 'ï' => 'i',
            'ó' => 'o', 'ò' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o',
            'ú' => 'u', 'ù' => 'u', 'û' => 'u', 'ü' => 'u',
            'ç' => 'c', 'ñ' => 'n',
            'Á' => 'A', 'À' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A',
            'É' => 'E', 'È' => 'E', 'Ê' => 'E', 'Ë' => 'E',
            'Í' => 'I', 'Ì' => 'I', 'Î' => 'I', 'Ï' => 'I',
            'Ó' => 'O', 'Ò' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'O',
            'Ú' => 'U', 'Ù' => 'U', 'Û' => 'U', 'Ü' => 'U',
            'Ç' => 'C', 'Ñ' => 'N',
        ];

        return strtr($str, $map);
    }

    /**
     * Check if a string contains only A-Z characters.
     */
    private function isAlphaOnly(string $word): bool
    {
        return preg_match('/^[A-Z]+$/', $word) === 1;
    }
}
