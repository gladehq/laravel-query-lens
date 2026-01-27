<!DOCTYPE html>
<html lang="en" class="antialiased">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laravel Query Analyzer</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f3f4f6; }
        .font-mono { font-family: 'JetBrains Mono', monospace; }
        
        /* Markdown Content Styles */
        .markdown-content ul { list-style-type: disc; padding-left: 1.5em; margin-bottom: 0.5em; }
        .markdown-content ol { list-style-type: decimal; padding-left: 1.5em; margin-bottom: 0.5em; }
        .markdown-content p { margin-bottom: 0.75em; }
        .markdown-content strong { font-weight: 600; color: #4f46e5; }
        .markdown-content code { background-color: #e2e8f0; padding: 0.1em 0.3em; border-radius: 0.2em; font-family: 'JetBrains Mono', monospace; font-size: 0.9em; color: #be185d; }
        .markdown-content pre { background-color: #1e293b; color: #e2e8f0; padding: 1em; border-radius: 0.5em; overflow-x: auto; margin-bottom: 1em; }
        .markdown-content pre code { background-color: transparent; color: inherit; padding: 0; }
        .markdown-content h1 { font-size: 1.1rem; font-weight: 700; color: #1e293b; margin-top: 1em; margin-bottom: 0.5em; border-bottom: 1px solid #e2e8f0; padding-bottom: 0.3em; }
        .markdown-content h2 { font-size: 1rem; font-weight: 600; color: #334155; margin-top: 1em; margin-bottom: 0.5em; }
        .markdown-content h3 { font-size: 0.9rem; font-weight: 600; color: #475569; margin-top: 0.75em; margin-bottom: 0.25em; }
        .markdown-content h4 { font-size: 0.85rem; font-weight: 600; color: #64748b; margin-top: 0.5em; margin-bottom: 0.25em; }
        
        /* Custom scrollbar */
        ::-webkit-scrollbar { width: 8px; height: 8px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

        .query-card { transition: all 0.2s ease; border-left: 4px solid transparent; }
        .query-card:hover { transform: translateY(-1px); box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06); }
        
        .type-select { border-left-color: #3b82f6; }
        .type-insert { border-left-color: #10b981; }
        .type-update { border-left-color: #f59e0b; }
        .type-insert { border-left-color: #10b981; }
        .type-update { border-left-color: #f59e0b; }
        .type-delete { border-left-color: #ef4444; }
        .type-cache { border-left-color: #8b5cf6; }


        .modal-enter { opacity: 0; transform: scale(0.95); }
        .modal-enter-active { transition: all 0.2s ease-out; }
        .modal-leave-active { transition: all 0.15s ease-in; opacity: 0; transform: scale(0.95); }
    </style>
</head>
<body class="text-slate-800 h-screen flex flex-col overflow-hidden">
    
    <!-- Header -->
    <header class="bg-white border-b border-slate-200 z-10 shadow-sm flex-none">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-12 flex items-center justify-between">
            <div class="flex items-center gap-8">
                <div class="flex items-center gap-3">
                    <div class="bg-indigo-600 text-white p-1 rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2-2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                    </div>
                    <h1 class="text-xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-indigo-500 to-purple-600">Query Analyzer v3 (Final Rewrite)</h1>
                </div>

                <div class="hidden md:flex items-center gap-6 border-l border-slate-100 pl-8 h-8">
                    <div class="flex items-baseline gap-2">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Total</span>
                        <span class="text-sm font-bold text-slate-700 tabular-nums" id="total-queries">-</span>
                    </div>
                    <div class="flex items-baseline gap-2">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Slow</span>
                        <span class="text-sm font-bold text-red-500 tabular-nums" id="slow-queries">-</span>
                    </div>
                    <div class="flex items-baseline gap-2">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Avg</span>
                        <span class="text-sm font-bold text-indigo-600 tabular-nums" id="avg-time">-</span>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-4">
                 <div class="flex items-center gap-2 px-2 py-1 bg-slate-50 rounded-full border border-slate-100 text-[10px] font-bold text-slate-500 uppercase">
                    <span class="relative flex h-1.5 w-1.5">
                      <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                      <span class="relative inline-flex rounded-full h-1.5 w-1.5 bg-green-500"></span>
                    </span>
                    {{ $isEnabled ? 'Live' : 'Paused' }}
                </div>
            </div>
        </div>
    </header>

    <!-- Request Sidebar replaced Filter Sidebar -->
    <main class="flex-1 flex overflow-hidden">
    <aside class="w-72 bg-white border-r border-slate-200 flex flex-col flex-none z-0">
        <!-- Filters & Sorting (Primary) -->
        <div class="p-4 bg-slate-50/50">
            <h3 class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-3">Filters</h3>
            <div class="grid grid-cols-2 gap-2 mb-3">
                <select id="type-filter" onchange="updateFilters()" class="w-full text-xs border-slate-200 rounded shadow-sm focus:border-indigo-500 bg-white" title="Filter by Query Type">
                    <option value="all">All Types</option>
                    <option value="select">SELECT</option>
                    <option value="insert">INSERT</option>
                    <option value="update">UPDATE</option>
                    <option value="delete">DELETE</option>
                    <option value="cache">CACHE</option>
                </select>
                <select id="issue-filter" onchange="updateFilters()" class="w-full text-xs border-slate-200 rounded shadow-sm focus:border-indigo-500 bg-white" title="Filter by Issue">
                    <option value="all">All Issues</option>
                    <option value="n+1">N+1 Queries</option>
                    <option value="performance">Performance</option>
                    <option value="security">Security</option>
                </select>
            </div>

            <h3 class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-3">Sort</h3>
            <div class="grid grid-cols-2 gap-2 mb-4">
                <select id="sort-by" onchange="refreshQueries()" class="w-full text-xs border-slate-200 rounded shadow-sm focus:border-indigo-500 bg-white" title="Sort Criteria">
                    <option value="timestamp">By Sequence</option>
                    <option value="time">By Speed</option>
                    <option value="complexity">By Complexity</option>
                </select>
                <select id="sort-order" onchange="refreshQueries()" class="w-full text-xs border-slate-200 rounded shadow-sm focus:border-indigo-500 bg-white" title="Sort Order">
                    <option value="desc">Desc (High→Low)</option>
                    <option value="asc">Asc (Low→High)</option>
                </select>
            </div>
            
             <div class="space-y-2">
                 <button onclick="resetQueries()" class="w-full flex justify-center items-center gap-1.5 px-3 py-1.5 bg-white border border-slate-200 text-red-500 rounded text-[10px] font-bold hover:bg-red-50 hover:text-red-600 transition-colors">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                    Clear History
                </button>
             </div>
        </div>

        <!-- Divider -->
        <div class="h-1 bg-slate-100 shadow-inner"></div>

        <!-- Sidebar Header -->
        <div class="p-4 border-b border-slate-100 bg-slate-50/50">
            <h2 class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-0">Incoming Requests <span id="request-count" class="ml-1 text-xs font-normal text-slate-400"></span></h2>
        </div>

        <!-- Refresh Action -->
        <div class="p-2 bg-slate-50 border-b border-slate-100">
             <button onclick="refreshRequests()" class="w-full flex justify-center items-center gap-1.5 px-3 py-1.5 bg-white border border-slate-200 text-indigo-600 rounded text-[10px] font-bold hover:bg-indigo-50 hover:text-indigo-700 transition-colors">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                Refresh List
             </button>
        </div>

        <!-- Request List -->
        <div id="request-list" class="flex-1 overflow-y-auto">
            <div class="text-center py-8 text-slate-400 text-xs">Loading requests...</div>
        </div>


    </aside>

    <!-- Query List -->
    <div class="flex-1 flex flex-col bg-slate-50 min-w-0">
         <div class="px-6 py-4 border-b border-slate-200 bg-white flex justify-between items-center shadow-sm z-10">
            <div class="flex flex-col">
                <h2 class="text-sm font-bold text-slate-800 flex items-center gap-2">
                    Queries
                    <span id="query-count" class="bg-slate-100 text-slate-600 text-xs px-2 py-0.5 rounded-full border border-slate-200">0</span>
                </h2>
                <div id="current-request-info" class="text-xs text-slate-400 mt-0.5 font-mono">Select a request to view queries</div>
            </div>
            <div class="flex items-center gap-3">
                <div class="flex bg-slate-100 rounded-lg p-0.5">
                    <button onclick="switchView('list')" id="btn-view-list" class="px-3 py-1 text-xs font-medium rounded-md bg-white shadow-sm text-slate-700 transition-all">List</button>
                    <button onclick="switchView('timeline')" id="btn-view-timeline" class="px-3 py-1 text-xs font-medium rounded-md text-slate-500 hover:text-slate-700 transition-all">Timeline</button>
                 </div>
            </div>
        </div>
        
        <div id="query-list" class="flex-1 overflow-y-auto p-6 space-y-4">
            <div class="text-center py-20 text-slate-400">
                <svg class="w-12 h-12 mx-auto mb-3 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122"></path></svg>
                Select a request from the sidebar
            </div>
        </div>

        <div id="timeline-view" class="hidden flex-1 overflow-y-auto p-6">
            <div class="text-center py-20 text-slate-400">
                Select a request to view timeline
            </div>
        </div>
    </div>

    <!-- Right Detail Panel -->
    <div id="detail-panel" class="hidden w-[45%] bg-white border-l border-slate-200 flex flex-col overflow-hidden shadow-xl z-20 transition-all duration-300 relative">
        <div class="absolute top-0 right-0 p-4 z-10">
            <button onclick="closeDetails()" class="text-slate-400 hover:text-slate-600 bg-white rounded-full p-1 shadow-sm border border-slate-100">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        <div id="detail-content" class="flex-1 overflow-y-auto p-6">
            <div class="h-full flex flex-col items-center justify-center text-slate-400">
                <svg class="w-12 h-12 mb-3 text-slate-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                <span class="text-sm">Select a query to view details</span>
            </div>
        </div>
    </div>
</main>

<!-- Modal (Existing structure reused) -->
<!-- ... -->

<script>
    let currentRequestId = null;
    let currentView = 'list';
    let currentQueries = [];
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    document.addEventListener('DOMContentLoaded', () => {
        refreshRequests();
    });

    async function refreshRequests() {
        const container = document.getElementById('request-list');
        
        // Get Filters
        const typeFilter = document.getElementById('type-filter').value;
        const issueFilter = document.getElementById('issue-filter').value;
        const params = new URLSearchParams();
        if (typeFilter !== 'all') params.append('type', typeFilter);
        if (issueFilter !== 'all') params.append('issue_type', issueFilter);
        
        // Add cache buster
        params.append('_cb', Date.now());

        try {
            const res = await fetch(`/query-analyzer/api/requests?${params}`);
            const requests = await res.json();
            
            if (requests.length === 0) {
                 container.innerHTML = '<div class="text-center py-8 text-slate-400 text-xs italic">No requests found matching filters.</div>';
                 document.getElementById('request-count').textContent = '(0)';
                 return;
            }

            // Calculate stats
            const total = requests.length;
            const matches = requests.filter(r => r.query_count > 0).length;
            document.getElementById('request-count').textContent = `(${matches}/${total})`;

            container.innerHTML = requests.map(req => {
                const isSelected = req.request_id === currentRequestId;
                const methodClass = req.method === 'GET' ? 'text-blue-600 bg-blue-50' : req.method === 'POST' ? 'text-green-600 bg-green-50' : 'text-slate-600 bg-slate-50';
                
                return `
                <div onclick="selectRequest('${req.request_id}')" class="group px-4 py-3 border-b border-slate-50 cursor-pointer hover:bg-slate-50 transition-colors ${isSelected ? 'bg-indigo-50 border-l-4 border-l-indigo-500 pl-3' : 'border-l-4 border-l-transparent'}">
                    <div class="flex justify-between items-start mb-1">
                         <span class="text-[10px] font-bold px-1.5 py-0.5 rounded ${methodClass}">${req.method}</span>
                         <span class="text-[10px] text-slate-400">${new Date(req.timestamp * 1000).toLocaleTimeString()}</span>
                    </div>
                    <div class="text-xs font-mono text-slate-700 truncate mb-1" title="${req.path || '/'}">${req.path || '/'}</div>
                    <div class="flex items-center justify-between text-[10px]">
                        <span class="text-slate-500 font-medium">${req.query_count} queries <span class="text-slate-300 mx-1">|</span> <span class="text-[9px]">${(req.avg_time * 1000).toFixed(2)}ms avg</span></span>
                        ${req.slow_count > 0 ? `<span class="text-red-500 font-bold flex items-center gap-0.5"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg> ${req.slow_count} slow</span>` : ''}
                    </div>
                </div>`;
            }).join('');

            // If current selected ID is no longer in the filtered list, deselect? 
            // Or just check if still valid? For simplified UX, if list non-empty and nothing selected, select first.
            if (!currentRequestId && requests.length > 0) {
                selectRequest(requests[0].request_id);
            }

        } catch (e) {
            console.error(e);
            container.innerHTML = '<div class="text-center py-4 text-red-400 text-xs">Failed to load requests</div>';
        }
    }

    function selectRequest(id) {
        currentRequestId = id;
        refreshRequests(); // Re-render sidebar to update highlighting
        refreshQueries();  // Update main content
    }

    async function refreshQueries() {
        if (!currentRequestId) return;

        const typeFilter = document.getElementById('type-filter').value;
        const issueFilter = document.getElementById('issue-filter').value;
        const sortBy = document.getElementById('sort-by').value;
        const sortOrder = document.getElementById('sort-order').value;
        
        const params = new URLSearchParams();
        params.append('request_id', currentRequestId);
        if (typeFilter !== 'all') params.append('type', typeFilter);
        if (issueFilter !== 'all') params.append('issue_type', issueFilter);
        params.append('sort', sortBy);
        params.append('order', sortOrder);

        try {
            const response = await fetch(`/query-analyzer/api/queries?${params}`);
            const data = await response.json();
            
            currentQueries = data.queries;
            updateStats(data.stats);
            renderCurrentView();
            
            // Update Header Info
            document.getElementById('current-request-info').innerText = `ID: ${currentRequestId}`;
            document.getElementById('query-count').innerText = currentQueries.length;

        } catch (error) {
            console.error('Error:', error);
        }
    }

    function switchView(view) {
        currentView = view;
        
        // Update Buttons
        const btnList = document.getElementById('btn-view-list');
        const btnTimeline = document.getElementById('btn-view-timeline');
        
        if (view === 'list') {
            btnList.className = 'px-3 py-1 text-xs font-bold rounded-md bg-white shadow-sm text-indigo-600 transition-all border border-slate-200';
            btnTimeline.className = 'px-3 py-1 text-xs font-medium rounded-md text-slate-500 hover:text-slate-700 transition-all border border-transparent';
            document.getElementById('query-list').classList.remove('hidden');
            document.getElementById('timeline-view').classList.add('hidden');
        } else {
            btnList.className = 'px-3 py-1 text-xs font-medium rounded-md text-slate-500 hover:text-slate-700 transition-all border border-transparent';
            btnTimeline.className = 'px-3 py-1 text-xs font-bold rounded-md bg-white shadow-sm text-indigo-600 transition-all border border-slate-200';
            document.getElementById('query-list').classList.add('hidden');
            document.getElementById('timeline-view').classList.remove('hidden');
        }

        renderCurrentView();
    }

    function renderCurrentView() {
        if (currentView === 'list') {
            renderQueries(currentQueries);
        } else {
            renderTimeline(currentQueries);
        }
    }

    function renderTimeline(queries) {
        const container = document.getElementById('timeline-view');
        container.innerHTML = ''; // Clear container

        if (queries.length === 0) {
            container.innerHTML = '<div class="text-center py-12 text-slate-400 text-sm">No queries found for this request.</div>';
            return;
        }

        // Create a wrapper for scrolling
        const scrollWrapper = document.createElement('div');
        scrollWrapper.className = 'overflow-y-auto pr-2 custom-scrollbar';
        scrollWrapper.style.maxHeight = '50vh';
        container.appendChild(scrollWrapper);

        // Create a dedicated container for the chart inside the scroll wrapper
        const chartContainer = document.createElement('div');
        scrollWrapper.appendChild(chartContainer);

        // Prepare Data for ApexCharts
        const minTs = Math.min(...queries.map(q => q.timestamp - q.time));
        
        const seriesData = queries.map((q, index) => {
            const startMs = (q.timestamp - q.time - minTs) * 1000;
            const endMs = (q.timestamp - minTs) * 1000;
            const durationMs = (q.time * 1000).toFixed(2);
            
            return {
                x: `${index + 1}. ${q.analysis.type} (${durationMs}ms)`,
                y: [startMs, endMs],
                fillColor: getColorForType(q.analysis.type),
                queryId: q.id,
                sql: q.sql,
                duration: q.time
            };
        });

        const options = {
            series: [{
                data: seriesData
            }],
            chart: {
                type: 'rangeBar',
                height: Math.max(350, queries.length * 28), 
                fontFamily: 'JetBrains Mono, monospace', // Monospace for alignment
                toolbar: { show: false },
                zoom: { enabled: false },
                selection: { enabled: false },
                events: {
                    dataPointSelection: function(event, chartContext, config) {
                        const point = config.w.config.series[0].data[config.dataPointIndex];
                        if (point && point.queryId) {
                            showQueryDetails(point.queryId);
                        }
                    }
                }
            },
            plotOptions: {
                bar: {
                    horizontal: true,
                    barHeight: '70%', 
                    rangeBarGroupRows: true,
                    borderRadius: 2
                }
            },
            dataLabels: {
                enabled: false // Clean look, info is in label/tooltip
            },
            xaxis: {
                type: 'numeric',
                position: 'top', 
                labels: {
                    formatter: function(val) {
                        return val.toFixed(0) + 'ms';
                    },
                    style: { colors: '#94a3b8', fontFamily: 'JetBrains Mono' }
                },
                tooltip: { enabled: false },
                axisBorder: { show: false },
                axisTicks: { show: false }
            },
            yaxis: {
                labels: {
                    style: { 
                        fontSize: '11px', 
                        fontFamily: 'JetBrains Mono',
                        colors: '#334155'
                    },
                    minWidth: 200, // Ensure strictly aligned left column
                    maxWidth: 400
                }
            },
            grid: {
                borderColor: '#f1f5f9',
                xaxis: { lines: { show: true } },
                yaxis: { lines: { show: true } }, // Row lines
                column: { opacity: 0 }
            },
            tooltip: {
                custom: function({series, seriesIndex, dataPointIndex, w}) {
                    const data = w.config.series[seriesIndex].data[dataPointIndex];
                    return `
                        <div class="px-3 py-2 bg-slate-800 text-white text-xs rounded shadow-lg border border-slate-700 z-50 relative">
                             <div class="font-bold mb-1 border-b border-slate-600 pb-1 flex justify-between gap-4">
                                <span>${data.x.split('(')[0]}</span>
                                <span class="text-indigo-300 font-mono">${(data.duration * 1000).toFixed(4)}ms</span>
                             </div>
                             <div class="font-mono text-[10px] opacity-80 max-w-xs break-all whitespace-pre-wrap leading-tight">
                                ${escapeHtml(data.sql.substring(0, 150))}${data.sql.length > 150 ? '...' : ''}
                             </div>
                        </div>
                    `;
                }
            }
        };

        const chart = new ApexCharts(chartContainer, options);
        chart.render();
    }

    function getColorForType(type) {
        switch(type) {
            case 'SELECT': return '#3b82f6';
            case 'INSERT': return '#10b981';
            case 'UPDATE': return '#f59e0b';
            case 'DELETE': return '#ef4444';
            case 'CACHE':  return '#8b5cf6';
            default: return '#94a3b8';
        }
    }
    
    function updateStats(stats) {
        if (!stats) return;
        document.getElementById('total-queries').innerText = stats.total_queries || 0;
        document.getElementById('slow-queries').innerText = stats.slow_queries || 0;
        document.getElementById('avg-time').innerText = (stats.average_time || 0).toFixed(4) + 's';
    }

    function renderQueries(queries) {
        const container = document.getElementById('query-list');
        
        if (queries.length === 0) {
            container.innerHTML = `<div class="text-center py-12 text-slate-400 text-sm">No queries found for this request.</div>`;
            return;
        }

        container.innerHTML = queries.map((query, index) => {
            const typeClass = `type-${query?.analysis?.type?.toLowerCase() || 'other'}`;
            const fileShort = query?.origin?.file && query?.origin?.file !== 'unknown' 
                ? query.origin.file.split('/').slice(-2).join('/') + ':' + query.origin.line
                : '';
            
            // NEW: Vendor vs App Tag
            const isVendor = query.origin.is_vendor;
            const sourceBadge = isVendor 
                ? `<span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-500 border border-slate-200">Vendor</span>`
                : `<span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-blue-50 text-blue-600 border border-blue-100">App</span>`;

            const issues = query?.analysis?.issues || [];
            const hasNPlusOne = issues.some(i => i.type === 'n+1');
            const recommendations = query?.analysis?.recommendations || [];

            console.log(query);

            const id = String(query.id || '');
            const clickAction = id ? `onclick="showQueryDetails('${id}')"` : '';
            const cursorClass = id ? 'cursor-pointer' : 'cursor-not-allowed opacity-75';

            return `
            <div class="bg-white rounded-lg shadow-sm border border-slate-200 query-card ${typeClass} p-4 ${cursorClass}" ${clickAction}>
                <div class="flex justify-between items-start mb-2 gap-2">
                    <div class="flex items-center gap-2 min-w-0">
                         <span class="px-2 py-0.5 rounded text-xs font-bold bg-slate-100 text-slate-700 whitespace-nowrap flex-shrink-0">${query?.analysis?.type || 'QUERY'}</span>
                         ${sourceBadge}
                         ${fileShort ? `
                            <div class="flex items-center gap-1 min-w-0 text-xs font-mono text-slate-400 group">
                                <svg class="w-3 h-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path></svg> 
                                <span class="truncate" title="${query.origin.file}:${query.origin.line}">${fileShort}</span>
                                <button onclick="event.stopPropagation(); copyToClipboard('${query.origin.file}:${query.origin.line}', this)" 
                                        class="bg-slate-50 border border-slate-200 text-slate-400 hover:text-indigo-600 hover:bg-white hover:border-indigo-300 transition-all p-1 rounded shadow-sm flex-shrink-0 ml-1" 
                                        title="Copy absolute path with line number">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                                </button>
                            </div>` : ''}
                    </div>
                    <div class="flex items-center gap-2 flex-shrink-0">
                        ${hasNPlusOne ? `<span class="px-2 py-0.5 rounded text-xs font-bold bg-purple-100 text-purple-600 flex items-center gap-1 whitespace-nowrap"><svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M7 3a1 1 0 000 2h6a1 1 0 100-2H7zM4 7a1 1 0 011-1h10a1 1 0 110 2H5a1 1 0 01-1-1zM2 11a2 2 0 012-2h12a2 2 0 012 2v4a2 2 0 01-2 2H4a2 2 0 01-2-2v-4z"></path></svg> N+1</span>` : ''}
                        ${issues.length > 0 && !hasNPlusOne ? `<span class="px-2 py-0.5 rounded text-xs font-bold bg-red-100 text-red-600 flex items-center gap-1 whitespace-nowrap"><svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg> ${issues.length}</span>` : ''}
                        <span class="px-2 py-0.5 rounded text-xs font-bold whitespace-nowrap ${getPerfClass(query?.analysis?.performance?.rating)}">${(query?.time || 0).toFixed(4)}s</span>
                    </div>
                </div>
                
                <div class="font-mono text-sm text-slate-700 bg-slate-50 p-2 rounded mb-2 overflow-hidden text-ellipsis whitespace-nowrap">
                    ${escapeHtml(query?.sql || '')}
                </div>
                
                ${recommendations.length > 0 ? `
                     <div class="text-xs text-indigo-600 mt-2 flex items-center gap-1">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        ${recommendations[0] || ''} ${recommendations.length > 1 ? `(+${recommendations.length - 1} more)` : ''}
                     </div>
                ` : ''}
            </div>`;
        }).join('');
    }

    async function resetQueries() {
         if(confirm('Reset all analytics history?')) {
             await fetch('/query-analyzer/api/reset', { method: 'POST', headers: {'X-CSRF-TOKEN': csrfToken} });
             currentRequestId = null;
             refreshRequests(); // Refresh sidebar
             closeDetails();    // Close side panel
             document.getElementById('query-list').innerHTML = '<div class="text-center py-20 text-slate-400">History cleared. Waiting for new requests...</div>';
             document.getElementById('timeline-view').innerHTML = ''; // Clear timeline
         }
    }

        function getPerfClass(rating) {
            if (rating === 'fast') return 'bg-green-100 text-green-700';
            if (rating === 'moderate') return 'bg-yellow-100 text-yellow-700';
            if (rating === 'slow') return 'bg-orange-100 text-orange-700';
            return 'bg-red-100 text-red-700';
        }

        function copyToClipboard(text, btn) {
            // Fallback for non-secure contexts
            if (!navigator.clipboard) {
                const textArea = document.createElement("textarea");
                textArea.value = text;
                textArea.style.position = "fixed"; // Avoid scrolling to bottom
                document.body.appendChild(textArea);
                textArea.focus();
                textArea.select();
                try {
                    document.execCommand('copy');
                    showCopyFeedback(btn);
                } catch (err) {
                    console.error('Fallback: Oops, unable to copy', err);
                }
                document.body.removeChild(textArea);
                return;
            }

            navigator.clipboard.writeText(text).then(() => {
                showCopyFeedback(btn);
            }, (err) => {
                console.error('Async: Could not copy text: ', err);
            });
        }

        function showCopyFeedback(btn) {
            const originalHtml = btn.innerHTML;
            const originalTitle = btn.title;
            
            // Visual feedback
            btn.innerHTML = `<svg class="w-3 h-3 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>`;
            btn.classList.add('bg-green-50', 'border-green-200');
            btn.title = "Copied!";
            
            setTimeout(() => {
                btn.innerHTML = originalHtml;
                btn.title = originalTitle;
                btn.classList.remove('bg-green-50', 'border-green-200');
            }, 2000);
        }

        async function showQueryDetails(id) {
            try {
                console.group('Query Analyzer Detail Fetch');
                console.log('Fetching ID:', id);
                
                const response = await fetch(`/query-analyzer/api/query/${id}?_cb=${Date.now()}`);
                const query = await response.json();
                console.log('Fetched Query Data:', query);
                console.groupEnd();

                if (!query || query.error) {
                    throw new Error(query?.error || 'Query not found on server.');
                }
                
                const panel = document.getElementById('detail-content');
                
                const perfRating = query?.analysis?.performance?.rating || 'unknown';
                const complexity = query?.analysis?.complexity || {level: 'N/A', score: 0};
                const origin = query?.origin || {file: 'unknown', line: 0};
                const recommendations = query?.analysis?.recommendations || [];
                const issues = query?.analysis?.issues || [];

                panel.innerHTML = `
                    <div class="flex items-center justify-between mb-8 border-b border-slate-100 pb-6">
                        <div>
                            <h3 class="text-xl font-bold text-slate-900 tracking-tight">Query Details</h3>
                            <p class="text-[10px] text-slate-500 mt-1">Captured at ${new Date((query.timestamp || 0) * 1000).toLocaleString()}</p>
                        </div>
                        <div class="flex flex-col items-end gap-1">
                            <span class="px-3 py-1 rounded-full text-sm font-bold ${getPerfClass(perfRating)}">${(query?.time || 0).toFixed(5)}s</span>
                            <span class="text-[9px] font-bold uppercase tracking-wider text-slate-400">${perfRating}</span>
                        </div>
                    </div>

                    <div class="mb-8 space-y-6">
                        <div class="space-y-6">
                             <!-- SQL Section -->
                             <div class="bg-white rounded-xl border border-slate-200 overflow-hidden shadow-sm">
                                <div class="flex justify-between items-center px-4 py-3 bg-slate-50 border-b border-slate-200">
                                     <h4 class="text-xs font-bold text-slate-500 uppercase tracking-widest">SQL Statement</h4>
                                     ${query?.analysis?.type === 'SELECT' ? `<button onclick="runExplain('${query.id}')" class="text-xs bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-1.5 rounded-md font-medium flex items-center gap-1.5 transition-colors shadow-sm"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 10l-2 1m0 0l-2-1m2 1v2.5M20 7l-2 1m2-1l-2-1m2 1v2.5M14 4l-2-1-2 1M4 7l2-1M4 7l2 1M4 7v2.5M12 21l-2-1m2 1l2-1m-2 1v-2.5M6 18l-2-1v-2.5M18 18l2-1v-2.5"></path></svg> Explain</button>` : ''}
                                </div>
                                <div class="relative group">
                                    <pre class="bg-slate-50 text-slate-700 p-6 font-mono text-sm leading-relaxed overflow-x-auto whitespace-pre-wrap border-t border-slate-200 shadow-inner">${escapeHtml(query?.sql || '')}</pre>
                                </div>
                                <div id="explain-result-${query.id}" class="hidden border-t border-slate-200 bg-white p-0"></div>
                             </div>

                             ${query.bindings && query.bindings.length > 0 ? `
                             <div>
                                <div class="flex items-center gap-2 mb-3">
                                    <h4 class="text-xs font-bold text-slate-500 uppercase tracking-widest">Bindings</h4>
                                    <div class="relative group cursor-help">
                                        <svg class="w-3.5 h-3.5 text-slate-400 group-hover:text-indigo-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-3 py-2 bg-slate-800 text-white text-[10px] rounded shadow-lg opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none w-48 z-10 font-normal leading-relaxed text-center">
                                            Values substituted into the prepared statement placeholders.
                                            <div class="absolute top-full left-1/2 -translate-x-1/2 -mt-1 border-4 border-transparent border-t-slate-800"></div>
                                        </div>
                                    </div>
                                </div>
                                 <div class="bg-slate-50 p-4 rounded-xl border border-slate-200 font-mono text-sm text-slate-700 break-all leading-relaxed shadow-inner">
                                     [ ${query.bindings.map(b => typeof b === 'string' ? `<span class="text-green-600">"${escapeHtml(b)}"</span>` : `<span class="text-blue-600">${b}</span>`).join(', ')} ]
                                 </div>
                             </div>` : ''}
                        </div>

                        <div class="space-y-6">
                            <!-- Metadata Card -->
                            <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
                                <h4 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-4 pb-2 border-b border-slate-100">Metadata</h4>
                                <div class="space-y-4">
                                    <div>
                                        <div class="text-xs text-slate-500 mb-1">Request ID</div>
                                        <div class="font-mono text-sm bg-slate-100 px-2 py-1 rounded inline-block text-slate-700 break-all">${query.request_id || 'N/A'}</div>
                                    </div>
                                    <div>
                                        <div class="text-xs text-slate-500 mb-1">Connection</div>
                                        <div class="text-sm font-medium text-slate-900">${query.connection || 'default'}</div>
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-1.5 mb-1">
                                            <div class="text-xs text-slate-500">Complexity Score</div>
                                            <div class="relative group cursor-help">
                                                <svg class="w-3.5 h-3.5 text-slate-400 group-hover:text-indigo-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-3 py-2 bg-slate-800 text-white text-[10px] rounded shadow-lg opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none w-56 z-10 font-normal leading-relaxed text-center">
                                                    Estimates potentially expensive operations. <strong class="text-indigo-300">Joins</strong> and <strong class="text-indigo-300">Subqueries</strong> add more weight than simple filters. Scores > 10 are considered High.
                                                    <div class="absolute top-full left-1/2 -translate-x-1/2 -mt-1 border-4 border-transparent border-t-slate-800"></div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <div class="h-2 w-16 bg-slate-100 rounded-full overflow-hidden">
                                                <div class="h-full bg-indigo-500" style="width: ${Math.min(complexity.score * 10, 100)}%"></div>
                                            </div>
                                            <span class="text-sm font-bold text-slate-700">${complexity.level} (${complexity.score})</span>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="text-xs text-slate-500 mb-1">Origin</div>
                                        <div class="text-sm font-mono text-indigo-600 break-all leading-relaxed bg-indigo-50 p-2 rounded border border-indigo-100">
                                            ${origin.file}:${origin.line}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Analysis Card -->
                            <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
                                <h4 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-4 pb-2 border-b border-slate-100">Analysis</h4>
                                
                                <div class="space-y-4">
                                     <div>
                                         <div class="text-xs text-slate-500 mb-2">Recommendations</div>
                                         ${recommendations.length > 0  ? 
                                            `<ul class="space-y-2">
                                                ${recommendations.map(rec => `<li class="flex items-start gap-2 text-sm text-indigo-700 bg-indigo-50/50 p-2 rounded"><svg class="w-4 h-4 mt-0.5 flex-none text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg> <span class="leading-snug">${rec}</span></li>`).join('')}
                                            </ul>` 
                                            : '<div class="text-sm text-slate-400 italic flex items-center gap-2"><svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> No optimizations needed</div>'}
                                    </div>

                                     <div>
                                         <div class="text-xs text-slate-500 mb-2">Issues</div>
                                         ${issues.length > 0 ? 
                                            `<ul class="space-y-2">
                                                ${issues.map(issue => `<li class="flex items-start gap-2 text-sm font-mono text-red-700 bg-red-50/50 p-2 rounded"><svg class="w-4 h-4 mt-0.5 flex-none text-red-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg> <span class="leading-snug"><strong>${issue?.type || 'Issue'}:</strong> ${issue?.message || 'Warning detected'}</span></li>`).join('')}
                                            </ul>` 
                                            : '<div class="text-sm text-slate-400 italic">No issues detected</div>'}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;

                document.getElementById('detail-panel').classList.remove('hidden');
            } catch (error) {
                console.error('Modal Error:', error);
                alert('Could not show query details: ' + error.message);
            }
        }

        function closeDetails() {
            document.getElementById('detail-panel').classList.add('hidden');
        }

        // function closeModal() renamed/kept for potential backward compat if needed? No, just replace logic.
        function closeModal() {
            closeDetails();
        }

        function filterQueries() { refreshQueries(); }

        function updateFilters() {
            refreshRequests(); // Updates sidebar counts based on filters
            refreshQueries();  // Updates main view stats based on filters (implicit via selectRequest -> refreshQueries, but good to ensure)
        }

        function toggleAutoRefresh() {
             const checkbox = document.getElementById('auto-refresh');
             if (checkbox.checked) {
                 autoRefreshInterval = setInterval(refreshQueries, 5000);
             } else {
                 if (autoRefreshInterval) clearInterval(autoRefreshInterval);
             }
        }
        

        
        async function runExplain(id) {
            const container = document.getElementById(`explain-result-${id}`);
            if (!container) return;
            
            container.classList.remove('hidden');
            container.innerHTML = '<div class="flex items-center gap-2 text-slate-500 font-sans p-4"><svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Generating deep insights...</div>';

            try {
                console.group('Explain Call Details');
                console.log('Fetching query details for ID:', id);

                const queryRes = await fetch(`/query-analyzer/api/query/${id}?_cb=${Date.now()}`);
                if (!queryRes.ok) throw new Error(`HTTP ${queryRes.status} fetching query`);
                const query = await queryRes.json();
                console.log('Query fetched for verify:', query.sql);

                const response = await fetch(`/query-analyzer/api/explain?_cb=${Date.now()}`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                    body: JSON.stringify({ sql: query.sql, bindings: query.bindings, connection: query.connection })
                });

                if (!response.ok) throw new Error(`HTTP ${response.status} running explain`);
                const data = await response.json();
                console.log('Explain logic output:', data);
                console.groupEnd();

                if (!data || data.error) {
                    container.innerHTML = `<div class="text-red-600 font-semibold p-4">Error: ${data?.error || 'Unknown server error'}</div>`;
                    return;
                }

                // Defensive normalization
                const standard = Array.isArray(data.standard) ? data.standard : [];
                const analyze = Array.isArray(data.analyze) ? data.analyze : [];
                const insights = Array.isArray(data.insights) ? data.insights : [];
                const summary = data.summary || 'No summary available.';

                // Detect if Standard is a tree (single column with long content)
                const standardIsTree = standard.length === 1 && Object.keys(standard[0] || {}).length === 1;

                let output = `
                    <div class="p-4">
                        <div class="mb-4 flex justify-between items-center border-b border-slate-100 pb-2">
                            <div class="flex flex-col">
                                 <span class="text-indigo-600 font-bold uppercase text-[10px] tracking-widest">Deep Analysis Results</span>
                                 <span class="text-[9px] text-slate-400 font-mono mt-0.5 truncate max-w-sm">Verifying SQL: ${escapeHtml(query.sql.substring(0, 70))}...</span>
                            </div>
                            <button onclick="document.getElementById('explain-result-${id}').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">&times;</button>
                        </div>
                    <div class="mb-6 bg-slate-900 text-slate-100 p-4 rounded-lg shadow-inner border-l-4 border-indigo-500">
                        <h5 class="text-[10px] uppercase font-bold text-indigo-400 mb-2 tracking-widest">Humanized Summary</h5>
                        <p class="text-xs leading-relaxed font-mono">${summary}</p>
                    </div>

                    <!-- Bullet Insights -->
                    ${insights.length > 0 ? `
                    <div class="mb-6 space-y-2">
                        ${insights.map(i => `<div class="bg-indigo-50 border-l-4 border-indigo-400 p-2 text-sm text-indigo-800 font-medium shadow-sm">${i}</div>`).join('')}
                    </div>` : ''}

                    <!-- Dual View Profiles -->
                    <div class="space-y-6">
                        <!-- 1. Standard Plan -->
                        ${standard.length > 0 ? `
                        <div>
                            <h5 class="text-[10px] uppercase font-bold text-slate-400 mb-2 tracking-widest">Standard Execution Plan</h5>
                            ${standardIsTree ? `
                                <pre class="whitespace-pre-wrap bg-slate-50 p-3 rounded border border-slate-100 font-mono text-xs leading-tight text-slate-600">${escapeHtml(String(Object.values(standard[0])[0] || ''))}</pre>
                            ` : `
                                <div class="overflow-x-auto border border-slate-200 rounded">
                                    <table class="w-full text-left border-collapse min-w-full">
                                        <thead>
                                            <tr class="bg-slate-50 border-b border-slate-200">
                                                ${Object.keys(standard[0] || {}).map(k => `<th class="p-2 font-bold text-slate-500 text-[9px] uppercase tracking-wider border-r border-slate-200 last:border-0">${k}</th>`).join('')}
                                            </tr>
                                        </thead>
                                        <tbody>
                                            ${standard.map(row => `
                                                <tr class="border-b border-slate-100 last:border-0 hover:bg-slate-50 transition-colors">
                                                    ${Object.values(row || {}).map(v => `<td class="p-2 border-r border-slate-100 last:border-0 text-[11px] text-slate-600">${v === null ? '<span class="text-slate-300 italic">null</span>' : escapeHtml(String(v))}</td>`).join('')}
                                                </tr>
                                            `).join('')}
                                        </tbody>
                                    </table>
                                </div>
                            `}
                        </div>` : ''}

                        <!-- 2. ANALYZE Profile Tree -->
                        <!-- 2. ANALYZE Profile Tree -->
                        <!-- If we have the raw tree, show it first -->
                        ${data.raw_analyze ? `
                        <div class="mb-6">
                            <h5 class="text-[10px] uppercase font-bold text-slate-400 mb-2 tracking-widest">Profiling Tree (Raw)</h5>
                            <pre class="whitespace-pre-wrap bg-slate-900 text-green-400 p-4 rounded font-mono text-xs leading-tight border border-slate-700 shadow-lg select-text overflow-x-auto">${escapeHtml(data.raw_analyze)}</pre>
                        </div>` : ''}

                        ${data.supports_analyze && analyze.length > 0 && analyze[0] ? `
                        <div>
                            <h5 class="text-[10px] uppercase font-bold text-slate-400 mb-2 tracking-widest">Deep Analysis Explanation</h5>
                            <div class="markdown-content bg-slate-50 text-slate-700 p-4 rounded text-xs leading-relaxed border border-slate-200 shadow-sm">${marked.parse(Object.values(analyze[0])[0] || '')}</div>
                        </div>` : ''}
                    </div>
                </div>
                `;

                container.innerHTML = output;

            } catch (error) {
                console.error('Explain Error Details:', error);
                container.innerHTML = `<div class="text-red-600 font-semibold p-4">System Error: ${error.message} (See console for details)</div>`;
            }
        }

        async function exportQueries(format) {
            const res = await fetch('/query-analyzer/api/export', { 
                method: 'POST', 
                headers: {'X-CSRF-TOKEN': csrfToken, 'Content-Type': 'application/json'},
                body: JSON.stringify({format})
            });
            const data = await res.json();
            const blob = new Blob([data.data], {type: format === 'csv' ? 'text/csv' : 'application/json'});
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a'); a.href = url; a.download = data.filename; a.click();
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    </script>
</body>
</html>