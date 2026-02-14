<?php

declare(strict_types=1);

namespace GladeHQ\QueryLens\Filament;

/**
 * Filament Panel Plugin for QueryLens.
 *
 * When Filament is installed, this class is used via:
 *   ->plugin(QueryLensPlugin::make())
 *
 * The register() and boot() methods match the Filament\Contracts\Plugin interface
 * without formally implementing it, so the class can be loaded even when
 * Filament is not present. Filament's plugin resolution uses duck typing
 * on the getId/register/boot methods.
 */
class QueryLensPlugin
{
    protected bool $enableDashboard = true;
    protected bool $enableAlerts = true;
    protected bool $enableTrends = true;

    public static function make(): static
    {
        return new static();
    }

    public function getId(): string
    {
        return 'query-lens';
    }

    public function dashboard(bool $enabled = true): static
    {
        $this->enableDashboard = $enabled;
        return $this;
    }

    public function alerts(bool $enabled = true): static
    {
        $this->enableAlerts = $enabled;
        return $this;
    }

    public function trends(bool $enabled = true): static
    {
        $this->enableTrends = $enabled;
        return $this;
    }

    public function isDashboardEnabled(): bool
    {
        return $this->enableDashboard;
    }

    public function isAlertsEnabled(): bool
    {
        return $this->enableAlerts;
    }

    public function isTrendsEnabled(): bool
    {
        return $this->enableTrends;
    }

    /**
     * Register plugin pages and widgets with the Filament panel.
     *
     * @param mixed $panel Filament\Panel instance
     */
    public function register($panel): void
    {
        $pages = [];
        $widgets = [];

        if ($this->enableDashboard) {
            $pages[] = Pages\QueryLensDashboard::class;
        }

        if ($this->enableAlerts) {
            $pages[] = Pages\QueryLensAlerts::class;
        }

        if ($this->enableTrends) {
            $pages[] = Pages\QueryLensTrends::class;
        }

        $widgets[] = Widgets\QueryLensStatsWidget::class;

        $panel->pages($pages)->widgets($widgets);
    }

    /**
     * Boot-time operations for the plugin.
     *
     * @param mixed $panel Filament\Panel instance
     */
    public function boot($panel): void
    {
        // No boot-time operations needed
    }

    public static function isFilamentInstalled(): bool
    {
        return interface_exists(\Filament\Contracts\Plugin::class);
    }
}
