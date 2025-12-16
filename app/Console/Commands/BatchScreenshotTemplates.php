<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class BatchScreenshotTemplates extends Command
{
    protected $signature = 'templates:batch-screenshot
        {--slugs= : Comma-separated list of template slugs}
        {--file= : Read slugs from file (txt or csv)}
        {--column=0 : Column name or index for CSV files (default: first column)}
        {--force : Force re-capture even if screenshot exists}
        {--fullpage : Capture full-page instead of viewport}
        {--w=1200 : Viewport width}
        {--h=800 : Viewport height}
        {--delay=3 : Seconds to wait between captures (default: 3)}
        {--continue-on-error : Continue processing even if a capture fails}';

    protected $description = 'Batch capture screenshots for multiple templates';

    private array $successfulSlugs = [];
    private array $failedSlugs = [];
    private array $skippedSlugs = [];

    public function handle(): int
    {
        $slugs = $this->collectSlugs();

        if (empty($slugs)) {
            $this->error('No slugs provided. Use --slugs or --file option.');
            $this->line('');
            $this->line('Examples:');
            $this->line('  php artisan templates:batch-screenshot --slugs=site1,site2,site3 --force');
            $this->line('  php artisan templates:batch-screenshot --file=slugs.txt --force');
            $this->line('  php artisan templates:batch-screenshot --file=export.csv --column=slug --force');
            return self::FAILURE;
        }

        $this->info('Batch Screenshot Capture');
        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->line("Total templates: " . count($slugs));
        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->newLine();

        $delay = max(1, (int) $this->option('delay'));
        $continueOnError = $this->option('continue-on-error');

        foreach ($slugs as $index => $slug) {
            $slug = trim($slug);
            if (empty($slug)) {
                $this->skippedSlugs[] = $slug;
                continue;
            }

            $current = $index + 1;
            $total = count($slugs);
            $this->info("[{$current}/{$total}] Processing: {$slug}");

            try {
                $exitCode = $this->call('templates:screenshot', array_filter([
                    '--slug' => $slug,
                    '--force' => $this->option('force'),
                    '--fullpage' => $this->option('fullpage'),
                    '--w' => $this->option('w'),
                    '--h' => $this->option('h'),
                ]));

                if ($exitCode === 0) {
                    $this->successfulSlugs[] = $slug;
                    $this->line("  ✓ Success");
                } else {
                    $this->failedSlugs[] = $slug;
                    $this->warn("  ✗ Failed (exit code: {$exitCode})");

                    if (!$continueOnError) {
                        $this->error("Stopping batch processing due to error. Use --continue-on-error to skip failures.");
                        break;
                    }
                }
            } catch (\Throwable $e) {
                $this->failedSlugs[] = $slug;
                $this->error("  ✗ Error: " . $e->getMessage());

                if (!$continueOnError) {
                    $this->error("Stopping batch processing due to exception. Use --continue-on-error to skip failures.");
                    break;
                }
            }

            // Delay between captures (except for last one)
            if ($current < $total) {
                $this->line("  Waiting {$delay}s before next capture...");
                sleep($delay);
            }

            $this->newLine();
        }

        // Summary report
        $this->displaySummary();

        return empty($this->failedSlugs) ? self::SUCCESS : self::FAILURE;
    }

    /**
     * Collect slugs from various input sources
     */
    private function collectSlugs(): array
    {
        $slugs = [];

        // Option 1: Direct comma-separated input
        if ($this->option('slugs')) {
            $slugs = array_map('trim', explode(',', $this->option('slugs')));
        }
        // Option 2: From file
        elseif ($this->option('file')) {
            $filePath = $this->option('file');

            if (!File::exists($filePath)) {
                $this->error("File not found: {$filePath}");
                return [];
            }

            $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

            if ($extension === 'csv') {
                $slugs = $this->parseCsvFile($filePath);
            } else {
                $slugs = $this->parseTextFile($filePath);
            }
        }

        // Remove empty entries and duplicates
        $slugs = array_values(array_unique(array_filter(array_map('trim', $slugs))));

        return $slugs;
    }

    /**
     * Parse plain text file (one slug per line)
     */
    private function parseTextFile(string $filePath): array
    {
        $content = File::get($filePath);
        $lines = explode("\n", $content);

        // Filter out empty lines and comments
        return array_filter(array_map('trim', $lines), function($line) {
            return !empty($line) && !str_starts_with($line, '#');
        });
    }

    /**
     * Parse CSV file and extract slugs
     */
    private function parseCsvFile(string $filePath): array
    {
        $slugs = [];
        $columnOption = $this->option('column');
        $columnIndex = null;
        $isFirstRow = true;
        $headers = [];

        $handle = fopen($filePath, 'r');
        if (!$handle) {
            $this->error("Could not open CSV file: {$filePath}");
            return [];
        }

        while (($row = fgetcsv($handle, 1000, ',')) !== false) {
            // Skip empty rows
            if (empty(array_filter($row))) {
                continue;
            }

            // First row: detect if it's a header
            if ($isFirstRow) {
                $isFirstRow = false;

                // Check if first row looks like a header
                $firstCell = strtolower(trim($row[0]));
                $isHeader = in_array($firstCell, ['slug', 'slugs', 'name', 'template', 'site', 'id']);

                if ($isHeader) {
                    $headers = array_map('trim', array_map('strtolower', $row));

                    // Find column index by name if specified
                    if (!is_numeric($columnOption)) {
                        $columnName = strtolower(trim($columnOption));
                        $columnIndex = array_search($columnName, $headers);

                        if ($columnIndex === false) {
                            $this->warn("Column '{$columnOption}' not found. Using first column.");
                            $columnIndex = 0;
                        }
                    } else {
                        $columnIndex = (int) $columnOption;
                    }

                    continue; // Skip header row
                }
            }

            // Determine which column to use
            if ($columnIndex === null) {
                $columnIndex = is_numeric($columnOption) ? (int) $columnOption : 0;
            }

            // Extract slug from specified column
            if (isset($row[$columnIndex])) {
                $slug = trim($row[$columnIndex]);
                if (!empty($slug)) {
                    $slugs[] = $slug;
                }
            }
        }

        fclose($handle);

        return $slugs;
    }

    /**
     * Display summary report
     */
    private function displaySummary(): void
    {
        $this->newLine();
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->info('Batch Processing Complete');
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

        $total = count($this->successfulSlugs) + count($this->failedSlugs) + count($this->skippedSlugs);

        $this->line("Total processed:  {$total}");
        $this->line("<fg=green>Successful:       " . count($this->successfulSlugs) . "</>");

        if (!empty($this->failedSlugs)) {
            $this->line("<fg=red>Failed:           " . count($this->failedSlugs) . "</>");
        }

        if (!empty($this->skippedSlugs)) {
            $this->line("<fg=yellow>Skipped:          " . count($this->skippedSlugs) . "</>");
        }

        // List failed slugs for easy retry
        if (!empty($this->failedSlugs)) {
            $this->newLine();
            $this->error('Failed Slugs (for retry):');
            foreach ($this->failedSlugs as $slug) {
                $this->line("  • {$slug}");
            }

            // Suggest retry command
            $this->newLine();
            $this->comment('To retry failed slugs, use:');
            $this->line('  php artisan templates:batch-screenshot --slugs=' . implode(',', $this->failedSlugs) . ' --force');

            // Or save to file
            $this->newLine();
            $this->comment('Or save failed slugs to a file:');
            $this->line("  echo '" . implode("\n", $this->failedSlugs) . "' > failed_slugs.txt");
            $this->line('  php artisan templates:batch-screenshot --file=failed_slugs.txt --force');
        }

        $this->newLine();
    }
}
