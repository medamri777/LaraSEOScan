<?php

namespace App\Console\Commands;

use App\Models\ToolUsageLog;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class ResetDailyUsageCommand extends Command
{
    protected $signature = 'usage:reset-daily {--days=7 : Purge logs older than this many days}';

    protected $description = 'Purge old tool usage logs (runs daily at midnight to clean up stale rows)';

    public function handle(): int
    {
        $days = (int) $this->option('days');

        $deleted = ToolUsageLog::purgeOlderThan($days);

        $this->info("Purged {$deleted} tool usage log rows older than {$days} days.");

        return self::SUCCESS;
    }
}
