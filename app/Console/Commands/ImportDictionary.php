<?php

namespace App\Console\Commands;

use App\Models\DictionaryWord;
use Illuminate\Console\Command;

class ImportDictionary extends Command
{
    protected $signature = 'dictionary:import
        {file : Path to word list file (one word per line, or CSV word,score)}
        {--min-length=3 : Minimum word length}
        {--max-length=12 : Maximum word length}
        {--max-icf=9.5 : Maximum ICF score (lower = more common, only for ICF files)}
        {--format=plain : File format: plain (one word per line) or icf (word,score)}
        {--fresh : Truncate the table before importing}';

    protected $description = 'Import words from a text/ICF file into the dictionary_words table';

    public function handle(): int
    {
        $filePath = $this->argument('file');
        $minLength = (int) $this->option('min-length');
        $maxLength = (int) $this->option('max-length');
        $maxIcf = (float) $this->option('max-icf');
        $format = $this->option('format');
        $fresh = $this->option('fresh');

        if (!file_exists($filePath)) {
            $this->error("File not found: {$filePath}");
            return self::FAILURE;
        }

        if ($fresh) {
            $this->info("Truncating dictionary_words table...");
            DictionaryWord::truncate();
        }

        $this->info("Importing words from: {$filePath}");
        $this->info("Filter: {$minLength}-{$maxLength} chars, format={$format}" . ($format === 'icf' ? ", max_icf={$maxIcf}" : ''));

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
            $bar->advance();

            if ($format === 'icf') {
                $parts = explode(',', $line, 2);
                if (count($parts) !== 2) {
                    $skipped++;
                    continue;
                }
                $original = trim($parts[0]);
                $icfScore = (float) trim($parts[1]);

                // Skip words with ICF higher than threshold (too rare)
                if ($icfScore > $maxIcf) {
                    $skipped++;
                    continue;
                }
            } else {
                $original = trim($line);
            }

            // Normalize: remove accents, uppercase
            $word = $this->normalizeWord($original);
            $length = mb_strlen($word);

            // Filter
            if ($word === '' || $length < $minLength || $length > $maxLength || !$this->isAlphaOnly($word)) {
                $skipped++;
                continue;
            }

            // Deduplicate
            if (isset($seen[$word])) {
                $skipped++;
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
        }

        if (!empty($chunk)) {
            DictionaryWord::upsert($chunk, ['word'], ['length', 'updated_at']);
            $imported += count($chunk);
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("Import complete!");
        $this->info("  Imported/Updated: {$imported}");
        $this->info("  Skipped: {$skipped}");

        return self::SUCCESS;
    }

    private function normalizeWord(string $word): string
    {
        return mb_strtoupper($this->removeAccents($word));
    }

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

    private function isAlphaOnly(string $word): bool
    {
        return preg_match('/^[A-Z]+$/', $word) === 1;
    }
}
