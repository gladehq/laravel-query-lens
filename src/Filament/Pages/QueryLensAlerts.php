<?php

declare(strict_types=1);

namespace GladeHQ\QueryLens\Filament\Pages;

use GladeHQ\QueryLens\Models\Alert;

/**
 * Alerts page configuration for the Filament plugin.
 *
 * Provides alert data assembly without depending on Filament base classes,
 * making it safe to load regardless of whether Filament is installed.
 */
class QueryLensAlerts
{
    public static string $navigationIcon = 'heroicon-o-bell-alert';
    public static string $navigationGroup = 'Query Lens';
    public static string $title = 'Query Alerts';
    public static string $slug = 'query-lens/alerts';
    public static int $navigationSort = 3;
    public static string $view = 'query-lens::filament.alerts';

    public function getViewData(): array
    {
        return [
            'alerts' => $this->loadAlerts(),
            'availableTypes' => Alert::getAvailableTypes(),
            'availableChannels' => Alert::getAvailableChannels(),
        ];
    }

    public function loadAlerts(): array
    {
        try {
            return Alert::orderByDesc('created_at')->get()->toArray();
        } catch (\Exception) {
            return [];
        }
    }

    public function toggleAlert(int $alertId): bool
    {
        $alert = Alert::find($alertId);
        if (!$alert) {
            return false;
        }

        $alert->update(['enabled' => !$alert->enabled]);
        return true;
    }

    public static function getNavigationLabel(): string
    {
        return 'Query Alerts';
    }
}
