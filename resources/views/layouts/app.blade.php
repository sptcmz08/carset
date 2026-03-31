<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'SetCar') — ระบบจัดขบวนรถ</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.3/dist/cdn.min.js"></script>
    <style>
        :root {
            --navy: #0f172a;
            --navy-light: #1e293b;
            --navy-card: #1e293b;
            --navy-hover: #334155;
            --amber: #f59e0b;
            --amber-light: #fbbf24;
            --amber-dark: #d97706;
            --surface: #0f172a;
            --surface-card: rgba(30, 41, 59, 0.8);
            --text: #f1f5f9;
            --text-muted: #94a3b8;
            --green: #22c55e;
            --green-bg: rgba(34, 197, 94, 0.15);
            --yellow: #eab308;
            --yellow-bg: rgba(234, 179, 8, 0.15);
            --orange: #f97316;
            --orange-bg: rgba(249, 115, 22, 0.15);
            --red: #ef4444;
            --red-bg: rgba(239, 68, 68, 0.15);
            --blue: #3b82f6;
            --blue-bg: rgba(59, 130, 246, 0.15);
            --sidebar-w: 260px;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Sarabun', 'Inter', sans-serif;
            background: var(--surface);
            color: var(--text);
            min-height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            position: fixed;
            top: 0; left: 0;
            width: var(--sidebar-w);
            height: 100vh;
            background: linear-gradient(180deg, #0f172a 0%, #1a2744 100%);
            border-right: 1px solid rgba(255,255,255,0.06);
            z-index: 100;
            display: flex;
            flex-direction: column;
            transition: transform 0.3s;
        }

        .sidebar-brand {
            padding: 24px 20px;
            border-bottom: 1px solid rgba(255,255,255,0.06);
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .sidebar-brand .logo {
            width: 42px; height: 42px;
            background: linear-gradient(135deg, var(--amber), var(--amber-dark));
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 20px; color: #000;
            box-shadow: 0 4px 15px rgba(245, 158, 11, 0.3);
        }

        .sidebar-brand h1 {
            font-size: 18px; font-weight: 700;
            letter-spacing: 1px;
            background: linear-gradient(135deg, var(--amber-light), var(--amber));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .sidebar-brand small {
            font-size: 11px; color: var(--text-muted);
            display: block; margin-top: 2px;
        }

        .sidebar-nav { padding: 16px 12px; flex: 1; }

        .sidebar-nav .nav-label {
            font-size: 10px; text-transform: uppercase;
            color: var(--text-muted); letter-spacing: 2px;
            padding: 12px 12px 6px; font-weight: 600;
        }

        .nav-link {
            display: flex; align-items: center; gap: 12px;
            padding: 12px 16px; border-radius: 10px;
            color: var(--text-muted); text-decoration: none;
            font-size: 14px; font-weight: 500;
            transition: all 0.2s;
            margin-bottom: 2px;
        }
        .nav-link:hover { background: rgba(255,255,255,0.05); color: var(--text); }
        .nav-link.active {
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.15), rgba(245, 158, 11, 0.05));
            color: var(--amber);
            border: 1px solid rgba(245, 158, 11, 0.2);
        }
        .nav-link i { width: 20px; text-align: center; font-size: 15px; }
        .nav-link .badge {
            margin-left: auto;
            background: var(--red);
            color: #fff;
            font-size: 10px;
            padding: 2px 8px;
            border-radius: 99px;
            font-weight: 700;
        }

        /* Main Content */
        .main-content {
            margin-left: var(--sidebar-w);
            min-height: 100vh;
        }

        .topbar {
            padding: 16px 32px;
            display: flex; align-items: center; justify-content: space-between;
            border-bottom: 1px solid rgba(255,255,255,0.06);
            backdrop-filter: blur(20px);
            background: rgba(15, 23, 42, 0.8);
            position: sticky; top: 0; z-index: 50;
        }

        .topbar h2 { font-size: 20px; font-weight: 600; }
        .topbar .datetime {
            color: var(--text-muted); font-size: 13px;
            display: flex; align-items: center; gap: 8px;
        }

        .page-content { padding: 28px 32px; }

        /* Cards */
        .card {
            background: var(--surface-card);
            border: 1px solid rgba(255,255,255,0.06);
            border-radius: 14px;
            padding: 24px;
            backdrop-filter: blur(20px);
            transition: all 0.3s;
        }
        .card:hover { border-color: rgba(255,255,255,0.12); }

        .card-header {
            display: flex; align-items: center; justify-content: space-between;
            margin-bottom: 20px;
        }
        .card-header h3 {
            font-size: 16px; font-weight: 600;
            display: flex; align-items: center; gap: 8px;
        }

        /* Stat Cards */
        .stat-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 24px; }

        .stat-card {
            background: var(--surface-card);
            border: 1px solid rgba(255,255,255,0.06);
            border-radius: 14px;
            padding: 20px 24px;
            backdrop-filter: blur(20px);
            position: relative;
            overflow: hidden;
            transition: all 0.3s;
        }
        .stat-card:hover { transform: translateY(-2px); border-color: rgba(255,255,255,0.12); }
        .stat-card::after {
            content: '';
            position: absolute;
            top: 0; right: 0;
            width: 80px; height: 80px;
            border-radius: 50%;
            opacity: 0.05;
            transform: translate(30%, -30%);
        }
        .stat-card.amber::after { background: var(--amber); }
        .stat-card.green::after { background: var(--green); }
        .stat-card.blue::after { background: var(--blue); }
        .stat-card.red::after { background: var(--red); }
        .stat-card.orange::after { background: var(--orange); }

        .stat-card .stat-icon {
            width: 44px; height: 44px;
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 18px; margin-bottom: 14px;
        }
        .stat-card.amber .stat-icon { background: rgba(245,158,11,0.15); color: var(--amber); }
        .stat-card.green .stat-icon { background: var(--green-bg); color: var(--green); }
        .stat-card.blue .stat-icon { background: var(--blue-bg); color: var(--blue); }
        .stat-card.red .stat-icon { background: var(--red-bg); color: var(--red); }
        .stat-card.orange .stat-icon { background: var(--orange-bg); color: var(--orange); }

        .stat-card .stat-value { font-size: 28px; font-weight: 700; line-height: 1; }
        .stat-card .stat-label { font-size: 13px; color: var(--text-muted); margin-top: 4px; }

        /* Tables */
        .data-table { width: 100%; border-collapse: collapse; }
        .data-table thead th {
            text-align: left; padding: 12px 16px;
            font-size: 12px; text-transform: uppercase;
            color: var(--text-muted); letter-spacing: 1px;
            border-bottom: 1px solid rgba(255,255,255,0.06);
            font-weight: 600;
        }
        .data-table tbody td {
            padding: 14px 16px; font-size: 14px;
            border-bottom: 1px solid rgba(255,255,255,0.04);
        }
        .data-table tbody tr { transition: background 0.2s; }
        .data-table tbody tr:hover { background: rgba(255,255,255,0.03); }

        /* Badges */
        .badge {
            display: inline-flex; align-items: center; gap: 4px;
            padding: 4px 12px; border-radius: 99px;
            font-size: 12px; font-weight: 600;
        }
        .badge-green { background: var(--green-bg); color: var(--green); }
        .badge-yellow { background: var(--yellow-bg); color: var(--yellow); }
        .badge-orange { background: var(--orange-bg); color: var(--orange); }
        .badge-red { background: var(--red-bg); color: var(--red); }
        .badge-blue { background: var(--blue-bg); color: var(--blue); }
        .badge-amber { background: rgba(245,158,11,0.15); color: var(--amber); }

        /* Buttons */
        .btn {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 10px 20px; border-radius: 10px;
            font-size: 14px; font-weight: 600;
            border: none; cursor: pointer;
            transition: all 0.2s;
            font-family: inherit;
            text-decoration: none;
        }
        .btn-primary {
            background: linear-gradient(135deg, var(--amber), var(--amber-dark));
            color: #000;
            box-shadow: 0 4px 15px rgba(245, 158, 11, 0.3);
        }
        .btn-primary:hover { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(245, 158, 11, 0.4); }
        .btn-secondary {
            background: rgba(255,255,255,0.06);
            color: var(--text); border: 1px solid rgba(255,255,255,0.1);
        }
        .btn-secondary:hover { background: rgba(255,255,255,0.1); }
        .btn-danger {
            background: var(--red-bg); color: var(--red);
            border: 1px solid rgba(239, 68, 68, 0.2);
        }
        .btn-sm { padding: 6px 14px; font-size: 12px; }

        /* Forms */
        .form-group { margin-bottom: 16px; }
        .form-label { display: block; font-size: 13px; color: var(--text-muted); margin-bottom: 6px; font-weight: 500; }
        .form-input, .form-select, .form-textarea {
            width: 100%; padding: 10px 14px;
            background: rgba(255,255,255,0.06);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 10px; color: var(--text);
            font-size: 14px; font-family: inherit;
            transition: border-color 0.2s;
        }
        .form-input:focus, .form-select:focus, .form-textarea:focus {
            outline: none; border-color: var(--amber);
            box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.1);
        }
        .form-select { cursor: pointer; }
        .form-select option { background: var(--navy-light); color: var(--text); }

        /* Modal */
        .modal-overlay {
            position: fixed; inset: 0;
            background: rgba(0,0,0,0.6);
            backdrop-filter: blur(4px);
            z-index: 200;
            display: flex; align-items: center; justify-content: center;
            padding: 20px;
        }
        .modal-content {
            background: var(--navy-light);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 16px;
            width: 100%; max-width: 560px;
            max-height: 90vh; overflow-y: auto;
            box-shadow: 0 25px 50px rgba(0,0,0,0.5);
        }
        .modal-header {
            padding: 20px 24px;
            border-bottom: 1px solid rgba(255,255,255,0.06);
            display: flex; align-items: center; justify-content: space-between;
        }
        .modal-header h3 { font-size: 18px; font-weight: 600; }
        .modal-body { padding: 24px; }
        .modal-footer {
            padding: 16px 24px;
            border-top: 1px solid rgba(255,255,255,0.06);
            display: flex; justify-content: flex-end; gap: 12px;
        }
        .modal-close {
            background: none; border: none; color: var(--text-muted);
            font-size: 20px; cursor: pointer; padding: 4px;
        }
        .modal-close:hover { color: var(--text); }

        /* Alert items */
        .alert-item {
            display: flex; align-items: flex-start; gap: 12px;
            padding: 14px 16px; border-radius: 10px;
            margin-bottom: 8px;
            transition: all 0.2s;
        }
        .alert-item.warning { background: var(--yellow-bg); border-left: 3px solid var(--yellow); }
        .alert-item.danger { background: var(--red-bg); border-left: 3px solid var(--red); }
        .alert-item .alert-icon { font-size: 16px; margin-top: 2px; }
        .alert-item .alert-text { flex: 1; }
        .alert-item .alert-vehicle { font-weight: 600; font-size: 13px; }
        .alert-item .alert-message { font-size: 13px; color: var(--text-muted); margin-top: 2px; }

        /* Grids */
        .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
        .grid-3 { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; }

        /* Scrollbar */
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 99px; }

        /* Flash message */
        .flash-message {
            padding: 12px 20px; border-radius: 10px;
            margin-bottom: 16px; font-size: 14px;
            display: flex; align-items: center; gap: 8px;
            animation: fadeIn 0.3s;
        }
        .flash-success { background: var(--green-bg); color: var(--green); border: 1px solid rgba(34,197,94,0.2); }

        @keyframes fadeIn { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }

        /* Progress bar */
        .progress-bar {
            height: 6px; background: rgba(255,255,255,0.06);
            border-radius: 99px; overflow: hidden;
        }
        .progress-bar .fill {
            height: 100%; border-radius: 99px;
            transition: width 0.6s ease;
        }

        /* Filter tabs */
        .filter-tabs {
            display: flex; gap: 8px; flex-wrap: wrap;
        }
        .filter-tab {
            padding: 8px 16px; border-radius: 10px;
            font-size: 13px; font-weight: 500;
            border: 1px solid rgba(255,255,255,0.1);
            background: transparent; color: var(--text-muted);
            cursor: pointer; transition: all 0.2s;
            text-decoration: none;
        }
        .filter-tab:hover { background: rgba(255,255,255,0.05); color: var(--text); }
        .filter-tab.active {
            background: rgba(245,158,11,0.15);
            color: var(--amber);
            border-color: rgba(245,158,11,0.3);
        }

        /* Custom icon circle */
        .vehicle-icon {
            width: 36px; height: 36px;
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 14px;
        }
        .vehicle-icon.bus { background: var(--blue-bg); color: var(--blue); }
        .vehicle-icon.van { background: var(--green-bg); color: var(--green); }
        .vehicle-icon.minibus { background: rgba(245,158,11,0.15); color: var(--amber); }

        /* Responsive */
        .mobile-toggle { display: none; }
        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.open { transform: translateX(0); }
            .main-content { margin-left: 0; }
            .mobile-toggle {
                display: flex; align-items: center; justify-content: center;
                width: 40px; height: 40px; border-radius: 10px;
                background: rgba(255,255,255,0.06); border: none;
                color: var(--text); font-size: 18px; cursor: pointer;
            }
            .stat-grid { grid-template-columns: 1fr 1fr; }
            .grid-2, .grid-3 { grid-template-columns: 1fr; }
            .page-content { padding: 16px; }
        }

        /* Empty state */
        .empty-state {
            text-align: center; padding: 48px 24px;
            color: var(--text-muted);
        }
        .empty-state i { font-size: 48px; margin-bottom: 16px; opacity: 0.3; }
        .empty-state p { font-size: 14px; }
    </style>
</head>
<body x-data="{ sidebarOpen: false }">

    <!-- Sidebar -->
    <aside class="sidebar" :class="{ 'open': sidebarOpen }" @click.away="sidebarOpen = false">
        <div class="sidebar-brand">
            <div class="logo"><i class="fas fa-bus"></i></div>
            <div>
                <h1>SetCar</h1>
                <small>Fleet Management System</small>
            </div>
        </div>
        <nav class="sidebar-nav">
            <div class="nav-label">เมนูหลัก</div>
            <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i class="fas fa-chart-pie"></i> แดชบอร์ด
            </a>
            <a href="{{ route('daily-plan') }}" class="nav-link {{ request()->routeIs('daily-plan') ? 'active' : '' }}">
                <i class="fas fa-calendar-day"></i> แผนงานรายวัน
            </a>
            <a href="{{ route('fleet') }}" class="nav-link {{ request()->routeIs('fleet') ? 'active' : '' }}">
                <i class="fas fa-train-subway"></i> ฐานข้อมูลขบวน
                @php
                    $allTrainSets = \App\Models\TrainSet::all();
                    $alertCount = $allTrainSets->filter(fn ($trainSet) => in_array($trainSet->health_status, ['warning', 'out_of_service'], true))->count();
                @endphp
                @if($alertCount > 0)
                    <span class="badge">{{ $alertCount }}</span>
                @endif
            </a>
            <a href="{{ route('reports') }}" class="nav-link {{ request()->routeIs('reports') ? 'active' : '' }}">
                <i class="fas fa-chart-bar"></i> รายงาน
            </a>

            <div class="nav-label" style="margin-top: 24px;">ข้อมูล</div>
            <div style="padding: 12px 16px;">
                <div style="font-size: 12px; color: var(--text-muted); margin-bottom: 8px;">สถานะขบวนทั้งหมด</div>
                @php $allSets = $allTrainSets ?? \App\Models\TrainSet::all(); @endphp
                <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                    <span class="badge badge-green" style="font-size: 11px;">🟢 {{ $allSets->where('health_status', 'available')->count() }}</span>
                    <span class="badge badge-yellow" style="font-size: 11px;">🟡 {{ $allSets->where('health_status', 'warning')->count() }}</span>
                    <span class="badge badge-red" style="font-size: 11px;">🔴 {{ $allSets->where('health_status', 'out_of_service')->count() }}</span>
                </div>
            </div>
        </nav>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <div class="topbar">
            <div style="display: flex; align-items: center; gap: 16px;">
                <button class="mobile-toggle" @click="sidebarOpen = !sidebarOpen">
                    <i class="fas fa-bars"></i>
                </button>
                <h2>@yield('title', 'แดชบอร์ด')</h2>
            </div>
            <div class="datetime">
                <i class="far fa-clock"></i>
                <span>{{ \Carbon\Carbon::now()->locale('th')->translatedFormat('l d F Y') }}</span>
            </div>
        </div>

        <div class="page-content">
            @if(session('success'))
                <div class="flash-message flash-success">
                    <i class="fas fa-check-circle"></i> {{ session('success') }}
                </div>
            @endif

            @yield('content')
        </div>
    </main>

</body>
</html>
