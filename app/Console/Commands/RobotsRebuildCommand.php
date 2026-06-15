<?php

namespace App\Console\Commands;

use App\Services\RobotsService;
use Illuminate\Console\Command;

class RobotsRebuildCommand extends Command
{
    protected $signature = 'robots:rebuild';
    protected $description = 'Rebuild and cache the robots.txt content from DB rules';

    public function handle(RobotsService $service): int
    {
        $service->cacheRobots();
        $this->info('Robots.txt rebuilt and cached successfully.');
        return Command::SUCCESS;
    }
}
