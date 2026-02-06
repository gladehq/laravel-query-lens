<?php

namespace Laravel\QueryAnalyzer;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Laravel\QueryAnalyzer\Commands\AnalyzeQueriesCommand;
use Laravel\QueryAnalyzer\Http\Controllers\QueryAnalyzerController;
use Laravel\QueryAnalyzer\Http\Middleware\QueryAnalyzerMiddleware;
use Laravel\QueryAnalyzer\Listeners\QueryListener;

class QueryAnalyzerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/query-analyzer.php',
            'query-analyzer'
        );

        $this->app->bind(\Laravel\QueryAnalyzer\Contracts\QueryStorage::class, function ($app) {
            return new \Laravel\QueryAnalyzer\Storage\CacheQueryStorage(
                config('query-analyzer.store')
            );
        });

        $this->app->singleton(QueryAnalyzer::class, function ($app) {
            $analyzer = new QueryAnalyzer(
                $app['config']['query-analyzer'],
                $app->make(\Laravel\QueryAnalyzer\Contracts\QueryStorage::class)
            );

            // Initialize Request ID immediately for HTTP requests to catch early queries
            // (e.g., Service Provider boot queries) that run before Middleware.
            if (!$app->runningInConsole()) {
                $analyzer->setRequestId((string) \Illuminate\Support\Str::orderedUuid());
            }

            return $analyzer;
        });

        $this->app->singleton(QueryListener::class);
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'query-analyzer');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/query-analyzer.php' => config_path('query-analyzer.php'),
            ], 'query-analyzer-config');

            // Views are explicitly NOT published to ensure updates are always reflected immediately.
            // users cannot override the dashboard view.

            $this->commands([
                AnalyzeQueriesCommand::class,
            ]);
        }

        $this->registerRoutes();

        if (config('query-analyzer.enabled', false)) {
            $this->app->make(QueryListener::class)->register();
            
            // Register the middleware to track Request IDs
            $router = $this->app['router'];
            if ($router->hasMiddlewareGroup('web')) {
                $router->pushMiddlewareToGroup('web', \Laravel\QueryAnalyzer\Http\Middleware\AnalyzeQueryMiddleware::class);
            }
            if ($router->hasMiddlewareGroup('api')) {
                $router->pushMiddlewareToGroup('api', \Laravel\QueryAnalyzer\Http\Middleware\AnalyzeQueryMiddleware::class);
            }
        }
    }

    protected function registerRoutes(): void
    {
        if (!config('query-analyzer.web_ui.enabled', true)) {
            return;
        }

        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
    }
}