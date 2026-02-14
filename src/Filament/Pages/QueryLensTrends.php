<?php

declare(strict_types=1);

namespace GladeHQ\QueryLens\Filament\Pages;

use GladeHQ\QueryLens\Filament\QueryLensDataService;

/**
 * Trends page configuration for the Filament plugin.
 *
 * Provides trend data assembly without depending on Filament base classes,
 * making it safe to load regardless of whether Filament is installed.
 */
class QueryLensTrends
{
    public static string $navigationIcon = 'heroicon-o-chart-bar';
    public static string $navigationGroup = 'Query Lens';
    public static string $title = 'Query Trends';
    public static string $slug = 'query-lens/trends';
    public static int $navigationSort = 2;
    public static string $view = 'query-lens::filament.trends';

    public function getViewData(
        QueryLensDataService $dataService,
        string $granularity = 'hour',
        string $topQueryType = 'slowest',
    ): array {
        return [
            'trendsData' => $dataService->getTrendsData($granularity),
            'topQueries' => $dataService->getTopQueries($topQueryType),
        ];
    }

    public static function getNavigationLabel(): string
    {
        return 'Query Trends';
    }
}
