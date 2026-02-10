<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class TestCommand extends Command
{
    protected $signature = 'test:hello';
    protected $description = 'A simple test command to verify Artisan registration';

    public function handle(): int
    {
        $this->info('TestCommand works!');
        return self::SUCCESS;
    }
}
