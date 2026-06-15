<?php

namespace App\Console\Commands;

use App\Jobs\CheckKeywordRankings;
use App\Models\Keyword;
use App\Models\Project;
use App\Models\RankCheckBatch;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class CheckAllKeywordRankings extends Command
{
    protected $signature   = 'ranktracker:check-all
                                {--project= : Only check keywords for a specific project ID}
                                {--force    : Run even if keywords were already checked today}';

    protected $description = 'Dispatch rank-check jobs for all active keywords across all projects.';

    public function handle(): int
    {
        $today = Carbon::today()->toDateString(); // YYYY-MM-DD for date comparison

        $query = Keyword::active()->with('project');

        if ($projectId = $this->option('project')) {
            $query->where('project_id', $projectId);
        }

        if (! $this->option('force')) {
            // Skip keywords already checked today
            $query->where(function ($q) use ($today) {
                $q->whereNull('last_checked_at')
                  ->orWhereDate('last_checked_at', '<', $today);
            });
        }

        $allKeywords = $query->get();

        if ($allKeywords->isEmpty()) {
            $this->info('No keywords to check. All up-to-date or none active.');
            return self::SUCCESS;
        }

        // Group by project for per-project batch jobs
        $byProject = $allKeywords->groupBy('project_id');
        $jobCount  = 0;

        foreach ($byProject as $projectId => $projectKeywords) {
            $project = $projectKeywords->first()->project;

            $batch = RankCheckBatch::create([
                'project_id'     => $projectId,
                'tenant_id'      => $project->tenant_id,
                'status'         => 'pending',
                'keywords_count' => $projectKeywords->count(),
            ]);

            CheckKeywordRankings::dispatch($batch, $projectKeywords->pluck('id')->all());
            $jobCount++;

            $this->line("  Dispatched batch #{$batch->id} — {$projectKeywords->count()} keywords for \"{$project->name}\"");
        }

        $this->info("Done. {$jobCount} batch job(s) dispatched for {$allKeywords->count()} total keyword(s).");

        return self::SUCCESS;
    }
}
