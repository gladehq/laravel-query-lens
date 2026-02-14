<x-filament-panels::page>
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <x-filament::section>
            <div class="text-sm text-gray-500">Total Queries (24h)</div>
            <div class="text-2xl font-bold">{{ number_format($stats['total_queries'] ?? 0) }}</div>
        </x-filament::section>

        <x-filament::section>
            <div class="text-sm text-gray-500">Slow Queries</div>
            <div class="text-2xl font-bold text-danger-600">{{ number_format($stats['slow_queries'] ?? 0) }}</div>
        </x-filament::section>

        <x-filament::section>
            <div class="text-sm text-gray-500">Avg Response Time</div>
            <div class="text-2xl font-bold">{{ $stats['avg_time'] ?? 0 }}ms</div>
        </x-filament::section>

        <x-filament::section>
            <div class="text-sm text-gray-500">P95 Latency</div>
            <div class="text-2xl font-bold">{{ $stats['p95_time'] ?? 0 }}ms</div>
        </x-filament::section>
    </div>

    <x-filament::section heading="Recent Queries">
        @if(empty($recentQueries))
            <p class="text-gray-500 text-sm">No queries recorded yet.</p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b">
                            <th class="text-left p-2">SQL</th>
                            <th class="text-left p-2">Type</th>
                            <th class="text-right p-2">Time (ms)</th>
                            <th class="text-left p-2">Rating</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentQueries as $query)
                            <tr class="border-b hover:bg-gray-50 dark:hover:bg-gray-800">
                                <td class="p-2 max-w-md truncate">{{ \Illuminate\Support\Str::limit($query['sql'] ?? '', 80) }}</td>
                                <td class="p-2">{{ $query['analysis']['type'] ?? 'N/A' }}</td>
                                <td class="p-2 text-right">{{ round(($query['time'] ?? 0) * 1000, 2) }}</td>
                                <td class="p-2">
                                    <span class="px-2 py-1 rounded text-xs {{ ($query['analysis']['performance']['is_slow'] ?? false) ? 'bg-danger-100 text-danger-700' : 'bg-success-100 text-success-700' }}">
                                        {{ $query['analysis']['performance']['rating'] ?? 'fast' }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </x-filament::section>
</x-filament-panels::page>
