<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Helpers\Themes;
use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

/**
 * Class InstallThemePackages.
 *
 * This class is the artisan command wrapper for theme package dependency installations.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 */
class InstallThemePackages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'obms:install-theme-packages';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install theme package dependencies';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Themes::list()
            ->reject(function ($paths) {
                return ! file_exists($paths->base_path . '/package.json');
            })
            ->each(function ($paths, string $key) {
                $this->info('Installing packages for theme "' . $key . '"');
                $process = Process::fromShellCommandline('npm --prefix ' . $paths->base_path . ' ci');

                $processOutput = '';
                $captureOutput = function ($type, $line) use (&$processOutput) {
                    $processOutput .= $line;
                };
                $process->setTimeout(null)->run($captureOutput);

                if ($process->getExitCode()) {
                    $this->warn($processOutput);
                }
            });
    }
}
