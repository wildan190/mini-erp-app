<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mini-ERP Core Engine & Documentation Portal</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">

    <!-- Modern Vanilla CSS -->
    <style>
        :root {
            --bg-main: #090D16;
            --bg-sidebar: #0E1322;
            --bg-card: #121829;
            --bg-card-hover: #1A2238;
            --border-color: #1E2742;
            --border-highlight: #2F3B66;
            --accent-blue: #3B82F6;
            --accent-indigo: #6366F1;
            --accent-purple: #8B5CF6;
            --accent-emerald: #10B981;
            --accent-amber: #F59E0B;
            --accent-rose: #F43F5E;
            --text-main: #F8FAFC;
            --text-muted: #94A3B8;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--bg-main);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            background-image: 
                radial-gradient(circle at 10% 10%, rgba(59, 130, 246, 0.08) 0%, transparent 40%),
                radial-gradient(circle at 90% 90%, rgba(139, 92, 246, 0.08) 0%, transparent 40%);
        }

        /* Top Bar */
        .topbar {
            border-bottom: 1px solid var(--border-color);
            background: rgba(14, 19, 34, 0.8);
            backdrop-filter: blur(12px);
            position: sticky;
            top: 0;
            z-index: 40;
            padding: 0.85rem 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 0.85rem;
        }

        .brand-logo {
            width: 38px;
            height: 38px;
            background: linear-gradient(135deg, var(--accent-blue), var(--accent-purple));
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 1.15rem;
            color: #fff;
            box-shadow: 0 0 16px rgba(59, 130, 246, 0.4);
        }

        .brand-title {
            font-size: 1.1rem;
            font-weight: 700;
            letter-spacing: -0.02em;
            color: #fff;
        }

        .brand-subtitle {
            font-size: 0.72rem;
            color: var(--text-muted);
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: rgba(16, 185, 129, 0.1);
            border: 1px solid rgba(16, 185, 129, 0.3);
            color: var(--accent-emerald);
            padding: 0.35rem 0.85rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .pulse-dot {
            width: 7px;
            height: 7px;
            background-color: var(--accent-emerald);
            border-radius: 50%;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.4; transform: scale(1.2); }
        }

        /* App Layout (Sidebar + Content) */
        .layout-container {
            display: flex;
            flex: 1;
            max-width: 1400px;
            width: 100%;
            margin: 0 auto;
        }

        /* Navigation Sidebar */
        .sidebar {
            width: 280px;
            padding: 1.75rem 1.25rem;
            border-right: 1px solid var(--border-color);
            background: var(--bg-sidebar);
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            flex-shrink: 0;
        }

        .nav-category {
            font-size: 0.68rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: var(--text-muted);
            margin: 1.25rem 0.5rem 0.5rem;
        }

        .nav-category:first-of-type {
            margin-top: 0;
        }

        .tab-btn {
            width: 100%;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.7rem 0.85rem;
            border-radius: 12px;
            border: 1px solid transparent;
            background: transparent;
            color: #94A3B8;
            font-size: 0.88rem;
            font-weight: 600;
            text-align: left;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .tab-btn:hover {
            color: #F8FAFC;
            background: rgba(255, 255, 255, 0.04);
            border-color: rgba(255, 255, 255, 0.06);
        }

        .tab-btn.active {
            color: #fff;
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.15), rgba(99, 102, 241, 0.2));
            border-color: rgba(99, 102, 241, 0.35);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.1);
        }

        .tab-btn .tab-icon {
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 22px;
        }

        /* Content Area */
        .content-area {
            flex: 1;
            padding: 2.25rem 2.5rem;
            overflow-y: auto;
        }

        .tab-pane {
            display: none;
            animation: fadeIn 0.25s ease forwards;
        }

        .tab-pane.active {
            display: block;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(6px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Common Elements inside Tab */
        .page-header {
            margin-bottom: 2rem;
        }

        .page-header h2 {
            font-size: 1.85rem;
            font-weight: 800;
            letter-spacing: -0.03em;
            margin-bottom: 0.5rem;
        }

        .page-header h2 span {
            background: linear-gradient(135deg, #60A5FA, #A78BFA);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .page-header p {
            font-size: 0.95rem;
            color: var(--text-muted);
            line-height: 1.6;
            max-width: 800px;
        }

        /* Action Buttons Grid */
        .action-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 1rem;
            margin-bottom: 2.5rem;
        }

        .btn {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.9rem 1.25rem;
            border-radius: 14px;
            font-size: 0.88rem;
            font-weight: 700;
            text-decoration: none;
            transition: all 0.2s ease;
            cursor: pointer;
            border: 1px solid transparent;
        }

        .btn:hover {
            transform: translateY(-2px);
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--accent-blue), #2563EB);
            color: #fff;
            box-shadow: 0 4px 14px rgba(37, 99, 235, 0.3);
        }

        .btn-purple {
            background: linear-gradient(135deg, var(--accent-purple), #7C3AED);
            color: #fff;
            box-shadow: 0 4px 14px rgba(124, 58, 237, 0.3);
        }

        .btn-emerald {
            background: linear-gradient(135deg, var(--accent-emerald), #059669);
            color: #fff;
            box-shadow: 0 4px 14px rgba(16, 185, 129, 0.3);
        }

        .btn-outline {
            background: rgba(255, 255, 255, 0.03);
            border-color: var(--border-color);
            color: #E2E8F0;
        }

        .btn-outline:hover {
            background: rgba(255, 255, 255, 0.08);
            border-color: var(--border-highlight);
        }

        /* Cards Grid */
        .grid-2 {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(360px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 18px;
            padding: 1.5rem;
            transition: all 0.2s ease;
        }

        .card:hover {
            border-color: var(--border-highlight);
        }

        .card-header {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1.25rem;
        }

        .card-icon {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.15rem;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }

        .card-title {
            font-size: 1.05rem;
            font-weight: 700;
        }

        /* Specs */
        .specs-list {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .specs-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.86rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px dashed rgba(255, 255, 255, 0.07);
        }

        .specs-label {
            color: var(--text-muted);
        }

        .specs-value {
            font-family: 'JetBrains Mono', monospace;
            font-weight: 600;
            color: #E2E8F0;
        }

        /* Services List */
        .services-list {
            display: flex;
            flex-direction: column;
            gap: 0.65rem;
        }

        .service-badge {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: rgba(14, 19, 34, 0.6);
            border: 1px solid var(--border-color);
            padding: 0.7rem 0.9rem;
            border-radius: 12px;
        }

        .service-info {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .svc-dot {
            width: 9px;
            height: 9px;
            border-radius: 50%;
            display: inline-block;
        }

        .svc-running {
            background-color: #10b981;
            box-shadow: 0 0 8px rgba(16, 185, 129, 0.6);
        }

        .svc-offline {
            background-color: #ef4444;
            box-shadow: 0 0 8px rgba(239, 68, 68, 0.6);
        }

        .svc-checking {
            background-color: #f59e0b;
            box-shadow: 0 0 8px rgba(245, 158, 11, 0.6);
            animation: pulse 1s infinite;
        }

        .service-name {
            font-weight: 700;
            font-size: 0.85rem;
        }

        .service-desc {
            font-size: 0.72rem;
            color: var(--text-muted);
        }

        .service-tag {
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.72rem;
            padding: 0.2rem 0.5rem;
            border-radius: 6px;
            background: rgba(59, 130, 246, 0.1);
            color: #93C5FD;
            border: 1px solid rgba(59, 130, 246, 0.2);
            font-weight: 600;
        }

        .svc-status-label {
            font-size: 0.72rem;
            font-weight: 700;
            padding: 2px 8px;
            border-radius: 4px;
        }

        .lbl-running { color: #34d399; background: rgba(16, 185, 129, 0.12); }
        .lbl-offline { color: #f87171; background: rgba(239, 68, 68, 0.12); }
        .lbl-checking { color: #fbbf24; background: rgba(245, 158, 11, 0.12); }

        /* Guide Boxes & Code Blocks */
        .guide-box {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 18px;
            padding: 1.75rem;
            margin-bottom: 2rem;
        }

        .guide-box h3 {
            font-size: 1.15rem;
            font-weight: 700;
            margin-bottom: 0.75rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .guide-step {
            margin-bottom: 1.5rem;
        }

        .guide-step:last-child {
            margin-bottom: 0;
        }

        .guide-step-title {
            font-weight: 700;
            font-size: 0.92rem;
            color: #F1F5F9;
            margin-bottom: 0.35rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .guide-step-desc {
            font-size: 0.84rem;
            color: var(--text-muted);
            line-height: 1.5;
            margin-bottom: 0.65rem;
        }

        .code-block {
            background: #070A11;
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 1.1rem 1.25rem;
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.82rem;
            color: #34D399;
            overflow-x: auto;
            line-height: 1.7;
        }

        .code-comment { color: #64748B; }
        .code-cmd { color: #60A5FA; }

        /* Responsive Mobile Layout */
        @media (max-width: 900px) {
            .layout-container {
                flex-direction: column;
            }
            .sidebar {
                width: 100%;
                border-right: none;
                border-bottom: 1px solid var(--border-color);
                padding: 1rem;
                overflow-x: auto;
                flex-direction: row;
                flex-wrap: wrap;
            }
            .nav-category {
                width: 100%;
                margin: 0.5rem 0 0.25rem;
            }
            .tab-btn {
                width: auto;
                flex: 1 1 calc(50% - 0.5rem);
            }
            .content-area {
                padding: 1.5rem 1rem;
            }
        }
    </style>
</head>
<body>
    <!-- Topbar -->
    <header class="topbar">
        <div class="brand">
            <div class="brand-logo">ERP</div>
            <div>
                <div class="brand-title">Mini-ERP Architecture & Docs Portal</div>
                <div class="brand-subtitle">Standalone Microservices Engine</div>
            </div>
        </div>
        <div class="status-badge">
            <span class="pulse-dot"></span>
            <span>Gateway Online (Port 8000)</span>
        </div>
    </header>

    <!-- Layout (Sidebar + Modular Content Tabs) -->
    <div class="layout-container">
        <!-- Sidebar Navigation -->
        <aside class="sidebar">
            <div class="nav-category">Overview</div>
            <button class="tab-btn active" onclick="switchTab('tab-overview')">
                <span class="tab-icon">⚡</span>
                <span>System Overview</span>
            </button>
            <button class="tab-btn" onclick="switchTab('tab-services')">
                <span class="tab-icon">🛰️</span>
                <span>Microservices Health</span>
            </button>

            <div class="nav-category">Documentation & Setup</div>
            <button class="tab-btn" onclick="switchTab('tab-build')">
                <span class="tab-icon">🛠️</span>
                <span>Build & Per-Service Ops</span>
            </button>
            <button class="tab-btn" onclick="switchTab('tab-seeder')">
                <span class="tab-icon">🌱</span>
                <span>Seeders & Factories</span>
            </button>
            <button class="tab-btn" onclick="switchTab('tab-architecture')">
                <span class="tab-icon">🏛️</span>
                <span>Architecture & Fault Isolation</span>
            </button>

            <div class="nav-category">External Tools</div>
            <a href="{{ url('/api/documentation') }}" class="tab-btn" target="_blank" style="text-decoration:none;">
                <span class="tab-icon">📖</span>
                <span>Swagger API Docs ↗</span>
            </a>
            <a href="{{ url('/log-viewer') }}" class="tab-btn" target="_blank" style="text-decoration:none;">
                <span class="tab-icon">📜</span>
                <span>Log Viewer (Opcodes) ↗</span>
            </a>
            <a href="{{ url('/horizon') }}" class="tab-btn" target="_blank" style="text-decoration:none;">
                <span class="tab-icon">📊</span>
                <span>Laravel Horizon ↗</span>
            </a>
        </aside>

        <!-- Main Content Area -->
        <main class="content-area">
            <!-- TAB 1: System Overview -->
            <section id="tab-overview" class="tab-pane active">
                <div class="page-header">
                    <h2>Enterprise Core Backend & <span>Microservices Engine</span></h2>
                    <p>
                        Platform Backend Mini-ERP berperforma tinggi ditenagai oleh PHP 8.5, Laravel Octane (Swoole), PostgreSQL, Redis, dan Microservice Machine Learning Python (Face Recognition).
                    </p>
                </div>

                <!-- Quick Action Buttons -->
                <div class="action-grid">
                    <a href="{{ url('/api/documentation') }}" class="btn btn-primary" target="_blank">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        <span>Swagger Docs</span>
                    </a>
                    <a href="{{ url('/log-viewer') }}" class="btn btn-emerald" target="_blank">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        <span>Log Viewer</span>
                    </a>
                    <a href="{{ url('/horizon') }}" class="btn btn-purple" target="_blank">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                        <span>Laravel Horizon</span>
                    </a>
                    <a href="{{ url('/up') }}" class="btn btn-outline" target="_blank">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        <span>Health Check (/up)</span>
                    </a>
                </div>

                <!-- Server Specifications -->
                <div class="grid-2">
                    <div class="card">
                        <div class="card-header">
                            <div class="card-icon">🖥️</div>
                            <div class="card-title">Minimum Server Specifications</div>
                        </div>
                        <ul class="specs-list">
                            <li class="specs-item">
                                <span class="specs-label">CPU Cores</span>
                                <span class="specs-value">2 Cores (ARM64 / x86_64)</span>
                            </li>
                            <li class="specs-item">
                                <span class="specs-label">RAM (Minimum)</span>
                                <span class="specs-value">4 GB RAM</span>
                            </li>
                            <li class="specs-item">
                                <span class="specs-label">RAM (Recommended)</span>
                                <span class="specs-value">8 GB RAM (for ML Compilation)</span>
                            </li>
                            <li class="specs-item">
                                <span class="specs-label">Storage Space</span>
                                <span class="specs-value">10 GB SSD / NVMe</span>
                            </li>
                            <li class="specs-item">
                                <span class="specs-label">Runtime Engine</span>
                                <span class="specs-value">PHP 8.5 CLI + Swoole 6</span>
                            </li>
                            <li class="specs-item">
                                <span class="specs-label">Database & Cache</span>
                                <span class="specs-value">PostgreSQL 16 + Redis 7</span>
                            </li>
                        </ul>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <div class="card-icon">🔐</div>
                            <div class="card-title">Default Administrator Access</div>
                        </div>
                        <p style="font-size:0.85rem;color:var(--text-muted);margin-bottom:1rem;">
                            Gunakan akun master super-admin berikut untuk mengakses seluruh endpoint dan UI Frontend:
                        </p>
                        <div class="code-block" style="margin-bottom:1rem;">
                            <span class="code-comment"># Super Admin Credentials:</span><br>
                            Email    : <span class="code-cmd">admin@erp.com</span><br>
                            Password : <span class="code-cmd">password</span><br>
                            Role     : <span style="color:#A78BFA;">super-admin (Full Access)</span>
                        </div>
                        <p style="font-size:0.8rem;color:var(--text-muted);">
                            Semua request API dari Frontend dikirimkan ke Gateway Nginx di Port <code>8000</code>.
                        </p>
                    </div>
                </div>
            </section>

            <!-- TAB 2: Microservices Health -->
            <section id="tab-services" class="tab-pane">
                <div class="page-header">
                    <h2>Microservices <span>Real-time Health Status</span></h2>
                    <p>
                        Monitor status liveness dari seluruh 10 container microservices. Setiap container berjalan di isolated process dengan healthcheck endpoint.
                    </p>
                </div>

                <div class="card">
                    <div class="card-header" style="justify-content:space-between;">
                        <div style="display:flex;align-items:center;gap:0.75rem;">
                            <div class="card-icon">🛰️</div>
                            <div class="card-title">Registered Containers & Port Routing</div>
                        </div>
                        <div style="display:flex;align-items:center;gap:8px;">
                            <span id="status-last-checked" style="font-size:0.75rem;color:var(--text-muted);">Checking...</span>
                            <button onclick="checkAllServices()" class="btn btn-outline" style="padding:0.35rem 0.75rem;font-size:0.75rem;">↺ Refresh</button>
                        </div>
                    </div>

                    <div class="services-list" id="services-status-list">
                        <!-- Gateway -->
                        <div class="service-badge" id="svc-gateway">
                            <div class="service-info">
                                <span class="svc-dot svc-checking" id="dot-gateway"></span>
                                <div>
                                    <div class="service-name">mini-erp-gateway</div>
                                    <div class="service-desc">NGINX Reverse Proxy API Gateway</div>
                                </div>
                            </div>
                            <div style="display:flex;align-items:center;gap:8px;">
                                <span class="svc-status-label" id="label-gateway">Checking...</span>
                                <span class="service-tag">Port 8000</span>
                            </div>
                        </div>
                        <!-- HRM -->
                        <div class="service-badge" id="svc-hrm">
                            <div class="service-info">
                                <span class="svc-dot svc-checking" id="dot-hrm"></span>
                                <div>
                                    <div class="service-name">mini-erp-hrm-service</div>
                                    <div class="service-desc">HRM Microservice — Laravel Octane (Swoole)</div>
                                </div>
                            </div>
                            <div style="display:flex;align-items:center;gap:8px;">
                                <span class="svc-status-label" id="label-hrm">Checking...</span>
                                <span class="service-tag">Port 8001</span>
                            </div>
                        </div>
                        <!-- CRM -->
                        <div class="service-badge" id="svc-crm">
                            <div class="service-info">
                                <span class="svc-dot svc-checking" id="dot-crm"></span>
                                <div>
                                    <div class="service-name">mini-erp-crm-service</div>
                                    <div class="service-desc">CRM Microservice — Laravel Octane (Swoole)</div>
                                </div>
                            </div>
                            <div style="display:flex;align-items:center;gap:8px;">
                                <span class="svc-status-label" id="label-crm">Checking...</span>
                                <span class="service-tag">Port 8002</span>
                            </div>
                        </div>
                        <!-- Finance -->
                        <div class="service-badge" id="svc-finance">
                            <div class="service-info">
                                <span class="svc-dot svc-checking" id="dot-finance"></span>
                                <div>
                                    <div class="service-name">mini-erp-finance-service</div>
                                    <div class="service-desc">Finance Microservice — Laravel Octane (Swoole)</div>
                                </div>
                            </div>
                            <div style="display:flex;align-items:center;gap:8px;">
                                <span class="svc-status-label" id="label-finance">Checking...</span>
                                <span class="service-tag">Port 8003</span>
                            </div>
                        </div>
                        <!-- Purchasing -->
                        <div class="service-badge" id="svc-purchasing">
                            <div class="service-info">
                                <span class="svc-dot svc-checking" id="dot-purchasing"></span>
                                <div>
                                    <div class="service-name">mini-erp-purchasing-service</div>
                                    <div class="service-desc">Purchasing Microservice — Laravel Octane (Swoole)</div>
                                </div>
                            </div>
                            <div style="display:flex;align-items:center;gap:8px;">
                                <span class="svc-status-label" id="label-purchasing">Checking...</span>
                                <span class="service-tag">Port 8004</span>
                            </div>
                        </div>
                        <!-- Project -->
                        <div class="service-badge" id="svc-project">
                            <div class="service-info">
                                <span class="svc-dot svc-checking" id="dot-project"></span>
                                <div>
                                    <div class="service-name">mini-erp-project-service</div>
                                    <div class="service-desc">Project Management — Laravel Octane (Swoole)</div>
                                </div>
                            </div>
                            <div style="display:flex;align-items:center;gap:8px;">
                                <span class="svc-status-label" id="label-project">Checking...</span>
                                <span class="service-tag">Port 8005</span>
                            </div>
                        </div>
                        <!-- Auth -->
                        <div class="service-badge" id="svc-auth">
                            <div class="service-info">
                                <span class="svc-dot svc-checking" id="dot-auth"></span>
                                <div>
                                    <div class="service-name">mini-erp-auth-service</div>
                                    <div class="service-desc">Authentication — Laravel Passport (OAuth2)</div>
                                </div>
                            </div>
                            <div style="display:flex;align-items:center;gap:8px;">
                                <span class="svc-status-label" id="label-auth">Checking...</span>
                                <span class="service-tag">Port 8006</span>
                            </div>
                        </div>
                        <!-- Inventory -->
                        <div class="service-badge" id="svc-inventory">
                            <div class="service-info">
                                <span class="svc-dot svc-checking" id="dot-inventory"></span>
                                <div>
                                    <div class="service-name">mini-erp-inventory-service</div>
                                    <div class="service-desc">Inventory &amp; Warehouse Management — Laravel Octane</div>
                                </div>
                            </div>
                            <div style="display:flex;align-items:center;gap:8px;">
                                <span class="svc-status-label" id="label-inventory">Checking...</span>
                                <span class="service-tag">Port 8008</span>
                            </div>
                        </div>
                        <!-- System Administration -->
                        <div class="service-badge" id="svc-system">
                            <div class="service-info">
                                <span class="svc-dot svc-checking" id="dot-system"></span>
                                <div>
                                    <div class="service-name">mini-erp-system-service</div>
                                    <div class="service-desc">System Administration — Dynamic RBAC &amp; Approval Engine</div>
                                </div>
                            </div>
                            <div style="display:flex;align-items:center;gap:8px;">
                                <span class="svc-status-label" id="label-system">Checking...</span>
                                <span class="service-tag">Port 8009</span>
                            </div>
                        </div>
                        <!-- Face API -->
                        <div class="service-badge" id="svc-faceapi">
                            <div class="service-info">
                                <span class="svc-dot svc-checking" id="dot-faceapi"></span>
                                <div>
                                    <div class="service-name">mini-erp-face-api</div>
                                    <div class="service-desc">Python FastAPI dlib ML Engine</div>
                                </div>
                            </div>
                            <div style="display:flex;align-items:center;gap:8px;">
                                <span class="svc-status-label" id="label-faceapi">Checking...</span>
                                <span class="service-tag" style="color:#6EE7B7;border-color:rgba(16,185,129,0.3);">Port 5005</span>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- TAB 3: Build & Per-Service Ops -->
            <section id="tab-build" class="tab-pane">
                <div class="page-header">
                    <h2>Build &amp; <span>Per-Service Operations</span></h2>
                    <p>
                        Panduan menjalankan rebuild container secara independen dan menjalankan perintah artisan spesifik per service.
                    </p>
                </div>

                <div class="guide-box">
                    <div class="guide-step">
                        <div class="guide-step-title">1. Rebuild Service Tertentu (Zero-Downtime Service Lain)</div>
                        <div class="guide-step-desc">
                            Jika terdapat perubahan kode di salah satu domain, jalankan rebuild <strong>hanya untuk container tersebut</strong>:
                        </div>
                        <div class="code-block">
                            <span class="code-comment"># Rebuild HRM Service:</span><br>
                            <span class="code-cmd">docker compose up -d --build hrm-service</span><br><br>
                            <span class="code-comment"># Rebuild CRM Service:</span><br>
                            <span class="code-cmd">docker compose up -d --build crm-service</span><br><br>
                            <span class="code-comment"># Rebuild Finance Service:</span><br>
                            <span class="code-cmd">docker compose up -d --build finance-service</span><br><br>
                            <span class="code-comment"># Rebuild Purchasing Service:</span><br>
                            <span class="code-cmd">docker compose up -d --build purchasing-service</span><br><br>
                            <span class="code-comment"># Rebuild Project Management Service:</span><br>
                            <span class="code-cmd">docker compose up -d --build project-service</span><br><br>
                            <span class="code-comment"># Rebuild Inventory Service:</span><br>
                            <span class="code-cmd">docker compose up -d --build inventory-service</span><br><br>
                            <span class="code-comment"># Rebuild System Service (RBAC &amp; Approvals):</span><br>
                            <span class="code-cmd">docker compose up -d --build system-service</span><br><br>
                            <span class="code-comment"># Rebuild Auth Service:</span><br>
                            <span class="code-cmd">docker compose up -d --build auth-service</span><br><br>
                            <span class="code-comment"># Rebuild Gateway (setelah ubah default.conf):</span><br>
                            <span class="code-cmd">docker compose up -d --build gateway</span>
                        </div>
                    </div>

                    <div class="guide-step">
                        <div class="guide-step-title">2. Jalankan Perintah Artisan per Container</div>
                        <div class="guide-step-desc">
                            Eksekusi perintah Artisan / Migrasi langsung di dalam container service terkait:
                        </div>
                        <div class="code-block">
                            <span class="code-comment"># HRM: Run Migration / Swagger Gen:</span><br>
                            <span class="code-cmd">docker exec mini-erp-hrm-service php artisan migrate</span><br>
                            <span class="code-cmd">docker exec mini-erp-hrm-service php artisan l5-swagger:generate</span><br><br>
                            <span class="code-comment"># System Administration: Run Seeders:</span><br>
                            <span class="code-cmd">docker exec mini-erp-system-service php artisan db:seed --class=RolePermissionSeeder</span><br>
                            <span class="code-cmd">docker exec mini-erp-system-service php artisan db:seed --class=ApprovalChainSeeder</span><br><br>
                            <span class="code-comment"># Clear Route &amp; Cache:</span><br>
                            <span class="code-cmd">docker exec mini-erp-core-service php artisan route:clear</span><br>
                            <span class="code-cmd">docker exec mini-erp-core-service php artisan cache:clear</span>
                        </div>
                    </div>
                </div>
            </section>

            <!-- TAB 4: Seeders & Factories -->
            <section id="tab-seeder" class="tab-pane">
                <div class="page-header">
                    <h2>Seeders &amp; <span>Factory Guidelines</span></h2>
                    <p>
                        Panduan mengisi data sample realistis untuk seluruh 5 domain bisnis (HRM, CRM, Finance, Purchasing, Project Management).
                    </p>
                </div>

                <div class="guide-box">
                    <div class="guide-step">
                        <div class="guide-step-title">🔄 Fresh Migration &amp; Seed Seluruh Data Master</div>
                        <div class="guide-step-desc">
                            Jalankan fresh migration dan seeder lengkap untuk seluruh domain:
                        </div>
                        <div class="code-block">
                            <span class="code-cmd">docker exec mini-erp-core-service php artisan migrate:fresh --seed --seeder=MasterDataSeeder</span>
                        </div>
                    </div>

                    <div class="guide-step">
                        <div class="guide-step-title">📦 Lokasi &amp; Cakupan Factory per Domain</div>
                        <div class="code-block">
                            <span class="code-comment"># HRM Domain — database/factories/HRM/</span><br>
                            EmployeeFactory · DepartmentFactory · DesignationFactory · ShiftFactory<br>
                            AttendanceFactory · PayrollPeriodFactory · SalaryComponentFactory · LeaveTypeFactory<br><br>
                            <span class="code-comment"># CRM Domain — database/factories/CRM/</span><br>
                            CustomerFactory · LeadFactory · ProspectFactory<br><br>
                            <span class="code-comment"># Finance Domain — database/factories/Finance/</span><br>
                            AccountFactory · FinancialRecordFactory<br><br>
                            <span class="code-comment"># Purchasing Domain — database/factories/Purchasing/</span><br>
                            SupplierFactory · PurchaseRequestFactory · PurchaseOrderFactory · PurchaseInvoiceFactory<br><br>
                            <span class="code-comment"># Project Management — database/factories/Project/</span><br>
                            ProjectFactory · ProjectTaskFactory · ProjectTimesheetFactory · ProjectCostFactory
                        </div>
                    </div>

                    <div class="guide-step">
                        <div class="guide-step-title">🔬 Generate Data Spesifik via Laravel Tinker</div>
                        <div class="code-block">
                            <span class="code-comment"># Buka interactive Tinker:</span><br>
                            <span class="code-cmd">docker exec -it mini-erp-core-service php artisan tinker</span><br><br>
                            <span class="code-comment"># Buat 5 supplier baru:</span><br>
                            \Database\Factories\Purchasing\SupplierFactory::new()->count(5)->create();<br><br>
                            <span class="code-comment"># Buat 3 project dengan tasks-nya:</span><br>
                            \Database\Factories\Project\ProjectFactory::new()->count(3)->create();
                        </div>
                    </div>
                </div>
            </section>

            <!-- TAB 5: Architecture & Fault Isolation -->
            <section id="tab-architecture" class="tab-pane">
                <div class="page-header">
                    <h2>Architecture &amp; <span>Fault Tolerance</span></h2>
                    <p>
                        Konsep pemisahan domain, isolasi error runtime, serta arsitektur multi-repo Mini-ERP.
                    </p>
                </div>

                <div class="guide-box">
                    <div class="guide-step">
                        <div class="guide-step-title">🛡️ Isolasi Error Antar Service (Zero Blast Radius)</div>
                        <div class="guide-step-desc">
                            Setiap service terisolasi dalam container Swoole/Octane terpisah. Jika salah satu service mengalami fatal crash:
                        </div>
                        <div class="code-block">
                            <span class="code-comment">✓ Service lain (HRM, Finance, Purchasing, Inventory, dll.) TETAP BERJALAN 100% normal.</span><br>
                            <span class="code-comment">✓ Database PostgreSQL &amp; Redis Queue Worker tidak terganggu.</span><br>
                            <span class="code-comment">✓ Hanya rute endpoint service yang bermasalah yang terpengaruh hingga container tersebut di-restart.</span>
                        </div>
                    </div>

                    <div class="guide-step">
                        <div class="guide-step-title">📂 Transisi Modular Monorepo ke Multi-Repo</div>
                        <div class="guide-step-desc">
                            Folder <code>app/Domain/[DomainName]</code> dan rute <code>routes/api/[domain].php</code> dirancang independen. Jika di masa depan tim dibagi menjadi beberapa repositori Git terpisah, setiap domain dapat langsung dipindahkan ke repository tersendiri.
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <!-- Script for Tab Navigation & Service Health Polling -->
    <script>
        function switchTab(tabId) {
            // Remove active classes
            document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
            document.querySelectorAll('.tab-pane').forEach(pane => pane.classList.remove('active'));

            // Activate tab button
            const clickedBtn = Array.from(document.querySelectorAll('.tab-btn')).find(b => b.getAttribute('onclick')?.includes(tabId));
            if (clickedBtn) clickedBtn.classList.add('active');

            // Activate tab pane
            const targetPane = document.getElementById(tabId);
            if (targetPane) targetPane.classList.add('active');

            // Save active tab
            localStorage.setItem('minierp_active_doc_tab', tabId);
        }

        // Restore saved tab
        document.addEventListener('DOMContentLoaded', () => {
            const saved = localStorage.getItem('minierp_active_doc_tab');
            if (saved && document.getElementById(saved)) {
                switchTab(saved);
            }
        });

        // Health Checker
        const services = [
            { id: 'gateway', url: 'http://' + window.location.hostname + ':8000/up', name: 'Gateway' },
            { id: 'hrm', url: 'http://' + window.location.hostname + ':8001/up', name: 'HRM' },
            { id: 'crm', url: 'http://' + window.location.hostname + ':8002/up', name: 'CRM' },
            { id: 'finance', url: 'http://' + window.location.hostname + ':8003/up', name: 'Finance' },
            { id: 'purchasing', url: 'http://' + window.location.hostname + ':8004/up', name: 'Purchasing' },
            { id: 'project', url: 'http://' + window.location.hostname + ':8005/up', name: 'Project' },
            { id: 'auth', url: 'http://' + window.location.hostname + ':8006/up', name: 'Auth' },
            { id: 'inventory', url: 'http://' + window.location.hostname + ':8008/up', name: 'Inventory' },
            { id: 'system', url: 'http://' + window.location.hostname + ':8009/up', name: 'System' },
            { id: 'faceapi', url: 'http://' + window.location.hostname + ':5005/docs', name: 'Face API' }
        ];

        async function checkServiceHealth(svc) {
            const dot = document.getElementById('dot-' + svc.id);
            const label = document.getElementById('label-' + svc.id);
            if (!dot || !label) return;

            try {
                const controller = new AbortController();
                const timeoutId = setTimeout(() => controller.abort(), 3000);
                
                await fetch(svc.url, { 
                    method: 'GET', 
                    mode: 'no-cors',
                    signal: controller.signal 
                });
                
                clearTimeout(timeoutId);

                dot.className = 'svc-dot svc-running';
                label.className = 'svc-status-label lbl-running';
                label.innerText = 'Online';
            } catch (err) {
                dot.className = 'svc-dot svc-offline';
                label.className = 'svc-status-label lbl-offline';
                label.innerText = 'Offline';
            }
        }

        async function checkAllServices() {
            const timeEl = document.getElementById('status-last-checked');
            if (timeEl) timeEl.innerText = 'Checking...';

            await Promise.all(services.map(svc => checkServiceHealth(svc)));

            if (timeEl) {
                const now = new Date();
                timeEl.innerText = 'Updated: ' + now.toLocaleTimeString();
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            checkAllServices();
            setInterval(checkAllServices, 15000);
        });
    </script>
</body>
</html>
