<?php

namespace Andruby\DeepLogin\Commands;

use Andruby\Login\LoginServiceProvider;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use SmallRuralDog\Admin\AdminServiceProvider;
use Symfony\Component\Process\Process;

class InstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'deep_login:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install deep login and publish the required assets and configurations.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->line('Publishing assets and configurations.. 🍪');

        $this->call('vendor:publish', ['--provider' => LoginServiceProvider::class,
            '--tag' => ['deep_login']]);
        
        $this->info('DeepDocs successfully installed! Enjoy 😍');
    }

    /**
     * Get the composer command for the environment.
     *
     * @return string
     */
    protected function findComposer()
    {
        if (file_exists(getcwd() . '/composer.phar')) {
            return '"' . PHP_BINARY . '" ' . getcwd() . '/composer.phar';
        }

        return 'composer';
    }
}
