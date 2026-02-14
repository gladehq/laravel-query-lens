<?php

declare(strict_types=1);

namespace GladeHQ\QueryLens\Filament\Pages;

use GladeHQ\QueryLens\Filament\QueryLensDataService;

/**
 * Dashboard page configuration for the Filament plugin.
 *
 * When Filament is installed, extend Filament\Pages\Page and use this class
 * as a reference for the data-fetching logic. Without Filament, this class
 * serves as a standalone data assembler for the dashboard view.
 */
class QueryLensDashboard
{
    public static string $navigationIcon = 'heroicon-o-magnifying-glass-circle';
    public static string $navigationGroup = 'Query Lens';
    public static string $title = 'Query Dashboard';
    public static string $slug = 'query-lens';
    public static int $navigationSort = 1;
    public static string $view = 'query-lens::filament.dashboard';

    public function getViewData(QueryLensDataService $dataService, array $filters = []): array
    {
        $stats = $dataService->getStatsForWidget();

        $queryFilters = array_filter([
            'type' => $filters['type'] ?? null,
            'is_slow' => isset($filters['is_slow']) ? (bool) $filters['is_slow'] : null,
            'sql_like' => $filters['sql_like'] ?? null,
            'page' => $filters['page'] ?? 1,
            'per_page' => $filters['per_page'] ?? 15,
        ], fn($v) => $v !== null);

        $result = $dataService->getRecentQueries($queryFilters);

        return [
            'stats' => $stats,
            'recentQueries' => $result['data'] ?? [],
            'total' => $result['total'] ?? 0,
            'page' => $result['page'] ?? 1,
        ];
    }

    public static function getNavigationLabel(): string
    {
        return 'Query Dashboard';
    }
}
