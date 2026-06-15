<?php

namespace App\Jobs;

use App\Models\CompetitorRanking;
use App\Models\Keyword;
use App\Models\KeywordRanking;
use App\Models\ProjectCompetitor;
use App\Models\RankCheckBatch;
use App\Services\DataForSeoService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class CheckKeywordRankings implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $timeout = 180;

    public function __construct(
        public readonly RankCheckBatch $batch,
        public readonly array $keywordIds
    ) {}

    public function handle(DataForSeoService $dataForSeo): void
    {
        $this->batch->update(['status' => 'processing', 'started_at' => now()]);

        $keywords = Keyword::whereIn('id', $this->keywordIds)
            ->with('project')
            ->get();

        if ($keywords->isEmpty()) {
            $this->batch->update(['status' => 'completed', 'finished_at' => now()]);
            return;
        }

        $today     = Carbon::today()->toDateTimeString();
        $completed = 0;
        $failed    = 0;

        // Group by (project, location, language, device) for efficient batching
        $groups = $keywords->groupBy(fn(Keyword $kw) =>
            "{$kw->project_id}|{$kw->location_code}|{$kw->language_code}|{$kw->device}"
        );

        foreach ($groups as $groupKey => $groupKeywords) {
            [$projectId, $locationCode, $languageCode, $device] = explode('|', $groupKey);

            $project      = $groupKeywords->first()->project;
            $ownDomain    = $dataForSeo->extractHost($project->url);

            // Load active competitors for this project
            $competitors  = ProjectCompetitor::where('project_id', $projectId)->get();
            $compDomains  = $competitors->map(fn($c) => $dataForSeo->extractHost($c->url))->all();
            $allDomains   = array_unique(array_merge([$ownDomain], $compDomains));

            $kwStrings = $groupKeywords->pluck('keyword')->all();

            try {
                // Single SERP call — extract positions for own domain + all competitors
                $multiResults = $dataForSeo->checkRankingsBatchMultiDomain(
                    $kwStrings,
                    $allDomains,
                    (int) $locationCode,
                    $languageCode,
                    $device
                );

                foreach ($groupKeywords as $keyword) {
                    $kwResults = $multiResults[$keyword->keyword] ?? null;

                    if (! $kwResults) {
                        $failed++;
                        continue;
                    }

                    try {
                        // ── Own ranking ──────────────────────────────────────
                        $ownResult = $kwResults[$ownDomain] ?? $dataForSeo->emptyResult();

                        $previousRanking = KeywordRanking::where('keyword_id', $keyword->id)
                            ->where('checked_at', '<', $today)
                            ->latest('checked_at')
                            ->first();

                        KeywordRanking::updateOrCreate(
                            ['keyword_id' => $keyword->id, 'checked_at' => $today],
                            [
                                'rank'          => $ownResult['rank'],
                                'previous_rank' => $previousRanking?->rank,
                                'url'           => $ownResult['url'],
                                'domain'        => $ownResult['domain'],
                                'title'         => $ownResult['title'],
                                'search_volume' => $ownResult['search_volume'],
                                'cpc'           => $ownResult['cpc'],
                                'competition'   => $ownResult['competition'],
                                'serp_features' => $ownResult['serp_features'],
                                'data_source'   => $ownResult['data_source'] ?? ($ownResult['_mock'] ?? false ? 'free_tier' : 'dataforseo'),
                            ]
                        );

                        $keyword->update(['last_checked_at' => $today]);

                        // ── Competitor rankings ───────────────────────────────
                        foreach ($competitors as $competitor) {
                            $compDomain = $dataForSeo->extractHost($competitor->url);
                            $compResult = $kwResults[$compDomain] ?? $dataForSeo->emptyResult();

                            CompetitorRanking::updateOrCreate(
                                [
                                    'keyword_id'    => $keyword->id,
                                    'competitor_id' => $competitor->id,
                                    'checked_at'    => $today,
                                ],
                                [
                                    'rank'  => $compResult['rank'],
                                    'url'   => $compResult['url'],
                                    'title' => $compResult['title'],
                                ]
                            );
                        }

                        $completed++;

                    } catch (\Throwable $e) {
                        Log::error('Failed to save keyword ranking', [
                            'keyword_id' => $keyword->id,
                            'error'      => $e->getMessage(),
                        ]);
                        $failed++;
                    }
                }

            } catch (\Throwable $e) {
                Log::error('DataForSEO batch group failed', [
                    'group' => $groupKey,
                    'error' => $e->getMessage(),
                ]);
                $failed += count($groupKeywords);
            }
        }

        $this->batch->update([
            'status'          => $failed === count($this->keywordIds) ? 'failed' : 'completed',
            'completed_count' => $completed,
            'failed_count'    => $failed,
            'finished_at'     => now(),
        ]);
    }

    public function failed(\Throwable $exception): void
    {
        $this->batch->update([
            'status'        => 'failed',
            'error_message' => $exception->getMessage(),
            'finished_at'   => now(),
        ]);
    }
}
