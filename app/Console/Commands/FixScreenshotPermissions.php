<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class FixScreenshotPermissions extends Command
{
    protected $signature = 'templates:fix-permissions {--path=}';
    protected $description = 'Fix ownership and permissions for screenshots and variants';

    public function handle(): int
    {
        $path = $this->option('path') ?: storage_path('app/public/screenshots');
        if (!is_dir($path)) {
            $this->error('Path not found: '.$path);
            return self::FAILURE;
        }

        $user = env('SCREENSHOT_SYSUSER');
        $group = env('SCREENSHOT_GROUP');

        // Try to infer from parent directory if not provided
        if (!$user) {
            $ownerId = @fileowner($path);
            if ($ownerId !== false && function_exists('posix_getpwuid')) {
                $pw = @posix_getpwuid($ownerId);
                if ($pw && !empty($pw['name'])) $user = $pw['name'];
            }
        }
        if (!$group) {
            $groupId = @filegroup($path);
            if ($groupId !== false && function_exists('posix_getgrgid')) {
                $gr = @posix_getgrgid($groupId);
                if ($gr && !empty($gr['name'])) $group = $gr['name'];
            }
        }

        $this->info("Fixing permissions under: $path");
        $this->line("Target user: ".($user ?: '(unchanged)')." group: ".($group ?: '(unchanged)'));

        $countFiles = 0; $countDirs = 0; $errors = 0;
        $rii = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS), \RecursiveIteratorIterator::SELF_FIRST);
        foreach ($rii as $item) {
            try {
                if ($item->isDir()) {
                    @chmod($item->getPathname(), 0755);
                    if ($user && function_exists('chown')) @chown($item->getPathname(), $user);
                    if ($group && function_exists('chgrp')) @chgrp($item->getPathname(), $group);
                    $countDirs++;
                } else {
                    @chmod($item->getPathname(), 0644);
                    if ($user && function_exists('chown')) @chown($item->getPathname(), $user);
                    if ($group && function_exists('chgrp')) @chgrp($item->getPathname(), $group);
                    $countFiles++;
                }
            } catch (\Throwable $e) {
                $errors++;
            }
        }

        // Shell chown fallback if PHP functions are disabled and we have a user/group
        if (($user || $group) && function_exists('shell_exec')) {
            @shell_exec('chown -R '.escapeshellarg($user ?: '').':'.escapeshellarg($group ?: '').' '.escapeshellarg($path));
        }

        $this->info("Done. Files: {$countFiles}, Dirs: {$countDirs}, Errors: {$errors}");
        $this->warn('If ownership did not change, run this command as the domain system user, or adjust SCREENSHOT_SYSUSER/SCREENSHOT_GROUP in .env');
        return self::SUCCESS;
    }
}

