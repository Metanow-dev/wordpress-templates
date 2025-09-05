<?php

namespace App\Support;

use Illuminate\Support\Facades\File;
use Spatie\Browsershot\Browsershot;

final class Screenshotter
{
    public function __construct(
        private string $slug,
        private string $url,
        private string $dir = 'screenshots',
    ) {}

    public static function for(string $slug, string $url): self
    {
        return new self($slug, $url);
    }

    private function rel(): string { return $this->dir.'/'.$this->slug.'.jpg'; }
    private function abs(): string { return storage_path('app/public/'.$this->rel()); }
    private function publicUrl(): string { return asset('storage/'.$this->rel()); }

    private function chromePath(): ?string
    {
        // 1) .env override (recommended)
        $env = env('BROWSERSHOT_CHROME_BINARY');
        if ($env && is_file($env)) return $env;

        // 2) Puppeteer-managed Chrome (if installed in container)
        $pp = trim(@shell_exec('node -e "try{console.log(require(\'puppeteer\').executablePath())}catch(e){}"') ?: '');
        if ($pp && is_file($pp)) return $pp;

        // 3) System binaries (avoid the snap shim chromium-browser)
        foreach (['/usr/bin/google-chrome', '/usr/bin/google-chrome-stable', '/usr/bin/chromium'] as $c) {
            if (is_file($c) && is_executable($c)) return $c;
        }
        return null;
    }

    private function nodePath(): ?string
    {
        $env = env('BROWSERSHOT_NODE_BINARY');
        if ($env && is_file($env)) return $env;

        $p = trim(@shell_exec('command -v node') ?: '');
        return ($p && is_file($p)) ? $p : null;
    }

    public function capture(int $width = 1200, int $height = 800, bool $fullPage = false): string
    {
        File::ensureDirectoryExists(dirname($this->abs()), 0775, true);

        $shot = Browsershot::url($this->url)
            ->windowSize($width, $height)
            ->waitUntilNetworkIdle()
            ->setDelay(700)
            ->quality(70)
            ->addChromiumArguments(['no-sandbox','disable-dev-shm-usage','ignore-certificate-errors']);

        if ($n = $this->nodePath())   $shot->setNodeBinary($n);
        if ($c = $this->chromePath()) $shot->setChromePath($c);
        if ($fullPage)                $shot->fullPage();

        $shot->save($this->abs());
        return $this->publicUrl();
    }
}
