<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class CleanupChromeProcessesCommand extends Command
{
    protected $signature = 'chrome:cleanup
        {--force : Kill all Chrome/Puppeteer processes immediately}
        {--dry-run : Show what would be cleaned without doing it}';

    protected $description = 'Clean up zombie Chrome/Puppeteer processes and temporary directories';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        $force = $this->option('force');

        $this->info('=== Chrome/Puppeteer Cleanup ===');
        $this->newLine();

        // 1. Find and kill zombie Chrome processes
        $this->cleanupChromeProcesses($dryRun, $force);

        // 2. Clean up temporary Puppeteer directories
        $this->cleanupTempDirectories($dryRun);

        // 3. Show memory stats
        $this->showMemoryStats();

        $this->newLine();
        $this->info('Cleanup complete!');

        return self::SUCCESS;
    }

    protected function cleanupChromeProcesses(bool $dryRun, bool $force): void
    {
        $this->line('Checking for Chrome/Puppeteer processes...');

        $processes = $this->findChromeProcesses();

        if (empty($processes)) {
            $this->info('✓ No zombie Chrome processes found');
            return;
        }

        $count = count($processes);
        $this->warn("Found {$count} Chrome/Puppeteer process(es)");

        if ($dryRun) {
            $this->table(['PID', 'User', 'CPU%', 'Memory', 'Command'], $processes);
            $this->line('(Dry run - would kill these processes)');
            return;
        }

        if (!$force && !$this->confirm('Kill these processes?', true)) {
            $this->line('Skipped');
            return;
        }

        $killed = 0;
        foreach ($processes as $process) {
            try {
                $pid = $process['pid'];
                exec("kill -9 {$pid} 2>&1", $output, $returnCode);

                if ($returnCode === 0) {
                    $killed++;
                    $this->line("✓ Killed PID {$pid}");
                }
            } catch (\Exception $e) {
                $this->error("Failed to kill PID {$process['pid']}: " . $e->getMessage());
            }
        }

        $this->info("✓ Killed {$killed} process(es)");
        Log::info("Chrome cleanup: killed {$killed} zombie processes");
    }

    protected function cleanupTempDirectories(bool $dryRun): void
    {
        $this->line('Checking for Puppeteer temp directories...');

        $tmpDirs = $this->findPuppeteerTempDirs();

        if (empty($tmpDirs)) {
            $this->info('✓ No Puppeteer temp directories found');
            return;
        }

        $count = count($tmpDirs);
        $totalSize = 0;

        foreach ($tmpDirs as $dir) {
            $totalSize += $dir['size'];
        }

        $totalSizeMB = round($totalSize / 1024 / 1024, 2);
        $this->warn("Found {$count} Puppeteer temp director(ies) using {$totalSizeMB} MB");

        if ($dryRun) {
            $this->table(['Directory', 'Size (MB)', 'Age'], array_map(function ($dir) {
                return [
                    basename($dir['path']),
                    round($dir['size'] / 1024 / 1024, 2),
                    $dir['age'],
                ];
            }, $tmpDirs));
            $this->line('(Dry run - would delete these directories)');
            return;
        }

        $deleted = 0;
        $freedSize = 0;

        foreach ($tmpDirs as $dir) {
            try {
                exec("rm -rf " . escapeshellarg($dir['path']) . " 2>&1", $output, $returnCode);
                if ($returnCode === 0) {
                    $deleted++;
                    $freedSize += $dir['size'];
                    $this->line("✓ Deleted " . basename($dir['path']));
                }
            } catch (\Exception $e) {
                $this->error("Failed to delete {$dir['path']}: " . $e->getMessage());
            }
        }

        $freedSizeMB = round($freedSize / 1024 / 1024, 2);
        $this->info("✓ Deleted {$deleted} director(ies), freed {$freedSizeMB} MB");
        Log::info("Chrome cleanup: deleted {$deleted} temp dirs, freed {$freedSizeMB} MB");
    }

    protected function showMemoryStats(): void
    {
        $this->line('Current memory usage:');

        $memInfo = $this->getMemoryInfo();

        if ($memInfo) {
            $this->table(
                ['Metric', 'Value'],
                [
                    ['Total RAM', $memInfo['total']],
                    ['Used RAM', $memInfo['used'] . ' (' . $memInfo['used_percent'] . '%)'],
                    ['Free RAM', $memInfo['free']],
                    ['Available RAM', $memInfo['available']],
                ]
            );
        }
    }

    protected function findChromeProcesses(): array
    {
        $processes = [];

        // Use auxww to prevent line truncation
        exec("/bin/ps auxww 2>&1", $psOutput, $returnCode);

        if ($returnCode !== 0 || empty($psOutput)) {
            return [];
        }

        // Filter for Chrome/Chromium processes
        $filtered = array_filter($psOutput, function($line) {
            return (stripos($line, 'google-chrome') !== false || stripos($line, 'chromium') !== false)
                   && stripos($line, 'grep') === false;
        });

        $output = implode("\n", $filtered);

        if (empty($output)) {
            return [];
        }

        $lines = explode("\n", trim($output));

        foreach ($lines as $line) {
            if (empty($line)) {
                continue;
            }

            // Parse ps output: USER PID ...
            $parts = preg_split('/\s+/', $line, 11);

            if (count($parts) < 11) {
                continue;
            }

            $user = $parts[0];
            $pid = $parts[1];
            $command = $parts[10] ?? '';

            // Skip if it's a user's active browser (has user data in home directory)
            if (str_contains($command, '--user-data-dir=/home') ||
                str_contains($command, '--user-data-dir=/root/.config/google-chrome')) {
                continue;
            }

            // Include Puppeteer/headless Chrome
            // These have: --user-data-dir=/tmp/puppeteer, --headless, --enable-automation
            if (str_contains($command, '/tmp/puppeteer') ||
                str_contains($command, '--headless') ||
                str_contains($command, '--enable-automation')) {

                $processes[] = [
                    'user' => $user,
                    'pid' => $pid,
                    'cpu' => $parts[2],
                    'mem' => $parts[3],
                    'command' => substr($command, 0, 80),
                ];
            }
        }

        return $processes;
    }

    protected function findPuppeteerTempDirs(): array
    {
        $dirs = [];
        $tmpPath = sys_get_temp_dir();

        $foundDirs = glob($tmpPath . '/puppeteer_dev_chrome_profile-*');

        if (!$foundDirs) {
            return [];
        }

        foreach ($foundDirs as $dir) {
            if (!is_dir($dir)) {
                continue;
            }

            $size = $this->getDirectorySize($dir);
            $mtime = filemtime($dir);
            $age = $this->getAge($mtime);

            $dirs[] = [
                'path' => $dir,
                'size' => $size,
                'age' => $age,
                'mtime' => $mtime,
            ];
        }

        return $dirs;
    }

    protected function getDirectorySize(string $path): int
    {
        $size = 0;

        try {
            $output = shell_exec("du -sb " . escapeshellarg($path) . " 2>/dev/null | cut -f1");
            $size = (int) trim($output);
        } catch (\Exception $e) {
            // Fallback method
            $files = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS)
            );

            foreach ($files as $file) {
                $size += $file->getSize();
            }
        }

        return $size;
    }

    protected function getAge(int $timestamp): string
    {
        $diff = time() - $timestamp;

        if ($diff < 3600) {
            return round($diff / 60) . ' minutes';
        } elseif ($diff < 86400) {
            return round($diff / 3600) . ' hours';
        } else {
            return round($diff / 86400) . ' days';
        }
    }

    protected function getMemoryInfo(): ?array
    {
        $output = shell_exec('free -b 2>/dev/null');

        if (!$output) {
            return null;
        }

        $lines = explode("\n", trim($output));

        if (count($lines) < 2) {
            return null;
        }

        preg_match('/^Mem:\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)/', $lines[1], $matches);

        if (!$matches) {
            return null;
        }

        $total = (int) $matches[1];
        $used = (int) $matches[2];
        $free = (int) $matches[3];
        $available = (int) $matches[6];

        return [
            'total' => $this->formatBytes($total),
            'used' => $this->formatBytes($used),
            'used_percent' => round(($used / $total) * 100, 1),
            'free' => $this->formatBytes($free),
            'available' => $this->formatBytes($available),
        ];
    }

    protected function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = 0;

        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }
}
