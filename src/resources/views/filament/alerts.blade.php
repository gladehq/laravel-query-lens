<x-filament-panels::page>
    <x-filament::section heading="Configured Alerts">
        @if(empty($alerts))
            <p class="text-gray-500 text-sm">No alerts configured yet.</p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b">
                            <th class="text-left p-2">Name</th>
                            <th class="text-left p-2">Type</th>
                            <th class="text-center p-2">Enabled</th>
                            <th class="text-right p-2">Triggers</th>
                            <th class="text-left p-2">Last Triggered</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($alerts as $alert)
                            <tr class="border-b hover:bg-gray-50 dark:hover:bg-gray-800">
                                <td class="p-2">{{ $alert['name'] ?? 'Unnamed' }}</td>
                                <td class="p-2">{{ $alert['type'] ?? 'N/A' }}</td>
                                <td class="p-2 text-center">
                                    <span class="px-2 py-1 rounded text-xs {{ ($alert['enabled'] ?? false) ? 'bg-success-100 text-success-700' : 'bg-gray-100 text-gray-500' }}">
                                        {{ ($alert['enabled'] ?? false) ? 'Active' : 'Disabled' }}
                                    </span>
                                </td>
                                <td class="p-2 text-right">{{ $alert['trigger_count'] ?? 0 }}</td>
                                <td class="p-2">{{ $alert['last_triggered_at'] ?? 'Never' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </x-filament::section>
</x-filament-panels::page>
