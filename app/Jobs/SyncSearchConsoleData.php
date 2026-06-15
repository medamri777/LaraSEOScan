<?php

namespace App\Jobs;

use App\Models\GscConnection;
use App\Models\GscDailySnapshot;
use App\Services\GoogleSearchConsoleService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncSearchConsoleData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds to wait before retrying.
     */
    public int $backoff = 60;

    public function __construct()
    {
        //
    }

    public function handle(GoogleSearchConsoleService $gsc): void
    {
        $connections = GscConnection::with('project')->get();

        if ($connections->isEmpty()) {
            Log::info('SyncSearchConsoleData: No GSC connections found, skipping.');
            return;
        }

        foreach ($connections as $connection) {
            try {
                $this->syncConnection($connection, $gsc);
            } catch (\Throwable $e) {
                Log::error('SyncSearchConsoleData: Failed for connection', [
                    'connection_id' => $connection->id,
                    'project_id'    => $connection->project_id,
                    'error'         => $e->getMessage(),
                ]);
                // Continue with next connection instead of failing the whole job
                continue;
            }
        }

        Log::info('SyncSearchConsoleData: Completed sync for ' . $connections->count() . ' connection(s).');
    }

    protected function syncConnection(GscConnection $connection, GoogleSearchConsoleService $gsc): void
    {
        $yesterday = now()->subDay()->toDateString();

        // 1) Fetch daily performance for yesterday
        $performance = $gsc->getDailyPerformance($connection, $yesterday, $yesterday);
        $rows = $performance['rows'] ?? [];

        $totalClicks      = 0;
        $totalImpressions = 0;
        $positionSum      = 0;
        $count            = 0;

        foreach ($rows as $row) {
            $totalClicks      += $row['clicks'];
            $totalImpressions += $row['impressions'];
            $positionSum      += $row['position'];
            $count++;
        }

        $avgCtr      = $totalImpressions > 0 ? round(($totalClicks / $totalImpressions) * 100, 4) : 0;
        $avgPosition = $count > 0 ? round($positionSum / $count, 2) : 0;

        // 2) Fetch top 100 queries for yesterday
        $topQueriesData = $gsc->getTopQueries($connection, 100, $yesterday, $yesterday);
        $topQueries = array_map(fn ($row) => [
            'query'       => $row['keys'][0] ?? '',
            'clicks'      => $row['clicks'],
            'impressions' => $row['impressions'],
            'ctr'         => $row['ctr'],
            'position'    => $row['position'],
        ], $topQueriesData['rows'] ?? []);

        // 3) Upsert the daily snapshot (update if already exists for this date)
        GscDailySnapshot::updateOrCreate(
            [
                'gsc_connection_id' => $connection->id,
                'date'              => $yesterday,
            ],
            [
                'clicks'       => $totalClicks,
                'impressions'  => $totalImpressions,
                'ctr'          => $avgCtr,
                'avg_position' => $avgPosition,
                'top_queries'  => $topQueries,
            ]
        );

        // 4) Update last_sync_at on the connection
        $connection->update(['last_sync_at' => now()]);

        Log::info('SyncSearchConsoleData: Synced connection ' . $connection->id, [
            'date'        => $yesterday,
            'clicks'      => $totalClicks,
            'impressions' => $totalImpressions,
            'queries'     => count($topQueries),
        ]);
    }
}
