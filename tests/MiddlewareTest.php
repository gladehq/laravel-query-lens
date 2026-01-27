<?php

namespace Laravel\QueryAnalyzer\Tests;

use Orchestra\Testbench\TestCase;
use Laravel\QueryAnalyzer\QueryAnalyzer;
use Laravel\QueryAnalyzer\Http\Middleware\AnalyzeQueryMiddleware;
use Illuminate\Http\Request;
use Mockery;

class MiddlewareTest extends TestCase
{
    public function test_middleware_sets_request_id()
    {
        $analyzerDescriptor = Mockery::mock(QueryAnalyzer::class);
        $analyzerDescriptor->shouldReceive('setRequestId')
            ->once()
            ->with(Mockery::type('string'))
            ->andReturnNull();

        $middleware = new AnalyzeQueryMiddleware($analyzerDescriptor);
        
        $request = Request::create('/test', 'GET');
        
        $response = $middleware->handle($request, function ($req) {
            return 'response';
        });

        $this->assertEquals('response', $response);
    }
}
