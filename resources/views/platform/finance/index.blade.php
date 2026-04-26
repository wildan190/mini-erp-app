<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Finance AI Stack - Mini ERP</title>

    <!-- Google Fonts: Inter & 72 (SAP's font style) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Tailwind 4 CSS -->
    <script src="https://unpkg.com/@tailwindcss/browser@4"></script>
    
    <!-- ApexCharts for SAP-like Analytics -->
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    <style type="text/tailwindcss">
        @theme {
            --color-sap-blue: #0a6ed1;
            --color-sap-navy: #1c2d3d;
            --color-horizon-bg-light: #f5f6f7;
            --color-horizon-bg-dark: #12171c;
            --color-horizon-card-light: #ffffff;
            --color-horizon-card-dark: #1d232a;
            --radius-sap: 0.5rem;
        }

        body {
            font-family: 'Inter', sans-serif;
        }

        .sap-card {
            @apply bg-horizon-card-light dark:bg-horizon-card-dark border border-gray-200 dark:border-gray-700 rounded-sap shadow-sm hover:shadow-md transition-all duration-300;
        }

        .sap-header {
            @apply bg-sap-navy text-white px-6 py-3 flex items-center justify-between shadow-lg;
        }

        .sap-nav-item {
            @apply text-gray-300 hover:text-white px-3 py-2 text-sm font-medium transition-colors;
        }

        .sap-badge {
            @apply px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-100;
        }

        /* SAP Horizon Tab style */
        .sap-tab {
            @apply px-4 py-2 text-sm font-medium border-b-2 border-transparent hover:text-sap-blue transition-all cursor-pointer;
        }
        .sap-tab-active {
            @apply border-sap-blue text-sap-blue;
        }
    </style>
</head>
<body class="bg-horizon-bg-light dark:bg-horizon-bg-dark text-gray-900 dark:text-gray-100 min-h-screen transition-colors duration-500">

    <!-- SAP Shell Header -->
    <header class="sap-header">
        <div class="flex items-center gap-6">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 bg-sap-blue rounded-md flex items-center justify-center">
                    <span class="text-white font-bold">F</span>
                </div>
                <h1 class="text-lg font-bold tracking-tight">Mini ERP <span class="font-normal text-gray-400">| Finance AI</span></h1>
            </div>
            <nav class="hidden md:flex gap-1">
                <a href="#" class="sap-nav-item">Dashboard</a>
                <a href="#" class="sap-nav-item">FP&A</a>
                <a href="#" class="sap-nav-item">Supply Chain</a>
                <a href="#" class="sap-nav-item">Forecasting</a>
            </nav>
        </div>
        <div class="flex items-center gap-4">
            <button onclick="toggleTheme()" class="p-2 rounded-full hover:bg-white/10 transition-colors">
                <svg id="theme-icon" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                </svg>
            </button>
            <div class="w-8 h-8 rounded-full bg-gray-600 flex items-center justify-center border border-gray-500">
                <span class="text-xs">JS</span>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="p-6 lg:p-10 max-w-(--breakpoint-2xl) mx-auto">
        
        <!-- Welcome Section -->
        <div class="mb-8">
            <h2 class="text-2xl font-semibold mb-1">Financial Intelligence Center</h2>
            <p class="text-gray-500 dark:text-gray-400 text-sm italic">AI-powered insights for modern enterprise management</p>
        </div>

        <!-- KPI Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
            <div class="sap-card p-5">
                <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Net Profit</div>
                <div class="text-3xl font-bold text-sap-blue" id="net-profit-val">$--</div>
                <div class="mt-2 flex items-center gap-1 text-xs text-green-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" />
                    </svg>
                    <span>12% Increase</span>
                </div>
            </div>
            <div class="sap-card p-5">
                <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Revenue Forecast</div>
                <div class="text-3xl font-bold" id="rev-forecast-val">$--</div>
                <div class="mt-2 text-xs text-gray-400 font-medium italic">Predictive (Linear Reg)</div>
            </div>
            <div class="sap-card p-5">
                <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Inventory Risk</div>
                <div class="text-3xl font-bold text-orange-500" id="inv-risk-val">Calculating...</div>
                <div class="mt-2 text-xs text-gray-400 font-medium italic">AI Classification (KNN)</div>
            </div>
            <div class="sap-card p-5">
                <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Confidence Score</div>
                <div class="text-3xl font-bold text-green-500" id="conf-score-val">--%</div>
                <div class="mt-2 text-xs text-gray-400 font-medium italic">Model Accuracy</div>
            </div>
        </div>

        <!-- Main Analytics Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- FP&A Section (Linear Regression) -->
            <div class="lg:col-span-2 space-y-8">
                <div class="sap-card overflow-hidden">
                    <div class="p-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center bg-gray-50/50 dark:bg-gray-800/50">
                        <h3 class="font-semibold text-sm">Revenue Trends & FP&A Projection (Linear Regression)</h3>
                        <span class="sap-badge">Historical + Forecast</span>
                    </div>
                    <div class="p-6">
                        <div id="revenue-chart"></div>
                    </div>
                    <div class="p-4 bg-blue-50/30 dark:bg-blue-900/10 text-xs border-t border-gray-100 dark:border-gray-800">
                        <span class="font-bold text-sap-blue">AI Context:</span> The trend line is calculated using ordinary least squares regression based on the last 24 months of revenue data.
                    </div>
                </div>

                <!-- Cash Forecasting Section -->
                <div class="sap-card overflow-hidden">
                    <div class="p-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50">
                        <h3 class="font-semibold text-sm">Cash Position Forecast (6 Months Rolling)</h3>
                    </div>
                    <div class="p-6">
                        <div id="cash-forecast-chart"></div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Supply Chain & Classification -->
            <div class="space-y-8">
                <div class="sap-card overflow-hidden">
                    <div class="p-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50">
                        <h3 class="font-semibold text-sm">Inventory Classification (KNN AI)</h3>
                    </div>
                    <div class="p-6">
                        <div class="flex items-center justify-center mb-6">
                            <div class="relative w-40 h-40">
                                <svg class="w-full h-full text-gray-200 dark:text-gray-700" viewBox="0 0 36 36">
                                    <path class="stroke-current fill-none" stroke-width="3" stroke-dasharray="100, 100" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                                    <path id="inv-circle" class="stroke-orange-500 fill-none transition-all duration-1000" stroke-width="3" stroke-dasharray="0, 100" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                                </svg>
                                <div class="absolute inset-0 flex flex-col items-center justify-center">
                                    <span class="text-2xl font-bold" id="risk-percent">--%</span>
                                    <span class="text-[10px] uppercase text-gray-500">Risk Factor</span>
                                </div>
                            </div>
                        </div>
                        <div class="space-y-4">
                            <div class="flex justify-between text-xs">
                                <span class="text-gray-500">Avg. Daily Demand</span>
                                <span class="font-semibold">45 Units</span>
                            </div>
                            <div class="flex justify-between text-xs">
                                <span class="text-gray-500">Lead Time</span>
                                <span class="font-semibold">6 Days</span>
                            </div>
                            <div class="pt-4 border-t border-gray-100 dark:border-gray-800">
                                <p class="text-xs leading-relaxed text-gray-600 dark:text-gray-400 italic" id="inv-recommendation">
                                    "Analyzing nearest historical patterns..."
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Transactions -->
                <div class="sap-card overflow-hidden">
                    <div class="p-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50">
                        <h3 class="font-semibold text-sm">Recent Intelligence Logs</h3>
                    </div>
                    <div class="p-0">
                        <table class="w-full text-xs text-left">
                            <thead class="bg-gray-50 dark:bg-gray-800/30 text-gray-500">
                                <tr>
                                    <th class="px-4 py-2 font-medium">Date</th>
                                    <th class="px-4 py-2 font-medium">Event</th>
                                    <th class="px-4 py-2 font-medium">Model</th>
                                </tr>
                            </thead>
                            <tbody id="logs-body" class="divide-y divide-gray-100 dark:divide-gray-800">
                                <!-- Logs will be injected here -->
                            </tbody>
                        </table>
                    </div>
                    <div class="p-3 text-center border-t border-gray-100 dark:border-gray-800">
                        <button class="text-xs text-sap-blue font-semibold hover:underline">View All Intelligence Logs</button>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        // Theme Management
        function toggleTheme() {
            const html = document.documentElement;
            html.classList.toggle('dark');
            localStorage.setItem('theme', html.classList.contains('dark') ? 'dark' : 'light');
            updateThemeIcon();
        }

        function updateThemeIcon() {
            const icon = document.getElementById('theme-icon');
            if (document.documentElement.classList.contains('dark')) {
                icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />';
            } else {
                icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />';
            }
        }

        // Apply saved theme
        if (localStorage.getItem('theme') === 'light') {
            document.documentElement.classList.remove('dark');
        }
        updateThemeIcon();

        // Data Fetching and Charts
        async function initDashboard() {
            try {
                // Fetch Dashboard Data
                // Note: In real app, we'd use CSRF token and Auth session. This is a mockup of the API response.
                const dashResp = await fetch('/api/platform/finance/dashboard', {
                    headers: { 'Accept': 'application/json' }
                }).then(r => r.json());

                const fpaResp = await fetch('/api/platform/finance/fpa/revenue-analysis', {
                    headers: { 'Accept': 'application/json' }
                }).then(r => r.json());

                const riskResp = await fetch('/api/platform/finance/supply-chain/risk-assessment', {
                    headers: { 'Accept': 'application/json' }
                }).then(r => r.json());

                const forecastResp = await fetch('/api/platform/finance/forecasting/cash-forecast', {
                    headers: { 'Accept': 'application/json' }
                }).then(r => r.json());

                if (dashResp.status === 'success') {
                    document.getElementById('net-profit-val').innerText = '$' + Number(dashResp.data.summary.net_profit).toLocaleString();
                    
                    const logsBody = document.getElementById('logs-body');
                    dashResp.data.recent_transactions.forEach(tx => {
                        const row = `<tr>
                            <td class="px-4 py-3 text-gray-500">${new Date(tx.record_date).toLocaleDateString()}</td>
                            <td class="px-4 py-3 font-medium">${tx.category}</td>
                            <td class="px-4 py-3"><span class="sap-badge">Manual Log</span></td>
                        </tr>`;
                        logsBody.innerHTML += row;
                    });
                }

                if (fpaResp.status === 'success') {
                    document.getElementById('rev-forecast-val').innerText = '$' + Math.round(fpaResp.data.predictions[0].predicted_value).toLocaleString();
                    document.getElementById('conf-score-val').innerText = Math.round(fpaResp.data.model.r_squared * 100) + '%';
                    
                    renderRevenueChart(fpaResp.data);
                }

                if (riskResp.status === 'success') {
                    document.getElementById('inv-risk-val').innerText = riskResp.data.risk_category;
                    document.getElementById('inv-recommendation').innerText = '"' + riskResp.data.recommendation + '"';
                    const riskValues = { 'Low Risk': 20, 'Medium Risk': 60, 'High Risk': 95 };
                    const riskLevel = riskValues[riskResp.data.risk_category];
                    document.getElementById('risk-percent').innerText = riskLevel + '%';
                    document.getElementById('inv-circle').setAttribute('stroke-dasharray', `${riskLevel}, 100`);
                    if (riskLevel > 80) document.getElementById('inv-circle').classList.replace('stroke-orange-500', 'stroke-red-500');
                }

                if (forecastResp.status === 'success') {
                    renderForecastChart(forecastResp.data);
                }

            } catch (err) {
                console.error("Dashboard error:", err);
            }
        }

        function renderRevenueChart(data) {
            const histLabels = Object.keys(data.historical);
            const histValues = Object.values(data.historical);
            
            // For trend line, we calculate it using the model we got
            const trendValues = histLabels.map((_, i) => (data.model.slope * i) + data.model.intercept);

            const options = {
                chart: { type: 'area', height: 350, toolbar: { show: false }, fontFamily: 'Inter, sans-serif' },
                series: [
                    { name: 'Historical Revenue', data: histValues },
                    { name: 'Linear Trend', data: trendValues, type: 'line' }
                ],
                stroke: { curve: 'smooth', width: [3, 2], dashArray: [0, 5] },
                colors: ['#0a6ed1', '#ffa500'],
                dataLabels: { enabled: false },
                xaxis: { categories: histLabels, labels: { style: { colors: '#777' } } },
                yaxis: { labels: { style: { colors: '#777' } } },
                grid: { borderColor: '#e7e7e733' },
                theme: { mode: document.documentElement.classList.contains('dark') ? 'dark' : 'light' }
            };

            new ApexCharts(document.querySelector("#revenue-chart"), options).render();
        }

        function renderForecastChart(data) {
            const labels = data.forecast_6_months.map(f => 'Month ' + (f.period + 1));
            const values = data.forecast_6_months.map(f => Math.round(f.value));

            const options = {
                chart: { type: 'bar', height: 250, toolbar: { show: false } },
                series: [{ name: 'Predicted Cash Position', data: values }],
                plotOptions: { bar: { borderRadius: 4, columnWidth: '45%' } },
                colors: ['#22c55e'],
                xaxis: { categories: labels },
                theme: { mode: document.documentElement.classList.contains('dark') ? 'dark' : 'light' }
            };

            new ApexCharts(document.querySelector("#cash-forecast-chart"), options).render();
        }

        initDashboard();
    </script>
</body>
</html>
