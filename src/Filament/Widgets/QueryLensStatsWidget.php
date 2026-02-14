<?php

declare(strict_types=1);

namespace GladeHQ\QueryLens\Filament\Widgets;

use GladeHQ\QueryLens\Filament\QueryLensDataService;

/**
 * Stats widget configuration for the Filament plugin.
 *
 * Assembles stats data for display. When Filament is installed, this data
 * can be used with Filament\Widgets\StatsOverviewWidget\Stat cards.
 * Without Filament, it provides structured data for any dashboard.
 */
class QueryLensStatsWidget
{
    protected static int $sort = 10;
    protected static string $pollingInterval = '30s';

    public function getStats(QueryLensDataService $dataService): array
    {
        $stats = $dataService->getStatsForWidget();

        return [
            [
                'label' => 'Total Queries (24h)',
                'value' => number_format($stats['total_queries']),
                'change' => $this->formatChange($stats['query_change']),
                'change_direction' => $stats['query_change']['direction'] ?? 'neutral',
                'color' => $this->getChangeColor($stats['query_change']),
            ],
            [
                'label' => 'Slow Queries',
                'value' => number_format($stats['slow_queries']),
                'change' => $this->formatChange($stats['slow_change']),
                'change_direction' => $stats['slow_change']['direction'] ?? 'neutral',
                'color' => $this->getSlowColor($stats['slow_change']),
            ],
            [
                'label' => 'Avg Response Time',
                'value' => $stats['avg_time'] . 'ms',
                'change' => $this->formatChange($stats['avg_time_change']),
                'change_direction' => $stats['avg_time_change']['direction'] ?? 'neutral',
                'color' => $this->getTimeColor($stats['avg_time_change']),
            ],
            [
                'label' => 'P95 Latency',
                'value' => $stats['p95_time'] . 'ms',
                'change' => 'N/A',
                'change_direction' => 'neutral',
                'color' => 'gray',
            ],
        ];
    }

    public function formatChange(array $change): string
    {
        $value = $change['value'] ?? 0;
        $direction = $change['direction'] ?? 'neutral';

        if ($direction === 'neutral' || $value == 0) {
            return 'No change';
        }

        $arrow = $direction === 'up' ? '+' : '-';
        return "{$arrow}{$value}% vs previous period";
    }

    public function getChangeColor(array $change): string
    {
        return match ($change['direction'] ?? 'neutral') {
            'up' => 'success',
            'down' => 'danger',
            default => 'gray',
        };
    }

    public function getSlowColor(array $change): string
    {
        return match ($change['direction'] ?? 'neutral') {
            'up' => 'danger',
            'down' => 'success',
            default => 'gray',
        };
    }

    public function getTimeColor(array $change): string
    {
        return match ($change['direction'] ?? 'neutral') {
            'up' => 'danger',
            'down' => 'success',
            default => 'gray',
        };
    }

    public static function getSort(): int
    {
        return static::$sort;
    }

    public static function getPollingInterval(): string
    {
        return static::$pollingInterval;
    }
}
