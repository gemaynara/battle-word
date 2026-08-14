<?php

namespace App\Console\Commands;

use App\Models\DictionaryWord;
use Illuminate\Console\Command;

class ImportDictionary extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'dictionary:import {file : Path to word list file}';

    /**
     * The console command description.
     */
    protected $description = 'Import words from a text file into the dictionary_words table';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $filePath = $this->argument('file');

        if (!file_exists($filePath)) {
            $this->error("File not found: {$filePath}");
            return self::FAILURE;
        }

        $this->info("Importing words from: {$filePath}");

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

        foreach ($lines as $line) {
            $word = mb_strtoupper(trim($line));

            // Skip empty lines and words longer than 50 chars
            if ($word === '' || mb_strlen($word) > 50) {
                $skipped++;
                $bar->advance();
                continue;
            }

            $chunk[] = [
                'word' => $word,
                'length' => mb_strlen($word),
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

        return self::SUCCESS;
    }
}
