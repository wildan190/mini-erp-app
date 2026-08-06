<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mini-ERP Core Engine & Microservices API</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">

    <!-- Inline Modern Vanilla CSS -->
    <style>
        :root {
            --bg-main: #0B0F19;
            --bg-card: #111827;
            --bg-card-hover: #1F2937;
            --border-color: #1F2937;
            --border-highlight: #374151;
            --accent-blue: #3B82F6;
            --accent-purple: #8B5CF6;
            --accent-emerald: #10B981;
            --accent-amber: #F59E0B;
            --text-main: #F9FAFB;
            --text-muted: #9CA3AF;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Outfit', sans-serif;
            background-color: var(--bg-main);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            background-image: 
                radial-gradient(circle at 15% 15%, rgba(59, 130, 246, 0.12) 0%, transparent 40%),
                radial-gradient(circle at 85% 85%, rgba(139, 92, 246, 0.12) 0%, transparent 40%);
        }

        .container {
            max-width: 1120px;
            margin: 0 auto;
            padding: 2.5rem 1.5rem;
            width: 100%;
        }

        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-bottom: 2rem;
            border-bottom: 1px solid var(--border-color);
            margin-bottom: 3rem;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .brand-logo {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, var(--accent-blue), var(--accent-purple));
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 1.2rem;
            color: #fff;
            box-shadow: 0 0 15px rgba(59, 130, 246, 0.4);
        }

        .brand-text h1 {
            font-size: 1.25rem;
            font-weight: 700;
            letter-spacing: -0.02em;
            background: linear-gradient(to right, #ffffff, #9CA3AF);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .brand-text p {
            font-size: 0.75rem;
            color: var(--text-muted);
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: rgba(16, 185, 129, 0.1);
            border: 1px solid rgba(16, 185, 129, 0.3);
            color: var(--accent-emerald);
            padding: 0.4rem 0.85rem;
            border-radius: 9999px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .pulse-dot {
            width: 8px;
            height: 8px;
            background-color: var(--accent-emerald);
            border-radius: 50%;
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }

        .svc-dot-wrap {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 14px;
            height: 14px;
            margin-right: 4px;
        }

        .svc-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            display: inline-block;
            transition: all 0.3s ease;
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

        .svc-status-label {
            font-size: 0.75rem;
            font-weight: 600;
            padding: 2px 8px;
            border-radius: 4px;
            transition: all 0.3s ease;
        }

        .lbl-running {
            color: #34d399;
            background: rgba(16, 185, 129, 0.12);
        }

        .lbl-offline {
            color: #f87171;
            background: rgba(239, 68, 68, 0.12);
        }

        .lbl-checking {
            color: #fbbf24;
            background: rgba(245, 158, 11, 0.12);
        }
            box-shadow: 0 0 8px var(--accent-emerald);
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.5; transform: scale(1.2); }
        }

        .hero {
            margin-bottom: 3rem;
        }

        .hero h2 {
            font-size: 2.75rem;
            font-weight: 700;
            line-height: 1.2;
            margin-bottom: 1rem;
            letter-spacing: -0.03em;
        }

        .hero h2 span {
            background: linear-gradient(135deg, #60A5FA, #A78BFA);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .hero p {
            font-size: 1.1rem;
            color: var(--text-muted);
            max-width: 720px;
            line-height: 1.6;
        }

        .nav-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            margin-top: 2rem;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.6rem;
            padding: 0.85rem 1.5rem;
            border-radius: 12px;
            font-size: 0.95rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.25s ease;
            cursor: pointer;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--accent-blue), #2563EB);
            color: #fff;
            box-shadow: 0 4px 14px rgba(37, 99, 235, 0.35);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(37, 99, 235, 0.5);
        }

        .btn-purple {
            background: linear-gradient(135deg, var(--accent-purple), #7C3AED);
            color: #fff;
            box-shadow: 0 4px 14px rgba(124, 58, 237, 0.35);
        }

        .btn-purple:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(124, 58, 237, 0.5);
        }

        .btn-emerald {
            background: linear-gradient(135deg, var(--accent-emerald), #059669);
            color: #fff;
            box-shadow: 0 4px 14px rgba(16, 185, 129, 0.35);
        }

        .btn-emerald:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(16, 185, 129, 0.5);
        }

        .btn-outline {
            background: rgba(31, 41, 55, 0.6);
            color: var(--text-main);
            border: 1px solid var(--border-highlight);
            backdrop-filter: blur(8px);
        }

        .btn-outline:hover {
            background: var(--bg-card-hover);
            border-color: var(--text-muted);
            transform: translateY(-2px);
        }

        .grid-2 {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2.5rem;
        }

        .card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 16px;
            padding: 1.75rem;
            transition: border-color 0.25s ease;
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
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
        }

        .icon-blue { background: rgba(59, 130, 246, 0.15); color: #60A5FA; }
        .icon-amber { background: rgba(245, 158, 11, 0.15); color: #FBBF24; }

        .card-title {
            font-size: 1.15rem;
            font-weight: 600;
        }

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
            font-size: 0.9rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px dashed rgba(255, 255, 255, 0.07);
        }

        .specs-label {
            color: var(--text-muted);
        }

        .specs-value {
            font-family: 'JetBrains Mono', monospace;
            font-weight: 500;
            color: #E5E7EB;
        }

        .services-list {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .service-badge {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: rgba(17, 24, 39, 0.8);
            border: 1px solid var(--border-color);
            padding: 0.65rem 0.9rem;
            border-radius: 10px;
        }

        .service-info {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .service-name {
            font-weight: 600;
            font-size: 0.88rem;
        }

        .service-desc {
            font-size: 0.73rem;
            color: var(--text-muted);
        }

        .service-tag {
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.75rem;
            padding: 0.2rem 0.5rem;
            border-radius: 6px;
            background: rgba(59, 130, 246, 0.1);
            color: #93C5FD;
            border: 1px solid rgba(59, 130, 246, 0.2);
        }

        .service-tag-purple {
            background: rgba(139, 92, 246, 0.1);
            color: #C084FC;
            border-color: rgba(139, 92, 246, 0.2);
        }

        .service-tag-emerald {
            background: rgba(16, 185, 129, 0.1);
            color: #6EE7B7;
            border-color: rgba(16, 185, 129, 0.2);
        }

        .guide-box {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 16px;
            padding: 1.75rem;
            margin-bottom: 3rem;
        }

        .guide-box h3 {
            font-size: 1.2rem;
            font-weight: 700;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .guide-step {
            margin-bottom: 1.5rem;
        }

        .guide-step-title {
            font-weight: 600;
            font-size: 0.95rem;
            color: #F3F4F6;
            margin-bottom: 0.4rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .guide-step-desc {
            font-size: 0.88rem;
            color: var(--text-muted);
            line-height: 1.5;
            margin-bottom: 0.5rem;
        }

        .code-block {
            background: #090D16;
            border: 1px solid var(--border-color);
            border-radius: 10px;
            padding: 1rem 1.25rem;
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.85rem;
            color: #34D399;
            overflow-x: auto;
            line-height: 1.6;
        }

        .code-comment {
            color: #6B7280;
        }

        .code-cmd {
            color: #60A5FA;
        }

        footer {
            border-top: 1px solid var(--border-color);
            padding: 1.5rem 0;
            text-align: center;
            font-size: 0.85rem;
            color: var(--text-muted);
        }

        footer a {
            color: var(--accent-blue);
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <header>
            <div class="brand">
                <div class="brand-logo">ERP</div>
                <div class="brand-text">
                    <h1>Mini-ERP Backend Engine</h1>
                    <p>Microservices Architecture Ready</p>
                </div>
            </div>
            <div class="status-badge">
                <span class="pulse-dot"></span>
                <span>System Online (PHP 8.5 Octane)</span>
            </div>
        </header>

        <!-- Hero Section -->
        <div class="hero">
            <h2>Enterprise Core Backend & <span>Microservices Engine</span></h2>
            <p>
                Platform Backend Mini-ERP berperforma tinggi ditenagai oleh PHP 8.5, Laravel Octane (Swoole), PostgreSQL, Redis, dan Microservice Machine Learning Python (dlib Face Recognition).
            </p>

            <!-- Navigation Buttons -->
            <div class="nav-buttons">
                <a href="{{ url('/api/documentation') }}" class="btn btn-primary">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    <span>Swagger API Docs</span>
                </a>
                <a href="{{ url('/log-viewer') }}" class="btn btn-emerald">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    <span>Log Viewer (Opcodes)</span>
                </a>
                <a href="{{ url('/horizon') }}" class="btn btn-purple">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                    <span>Laravel Horizon</span>
                </a>
                <a href="{{ url('/up') }}" target="_blank" class="btn btn-outline">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    <span>Health Check</span>
                </a>
            </div>
        </div>

        <!-- Specifications & Microservices Grid -->
        <div class="grid-2">
            <!-- Minimum Server Specifications -->
            <div class="card">
                <div class="card-header">
                    <div class="card-icon icon-amber">🖥️</div>
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

            <!-- Microservice Architecture Status (All 10 Containers) -->
            <div class="card">
                <div class="card-header">
                    <div class="card-icon icon-blue">🛰️</div>
                    <div class="card-title">All Registered Microservices & Infrastructure</div>
                </div>
                <div class="services-list" id="services-status-list">
                    <!-- Gateway -->
                    <div class="service-badge" id="svc-gateway">
                        <div class="service-info">
                            <div class="svc-dot-wrap">
                                <span class="svc-dot svc-checking" id="dot-gateway" title="Checking..."></span>
                            </div>
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
                            <div class="svc-dot-wrap">
                                <span class="svc-dot svc-checking" id="dot-hrm" title="Checking..."></span>
                            </div>
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
                            <div class="svc-dot-wrap">
                                <span class="svc-dot svc-checking" id="dot-crm" title="Checking..."></span>
                            </div>
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
                            <div class="svc-dot-wrap">
                                <span class="svc-dot svc-checking" id="dot-finance" title="Checking..."></span>
                            </div>
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
                            <div class="svc-dot-wrap">
                                <span class="svc-dot svc-checking" id="dot-purchasing" title="Checking..."></span>
                            </div>
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
                    <div class="service-badge" id="svc-project">
                        <div class="service-info">
                            <div class="svc-dot-wrap">
                                <span class="svc-dot svc-checking" id="dot-project" title="Checking..."></span>
                            </div>
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
                            <div class="svc-dot-wrap">
                                <span class="svc-dot svc-checking" id="dot-auth" title="Checking..."></span>
                            </div>
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
                            <div class="svc-dot-wrap">
                                <span class="svc-dot svc-checking" id="dot-inventory" title="Checking..."></span>
                            </div>
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
                    <!-- Horizon -->
                    <div class="service-badge">
                        <div class="service-info">
                            <div class="svc-dot-wrap">
                                <span class="svc-dot svc-running" style="background:#a78bfa;box-shadow:0 0 8px #a78bfa88;"></span>
                            </div>
                            <div>
                                <div class="service-name">mini-erp-horizon</div>
                                <div class="service-desc">Redis Queue Worker Monitoring</div>
                            </div>
                        </div>
                        <span class="service-tag service-tag-purple">Worker</span>
                    </div>
                    <!-- Face API -->
                    <div class="service-badge" id="svc-faceapi">
                        <div class="service-info">
                            <div class="svc-dot-wrap">
                                <span class="svc-dot svc-checking" id="dot-faceapi" title="Checking..."></span>
                            </div>
                            <div>
                                <div class="service-name">mini-erp-face-api</div>
                                <div class="service-desc">Python FastAPI dlib ML Engine</div>
                            </div>
                        </div>
                        <div style="display:flex;align-items:center;gap:8px;">
                            <span class="svc-status-label" id="label-faceapi">Checking...</span>
                            <span class="service-tag service-tag-emerald">Port 5005</span>
                        </div>
                    </div>
                </div>
                <!-- Last checked timestamp -->
                <div style="margin-top:12px;font-size:0.75rem;color:var(--text-muted);display:flex;align-items:center;gap:6px;">
                    <span id="status-last-checked">🔄 Checking all services...</span>
                    <button onclick="checkAllServices()" style="background:rgba(99,102,241,0.15);color:#818cf8;border:none;border-radius:6px;padding:2px 10px;font-size:0.72rem;cursor:pointer;transition:background 0.2s;" onmouseover="this.style.background='rgba(99,102,241,0.3)'" onmouseout="this.style.background='rgba(99,102,241,0.15)'">↺ Refresh</button>
                </div>
            </div>
        </div>

        <!-- Comprehensive Build, Maintenance & Architecture Guide -->
        <div class="guide-box">
            <h3>📖 Panduan Lengkap Build & Perintah Per-Service (Seluruh 5 Domain)</h3>
            <p style="color: var(--text-muted); font-size: 0.95rem; line-height: 1.6; margin-bottom: 1.5rem;">
                Setiap domain microservice (<strong>HRM, CRM, Finance, Purchasing, Project, Inventory, dan Auth</strong>) memiliki konteks eksekusi terpisah. Anda dapat me-rebuild, mengeksekusi artisan, atau menangani error secara terisolasi untuk service tertentu tanpa risiko mempengaruhi service lainnya.
            </p>

            <!-- Step 1: Rebuild Per Service -->
            <div class="guide-step">
                <div class="guide-step-title">1. Rebuild & Restart Service Spesifik (Zero-Downtime Service Lain)</div>
                <div class="guide-step-desc">
                    Jika terdapat perubahan kode di salah satu domain, jalankan rebuild <strong>hanya untuk container tersebut</strong>. Container lain tetap aktif 100%:
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
                    <span class="code-comment"># Rebuild Inventory &amp; Warehouse Service:</span><br>
                    <span class="code-cmd">docker compose up -d --build inventory-service</span><br><br>
                    <span class="code-comment"># Rebuild Auth Service (login / register / logout / Passport):</span><br>
                    <span class="code-cmd">docker compose up -d --build auth-service</span><br><br>
                    <span class="code-comment"># Rebuild Gateway (Nginx) — setelah ubah default.conf:</span><br>
                    <span class="code-cmd">docker compose up -d --build gateway</span>
                </div>
            </div>

            <!-- Step 2: Perintah Artisan per Service -->
            <div class="guide-step">
                <div class="guide-step-title">2. Jalankan Perintah Artisan / Migrasi Spesifik per Container Service</div>
                <div class="guide-step-desc">
                    Gunakan `docker exec` untuk menjalankan perintah artisan hanya di dalam container service yang bersangkutan:
                </div>
                <div class="code-block">
                    <span class="code-comment"># HRM: Run Migration / Swagger Gen:</span><br>
                    <span class="code-cmd">docker exec mini-erp-hrm-service php artisan migrate</span><br>
                    <span class="code-cmd">docker exec mini-erp-hrm-service php artisan l5-swagger:generate</span><br><br>
                    <span class="code-comment"># CRM: Run Migration / Swagger Gen:</span><br>
                    <span class="code-cmd">docker exec mini-erp-crm-service php artisan migrate</span><br>
                    <span class="code-cmd">docker exec mini-erp-crm-service php artisan l5-swagger:generate</span><br><br>
                    <span class="code-comment"># Finance: Run Migration / Swagger Gen:</span><br>
                    <span class="code-cmd">docker exec mini-erp-finance-service php artisan migrate</span><br>
                    <span class="code-cmd">docker exec mini-erp-finance-service php artisan l5-swagger:generate</span><br><br>
                    <span class="code-comment"># Purchasing: Run Migration / Swagger Gen:</span><br>
                    <span class="code-cmd">docker exec mini-erp-purchasing-service php artisan migrate</span><br>
                    <span class="code-cmd">docker exec mini-erp-purchasing-service php artisan l5-swagger:generate</span><br><br>
                    <span class="code-comment"># Project: Run Migration / Swagger Gen:</span><br>
                    <span class="code-cmd">docker exec mini-erp-project-service php artisan migrate</span><br>
                    <span class="code-cmd">docker exec mini-erp-project-service php artisan l5-swagger:generate</span><br><br>
                    <span class="code-comment"># Inventory: Run Migration / Cache Clear:</span><br>
                    <span class="code-cmd">docker exec mini-erp-inventory-service php artisan migrate</span><br>
                    <span class="code-cmd">docker exec mini-erp-inventory-service php artisan route:clear</span><br>
                    <span class="code-cmd">docker exec mini-erp-inventory-service php artisan cache:clear</span><br><br>
                    <span class="code-comment"># Auth: Regenerate Passport Keys / Clear Cache:</span><br>
                    <span class="code-cmd">docker exec mini-erp-auth-service php artisan passport:install --force</span><br>
                    <span class="code-cmd">docker exec mini-erp-auth-service php artisan route:clear</span><br>
                    <span class="code-cmd">docker exec mini-erp-auth-service php artisan cache:clear</span>
                </div>
            </div>

            <!-- Step 3: Isolasi Error -->
            <div class="guide-step">
                <div class="guide-step-title">3. Isolasi Error & Fault Tolerance Antar Service</div>
                <div class="guide-step-desc">
                    Setiap service terisolasi dalam container Swoole/Octane terpisah. Jika satu service crash (contoh: CRM mengalami fatal error):
                </div>
                <div class="code-block">
                    <span class="code-comment">✓ Service HRM, Finance, Purchasing, & Project Management TETAP BERJALAN 100% Normal.</span><br>
                    <span class="code-comment">✓ Database PostgreSQL & Horizon Worker tidak terpengaruh.</span><br>
                    <span class="code-comment">✓ Hanya URL rute `/api/platform/crm/*` yang melempar error sampai container crm-service di-restart.</span>
                </div>
            </div>

            <!-- Step 4: Multi-Repo Strategy -->
            <div class="guide-step">
                <div class="guide-step-title">4. Strategi Migrasi ke Multi-Repo (Terpisah Repositori Git)</div>
                <div class="guide-step-desc">
                    Arsitektur `app/Domain/[NamaDomain]` saat ini dirancang agar mudah diekstrak ke Git Repository terpisah kapan saja:
                </div>
                <div class="code-block">
                    <span class="code-comment">• Monorepo (Saat Ini): Paling efisien untuk dev awal, kecepatan iterasi, & sharing library tanpa overhead banyak repo.</span><br>
                    <span class="code-comment">• Multi-Repo (Masa Depan): Jika divisi developer sudah terpisah, cukup pindahkan folder domain (misal: `app/Domain/CRM`) & route file (`routes/api/crm.php`) ke repository Git terpisah untuk tiap tim.</span>
                </div>
            </div>
        </div>

        <!-- Seeder & Factory Guide -->
        <div class="guide-box">
            <h3>🌱 Panduan Seeder & Factory — Mengisi Data Testing Seluruh Domain</h3>
            <p style="color: var(--text-muted); font-size: 0.95rem; line-height: 1.6; margin-bottom: 1.5rem;">
                MasterDataSeeder mengisi data sample realistis untuk <strong>semua 5 domain</strong> sekaligus (HRM, CRM, Finance, Purchasing, Project Management). Factory tersedia di <code style="background: #090D16; padding: 2px 6px; border-radius: 4px; font-family: 'JetBrains Mono', monospace; font-size: 0.8rem;">database/factories/</code> tiap domain.
            </p>

            <!-- Seeder: Run All -->
            <div class="guide-step">
                <div class="guide-step-title">🔄 Reset & Seed Seluruh Data Baru (Fresh Start)</div>
                <div class="guide-step-desc">
                    Drop semua tabel, jalankan ulang semua migrasi, dan isi data sample untuk seluruh 5 domain sekaligus:
                </div>
                <div class="code-block">
                    <span class="code-comment"># Fresh migration + seeding seluruh domain (HRM, CRM, Finance, Purchasing, Project):</span><br>
                    <span class="code-cmd">docker exec mini-erp-hrm-service php artisan migrate:fresh --seed --seeder=MasterDataSeeder</span>
                </div>
            </div>

            <!-- Seeder: Add Data Only -->
            <div class="guide-step">
                <div class="guide-step-title">➕ Tambah Data Sample Tanpa Reset Database</div>
                <div class="guide-step-desc">
                    Tambahkan data sample baru tanpa menghapus data yang sudah ada (idempotent untuk data master seperti akun keuangan):
                </div>
                <div class="code-block">
                    <span class="code-comment"># Tambah data sample ke database yang sudah ada:</span><br>
                    <span class="code-cmd">docker exec mini-erp-hrm-service php artisan db:seed --class=MasterDataSeeder</span>
                </div>
            </div>

            <!-- Factory Coverage -->
            <div class="guide-step">
                <div class="guide-step-title">📦 Cakupan Factory per Domain</div>
                <div class="guide-step-desc">
                    Factory tersedia untuk seluruh model utama di semua domain:
                </div>
                <div class="code-block">
                    <span class="code-comment"># HRM Domain — database/factories/HRM/</span><br>
                    EmployeeFactory · DepartmentFactory · DesignationFactory · ShiftFactory<br>
                    AttendanceFactory · PayrollPeriodFactory · SalaryComponentFactory<br>
                    OfficeLocationFactory · LeaveTypeFactory · LeaveRequestFactory<br><br>
                    <span class="code-comment"># CRM Domain — database/factories/CRM/</span><br>
                    CustomerFactory · LeadFactory · ProspectFactory<br><br>
                    <span class="code-comment"># Finance Domain — database/factories/Finance/</span><br>
                    AccountFactory · FinancialRecordFactory<br><br>
                    <span class="code-comment"># Purchasing Domain — database/factories/Purchasing/</span><br>
                    SupplierFactory · PurchaseRequestFactory · PurchaseOrderFactory · PurchaseInvoiceFactory<br><br>
                    <span class="code-comment"># Project Management Domain — database/factories/Project/</span><br>
                    ProjectFactory · ProjectTaskFactory · ProjectTimesheetFactory · ProjectCostFactory
                </div>
            </div>

            <!-- Factory Usage in Tinker -->
            <div class="guide-step">
                <div class="guide-step-title">🔬 Buat Data Spesifik via Laravel Tinker</div>
                <div class="guide-step-desc">
                    Gunakan Tinker untuk membuat data domain tertentu secara granular tanpa menjalankan ulang seeder penuh:
                </div>
                <div class="code-block">
                    <span class="code-comment"># Buka Tinker di container HRM:</span><br>
                    <span class="code-cmd">docker exec -it mini-erp-hrm-service php artisan tinker</span><br><br>
                    <span class="code-comment"># Di dalam Tinker — buat 5 supplier baru:</span><br>
                    \Database\Factories\Purchasing\SupplierFactory::new()->count(5)->create();<br><br>
                    <span class="code-comment"># Buat 3 project dengan tasks-nya sekaligus:</span><br>
                    \Database\Factories\Project\ProjectFactory::new()->count(3)->create();
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <div class="container">
            <p>Mini-ERP Enterprise Engine &copy; 2026. Built with Laravel 11, Swoole Octane & Python ML.</p>
        </div>
    </footer>
    <script>
        const services = [
            { id: 'gateway', url: 'http://' + window.location.hostname + ':8000/up', name: 'Gateway' },
            { id: 'hrm', url: 'http://' + window.location.hostname + ':8001/up', name: 'HRM' },
            { id: 'crm', url: 'http://' + window.location.hostname + ':8002/up', name: 'CRM' },
            { id: 'finance', url: 'http://' + window.location.hostname + ':8003/up', name: 'Finance' },
            { id: 'purchasing', url: 'http://' + window.location.hostname + ':8004/up', name: 'Purchasing' },
            { id: 'project', url: 'http://' + window.location.hostname + ':8005/up', name: 'Project' },
            { id: 'auth', url: 'http://' + window.location.hostname + ':8006/up', name: 'Auth' },
            { id: 'inventory', url: 'http://' + window.location.hostname + ':8008/up', name: 'Inventory' },
            { id: 'faceapi', url: 'http://' + window.location.hostname + ':5005/docs', name: 'Face API' }
        ];

        async function checkServiceHealth(svc) {
            const dot = document.getElementById('dot-' + svc.id);
            const label = document.getElementById('label-' + svc.id);
            
            if (!dot || !label) return;

            try {
                const controller = new AbortController();
                const timeoutId = setTimeout(() => controller.abort(), 3000);
                
                const res = await fetch(svc.url, { 
                    method: 'GET', 
                    mode: 'no-cors',
                    signal: controller.signal 
                });
                
                clearTimeout(timeoutId);

                dot.className = 'svc-dot svc-running';
                dot.title = 'Octane Running (Online)';
                label.className = 'svc-status-label lbl-running';
                label.innerText = 'Octane Running';
            } catch (err) {
                dot.className = 'svc-dot svc-offline';
                dot.title = 'Service Offline / Not Running';
                label.className = 'svc-status-label lbl-offline';
                label.innerText = 'Not Running';
            }
        }

        async function checkAllServices() {
            const timeEl = document.getElementById('status-last-checked');
            if (timeEl) timeEl.innerText = '🔄 Checking status...';

            await Promise.all(services.map(svc => checkServiceHealth(svc)));

            if (timeEl) {
                const now = new Date();
                timeEl.innerText = 'Updated: ' + now.toLocaleTimeString();
            }
        }

        // Initial check on page load & interval polling
        document.addEventListener('DOMContentLoaded', () => {
            checkAllServices();
            setInterval(checkAllServices, 15000);
        });
    </script>
</body>
</html>
