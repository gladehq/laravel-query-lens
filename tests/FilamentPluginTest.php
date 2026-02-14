<?php

namespace GladeHQ\QueryLens\Tests;

use GladeHQ\QueryLens\Filament\Pages\QueryLensAlerts;
use GladeHQ\QueryLens\Filament\Pages\QueryLensDashboard;
use GladeHQ\QueryLens\Filament\Pages\QueryLensTrends;
use GladeHQ\QueryLens\Filament\QueryLensDataService;
use GladeHQ\QueryLens\Filament\QueryLensPlugin;
use GladeHQ\QueryLens\Filament\Widgets\QueryLensStatsWidget;
use GladeHQ\QueryLens\Services\AggregationService;
use GladeHQ\QueryLens\Tests\Fakes\InMemoryQueryStorage;
use Orchestra\Testbench\TestCase;

class FilamentPluginTest extends TestCase
{
    protected function getPackageProviders($app): array
    {
        return [\GladeHQ\QueryLens\QueryLensServiceProvider::class];
    }

    protected function defineEnvironment($app): void
    {
        // Use cache driver to avoid database dependency in Filament plugin tests
        $app['config']->set('query-lens.storage.driver', 'cache');
    }

    protected function makeDataService(?InMemoryQueryStorage $storage = null): QueryLensDataService
    {
        $storage = $storage ?? new InMemoryQueryStorage();
        $aggregation = new AggregationService();
        $aggregation->setStorage($storage);

        return new QueryLensDataService($storage, $aggregation);
    }

    // ---------------------------------------------------------------
    // Plugin configuration
    // ---------------------------------------------------------------

    public function test_plugin_has_correct_id(): void
    {
        $plugin = QueryLensPlugin::make();
        $this->assertSame('query-lens', $plugin->getId());
    }

    public function test_plugin_defaults_all_features_enabled(): void
    {
        $plugin = QueryLensPlugin::make();
        $this->assertTrue($plugin->isDashboardEnabled());
        $this->assertTrue($plugin->isAlertsEnabled());
        $this->assertTrue($plugin->isTrendsEnabled());
    }

    public function test_plugin_can_disable_dashboard(): void
    {
        $plugin = QueryLensPlugin::make()->dashboard(false);
        $this->assertFalse($plugin->isDashboardEnabled());
        $this->assertTrue($plugin->isAlertsEnabled());
    }

    public function test_plugin_can_disable_alerts(): void
    {
        $plugin = QueryLensPlugin::make()->alerts(false);
        $this->assertTrue($plugin->isDashboardEnabled());
        $this->assertFalse($plugin->isAlertsEnabled());
    }

    public function test_plugin_can_disable_trends(): void
    {
        $plugin = QueryLensPlugin::make()->trends(false);
        $this->assertTrue($plugin->isDashboardEnabled());
        $this->assertFalse($plugin->isTrendsEnabled());
    }

    public function test_plugin_fluent_chaining(): void
    {
        $plugin = QueryLensPlugin::make()
            ->dashboard(false)
            ->alerts(false)
            ->trends(false);

        $this->assertFalse($plugin->isDashboardEnabled());
        $this->assertFalse($plugin->isAlertsEnabled());
        $this->assertFalse($plugin->isTrendsEnabled());
    }

    public function test_make_returns_static_instance(): void
    {
        $plugin = QueryLensPlugin::make();
        $this->assertInstanceOf(QueryLensPlugin::class, $plugin);
    }

    // ---------------------------------------------------------------
    // Filament detection
    // ---------------------------------------------------------------

    public function test_is_filament_installed_returns_bool(): void
    {
        // Filament is not installed in the test environment
        $this->assertFalse(QueryLensPlugin::isFilamentInstalled());
    }

    // ---------------------------------------------------------------
    // Package works without Filament
    // ---------------------------------------------------------------

    public function test_package_boots_without_filament(): void
    {
        // The service provider should boot without errors when Filament is absent
        $this->assertTrue(true);
    }

    public function test_service_provider_registers_core_services_without_filament(): void
    {
        $this->assertNotNull(app(\GladeHQ\QueryLens\Contracts\QueryStorage::class));
        $this->assertNotNull(app(\GladeHQ\QueryLens\Services\AlertService::class));
        $this->assertNotNull(app(\GladeHQ\QueryLens\Services\AggregationService::class));
    }

    public function test_data_service_registered_in_container(): void
    {
        $this->assertNotNull(app(QueryLensDataService::class));
    }

    public function test_routes_register_without_filament(): void
    {
        $routes = app('router')->getRoutes();
        $routeNames = collect($routes->getRoutes())->map(fn($r) => $r->getName())->filter()->toArray();

        $this->assertContains('query-lens.dashboard', $routeNames);
        $this->assertContains('query-lens.api.queries', $routeNames);
    }

    public function test_analyzer_works_independently_of_filament(): void
    {
        $analyzer = new \GladeHQ\QueryLens\QueryAnalyzer(
            ['performance_thresholds' => ['fast' => 0.1, 'moderate' => 0.5, 'slow' => 1.0]],
            new InMemoryQueryStorage()
        );

        $analyzer->recordQuery('SELECT * FROM users', [], 0.05);
        $this->assertCount(1, $analyzer->getQueries());
    }

    // ---------------------------------------------------------------
    // QueryLensDataService
    // ---------------------------------------------------------------

    public function test_data_service_returns_stats_for_widget(): void
    {
        $dataService = $this->makeDataService();
        $stats = $dataService->getStatsForWidget();

        $this->assertArrayHasKey('total_queries', $stats);
        $this->assertArrayHasKey('slow_queries', $stats);
        $this->assertArrayHasKey('avg_time', $stats);
        $this->assertArrayHasKey('p95_time', $stats);
        $this->assertArrayHasKey('query_change', $stats);
        $this->assertArrayHasKey('slow_change', $stats);
        $this->assertArrayHasKey('avg_time_change', $stats);
    }

    public function test_data_service_returns_empty_stats_when_no_data(): void
    {
        $dataService = $this->makeDataService();
        $stats = $dataService->getStatsForWidget();

        $this->assertSame(0, $stats['total_queries']);
        $this->assertSame(0, $stats['slow_queries']);
        $this->assertSame(0.0, $stats['avg_time']);
    }

    public function test_data_service_recent_queries_returns_paginated_result(): void
    {
        $dataService = $this->makeDataService();
        $result = $dataService->getRecentQueries(['per_page' => 10]);

        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('total', $result);
        $this->assertArrayHasKey('page', $result);
        $this->assertArrayHasKey('per_page', $result);
    }

    public function test_data_service_recent_queries_with_type_filter(): void
    {
        $storage = new InMemoryQueryStorage();
        $storage->store([
            'id' => 'q1', 'sql' => 'SELECT * FROM users', 'time' => 0.05,
            'timestamp' => microtime(true),
            'analysis' => ['type' => 'SELECT', 'performance' => ['is_slow' => false], 'issues' => []],
        ]);
        $storage->store([
            'id' => 'q2', 'sql' => 'INSERT INTO users (name) VALUES ("test")', 'time' => 0.03,
            'timestamp' => microtime(true),
            'analysis' => ['type' => 'INSERT', 'performance' => ['is_slow' => false], 'issues' => []],
        ]);

        $dataService = $this->makeDataService($storage);
        $result = $dataService->getRecentQueries(['type' => 'SELECT']);
        $this->assertSame(1, $result['total']);
    }

    public function test_data_service_trends_returns_expected_structure(): void
    {
        $dataService = $this->makeDataService();
        $trends = $dataService->getTrendsData('hour');

        $this->assertArrayHasKey('labels', $trends);
        $this->assertArrayHasKey('latency', $trends);
        $this->assertArrayHasKey('throughput', $trends);
        $this->assertArrayHasKey('p50', $trends);
        $this->assertArrayHasKey('p95', $trends);
        $this->assertArrayHasKey('p99', $trends);
    }

    public function test_data_service_top_queries_returns_array(): void
    {
        $dataService = $this->makeDataService();
        $topQueries = $dataService->getTopQueries('slowest', 'day', 5);

        $this->assertIsArray($topQueries);
    }

    public function test_data_service_top_queries_with_data(): void
    {
        $storage = new InMemoryQueryStorage();
        $storage->store([
            'id' => 'q1', 'sql' => 'SELECT * FROM users', 'time' => 0.5,
            'timestamp' => microtime(true),
            'analysis' => ['type' => 'SELECT', 'performance' => ['is_slow' => false], 'issues' => []],
        ]);
        $storage->store([
            'id' => 'q2', 'sql' => 'SELECT * FROM users', 'time' => 0.8,
            'timestamp' => microtime(true),
            'analysis' => ['type' => 'SELECT', 'performance' => ['is_slow' => false], 'issues' => []],
        ]);

        $dataService = $this->makeDataService($storage);
        $topQueries = $dataService->getTopQueries('slowest', 'day', 5);

        $this->assertNotEmpty($topQueries);
        $this->assertArrayHasKey('sql_sample', $topQueries[0]);
    }

    public function test_data_service_trends_with_data(): void
    {
        $storage = new InMemoryQueryStorage();
        for ($i = 0; $i < 5; $i++) {
            $storage->store([
                'id' => "q{$i}", 'sql' => 'SELECT * FROM users',
                'time' => 0.05 + ($i * 0.01),
                'timestamp' => microtime(true) - ($i * 60),
                'analysis' => ['type' => 'SELECT', 'performance' => ['is_slow' => false], 'issues' => []],
            ]);
        }

        $dataService = $this->makeDataService($storage);
        $trends = $dataService->getTrendsData('hour', now()->subHours(2), now());

        $this->assertNotEmpty($trends['labels']);
        $this->assertNotEmpty($trends['throughput']);
    }

    public function test_data_service_stats_include_change_directions(): void
    {
        $dataService = $this->makeDataService();
        $stats = $dataService->getStatsForWidget();

        $this->assertArrayHasKey('direction', $stats['query_change']);
        $this->assertContains($stats['query_change']['direction'], ['up', 'down', 'neutral']);
    }

    public function test_data_service_handles_empty_search_results(): void
    {
        $dataService = $this->makeDataService();
        $result = $dataService->getRecentQueries(['type' => 'NONEXISTENT']);

        $this->assertSame(0, $result['total']);
        $this->assertEmpty($result['data']);
    }

    // ---------------------------------------------------------------
    // Dashboard page data assembly
    // ---------------------------------------------------------------

    public function test_dashboard_page_view_data(): void
    {
        $page = new QueryLensDashboard();
        $dataService = $this->makeDataService();

        $viewData = $page->getViewData($dataService);

        $this->assertArrayHasKey('stats', $viewData);
        $this->assertArrayHasKey('recentQueries', $viewData);
        $this->assertArrayHasKey('total', $viewData);
        $this->assertArrayHasKey('page', $viewData);
    }

    public function test_dashboard_page_view_data_with_filters(): void
    {
        $storage = new InMemoryQueryStorage();
        $storage->store([
            'id' => 'q1', 'sql' => 'SELECT * FROM users', 'time' => 0.05,
            'timestamp' => microtime(true),
            'analysis' => ['type' => 'SELECT', 'performance' => ['is_slow' => false], 'issues' => []],
        ]);

        $page = new QueryLensDashboard();
        $dataService = $this->makeDataService($storage);

        $viewData = $page->getViewData($dataService, ['type' => 'SELECT']);
        $this->assertSame(1, $viewData['total']);
    }

    public function test_dashboard_page_navigation_label(): void
    {
        $this->assertSame('Query Dashboard', QueryLensDashboard::getNavigationLabel());
    }

    public function test_dashboard_page_static_properties(): void
    {
        $this->assertSame('query-lens', QueryLensDashboard::$slug);
        $this->assertSame('Query Dashboard', QueryLensDashboard::$title);
    }

    // ---------------------------------------------------------------
    // Trends page data assembly
    // ---------------------------------------------------------------

    public function test_trends_page_view_data(): void
    {
        $page = new QueryLensTrends();
        $dataService = $this->makeDataService();

        $viewData = $page->getViewData($dataService);

        $this->assertArrayHasKey('trendsData', $viewData);
        $this->assertArrayHasKey('topQueries', $viewData);
    }

    public function test_trends_page_view_data_with_granularity(): void
    {
        $page = new QueryLensTrends();
        $dataService = $this->makeDataService();

        $viewData = $page->getViewData($dataService, 'day');

        $this->assertArrayHasKey('trendsData', $viewData);
    }

    public function test_trends_page_navigation_label(): void
    {
        $this->assertSame('Query Trends', QueryLensTrends::getNavigationLabel());
    }

    // ---------------------------------------------------------------
    // Alerts page data assembly
    // ---------------------------------------------------------------

    public function test_alerts_page_navigation_label(): void
    {
        $this->assertSame('Query Alerts', QueryLensAlerts::getNavigationLabel());
    }

    public function test_alerts_page_static_properties(): void
    {
        $this->assertSame('query-lens/alerts', QueryLensAlerts::$slug);
        $this->assertSame('Query Alerts', QueryLensAlerts::$title);
    }

    // ---------------------------------------------------------------
    // Stats widget
    // ---------------------------------------------------------------

    public function test_widget_returns_four_stat_cards(): void
    {
        $widget = new QueryLensStatsWidget();
        $dataService = $this->makeDataService();

        $stats = $widget->getStats($dataService);

        $this->assertCount(4, $stats);
        $this->assertSame('Total Queries (24h)', $stats[0]['label']);
        $this->assertSame('Slow Queries', $stats[1]['label']);
        $this->assertSame('Avg Response Time', $stats[2]['label']);
        $this->assertSame('P95 Latency', $stats[3]['label']);
    }

    public function test_widget_stat_cards_have_expected_keys(): void
    {
        $widget = new QueryLensStatsWidget();
        $dataService = $this->makeDataService();

        $stats = $widget->getStats($dataService);

        foreach ($stats as $stat) {
            $this->assertArrayHasKey('label', $stat);
            $this->assertArrayHasKey('value', $stat);
            $this->assertArrayHasKey('change', $stat);
            $this->assertArrayHasKey('change_direction', $stat);
            $this->assertArrayHasKey('color', $stat);
        }
    }

    public function test_widget_format_change_neutral(): void
    {
        $widget = new QueryLensStatsWidget();
        $this->assertSame('No change', $widget->formatChange(['value' => 0, 'direction' => 'neutral']));
    }

    public function test_widget_format_change_up(): void
    {
        $widget = new QueryLensStatsWidget();
        $this->assertSame('+25.5% vs previous period', $widget->formatChange(['value' => 25.5, 'direction' => 'up']));
    }

    public function test_widget_format_change_down(): void
    {
        $widget = new QueryLensStatsWidget();
        $this->assertSame('-10% vs previous period', $widget->formatChange(['value' => 10, 'direction' => 'down']));
    }

    public function test_widget_change_color_logic(): void
    {
        $widget = new QueryLensStatsWidget();

        $this->assertSame('success', $widget->getChangeColor(['direction' => 'up']));
        $this->assertSame('danger', $widget->getChangeColor(['direction' => 'down']));
        $this->assertSame('gray', $widget->getChangeColor(['direction' => 'neutral']));
    }

    public function test_widget_slow_color_inverted(): void
    {
        $widget = new QueryLensStatsWidget();

        // For slow queries: up = bad (danger), down = good (success)
        $this->assertSame('danger', $widget->getSlowColor(['direction' => 'up']));
        $this->assertSame('success', $widget->getSlowColor(['direction' => 'down']));
    }

    public function test_widget_time_color_inverted(): void
    {
        $widget = new QueryLensStatsWidget();

        // For time: up = bad (danger), down = good (success)
        $this->assertSame('danger', $widget->getTimeColor(['direction' => 'up']));
        $this->assertSame('success', $widget->getTimeColor(['direction' => 'down']));
    }

    public function test_widget_sort_and_polling(): void
    {
        $this->assertSame(10, QueryLensStatsWidget::getSort());
        $this->assertSame('30s', QueryLensStatsWidget::getPollingInterval());
    }

    // ---------------------------------------------------------------
    // Plugin register method (mock panel)
    // ---------------------------------------------------------------

    public function test_plugin_register_calls_panel_with_pages(): void
    {
        $plugin = QueryLensPlugin::make();

        $registeredPages = [];
        $registeredWidgets = [];

        // Simple mock panel object
        $panel = new class($registeredPages, $registeredWidgets) {
            public array $pages;
            public array $widgets;

            public function __construct(array &$pages, array &$widgets)
            {
                $this->pages = &$pages;
                $this->widgets = &$widgets;
            }

            public function pages(array $pages): self
            {
                $this->pages = $pages;
                return $this;
            }

            public function widgets(array $widgets): self
            {
                $this->widgets = $widgets;
                return $this;
            }
        };

        $plugin->register($panel);

        $this->assertContains(QueryLensDashboard::class, $panel->pages);
        $this->assertContains(QueryLensAlerts::class, $panel->pages);
        $this->assertContains(QueryLensTrends::class, $panel->pages);
        $this->assertContains(QueryLensStatsWidget::class, $panel->widgets);
    }

    public function test_plugin_register_respects_disabled_pages(): void
    {
        $plugin = QueryLensPlugin::make()
            ->dashboard(false)
            ->alerts(false);

        $panel = new class {
            public array $pages = [];
            public array $widgets = [];

            public function pages(array $pages): self { $this->pages = $pages; return $this; }
            public function widgets(array $widgets): self { $this->widgets = $widgets; return $this; }
        };

        $plugin->register($panel);

        $this->assertNotContains(QueryLensDashboard::class, $panel->pages);
        $this->assertNotContains(QueryLensAlerts::class, $panel->pages);
        $this->assertContains(QueryLensTrends::class, $panel->pages);
    }

    public function test_plugin_boot_does_not_throw(): void
    {
        $plugin = QueryLensPlugin::make();
        $panel = new class {
            public function pages(array $p): self { return $this; }
            public function widgets(array $w): self { return $this; }
        };

        // boot should be a no-op and not throw
        $plugin->boot($panel);
        $this->assertTrue(true);
    }
}
