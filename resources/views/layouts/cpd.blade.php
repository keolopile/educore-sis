<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'IDM CPD')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <style>
        :root {
            /* IDM palette */
            --idm-primary: #007a3d;        /* rich green */
            --idm-primary-dark: #005627;   /* darker green */
            --idm-accent: #f4b000;         /* gold accent */
            --idm-bg: #f3f7f5;             /* soft light green/grey */
            --idm-border: #d1d5db;
            --idm-text: #0b1a13;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            background: var(--idm-bg);
            color: var(--idm-text);
        }

        /* ─── Top IDM navbar (green gradient) ─────────────────────────── */
        .navbar {
            background: linear-gradient(90deg, #005627, #00964a);
            color: #fff;
            padding: 10px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 2px 4px rgba(15, 23, 42, 0.35);
        }
        .navbar .brand {
            font-weight: 700;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            font-size: 14px;
            display: flex;
            align-items: baseline;
            gap: 4px;
        }
        .navbar .brand span.suffix {
            font-weight: 400;
            opacity: 0.9;
            font-size: 12px;
        }
        .navbar nav {
            display: flex;
            align-items: center;
        }
        .navbar nav a {
            color: #e5e7eb;
            text-decoration: none;
            margin-left: 18px;
            font-size: 13px;
            padding-bottom: 3px;
            border-bottom: 2px solid transparent;
        }
        .navbar nav a:hover {
            color: #ffffff;
            border-bottom-color: rgba(255, 255, 255, 0.4);
        }
        .navbar nav a.active {
            color: #ffffff;
            border-bottom-color: var(--idm-accent);
            font-weight: 600;
        }
        .navbar .nav-user {
            margin-left: 18px;
            font-size: 12px;
            opacity: 0.9;
        }

        /* ─── Page shell & card (wider) ───────────────────────────────── */
        .page-wrap {
            padding: 20px 10px 30px;  /* smaller side padding */
        }
        .container {
            max-width: 1380px;        /* wider content area */
            width: 98%;
            margin: 0 auto;
            background: #ffffff;
            padding: 20px 24px 24px;
            border-radius: 10px;
            border: 1px solid var(--idm-border);
            box-shadow: 0 10px 25px rgba(15, 23, 42, 0.06);
        }
        .container h1 {
            margin-top: 4px;
            font-size: 22px;
        }

        /* ─── Tables – more visual polish ─────────────────────────────── */
        table {
            border-collapse: collapse;
            width: 100%;
            font-size: 13px;
        }
        th, td {
            border: 1px solid var(--idm-border);
            padding: 6px 8px;
            text-align: left;
        }
        th {
            background-color: #f9fafb;
            font-weight: 600;
        }
        tbody tr:nth-child(even) {
            background-color: #f9fafb;        /* zebra stripes */
        }
        tbody tr:hover {
            background-color: #ecfdf3;        /* soft green hover */
        }

        /* ─── Buttons ─────────────────────────────────────────────────── */
        .btn {
            display: inline-block;
            padding: 6px 10px;
            font-size: 12px;
            border-radius: 4px;
            border: none;
            cursor: pointer;
            text-decoration: none;
        }
        .btn-primary {
            background-color: var(--idm-primary);
            color: #fff;
        }
        .btn-primary:hover {
            background-color: var(--idm-primary-dark);
        }
        .btn-secondary {
            background-color: #4b5563;
            color: #fff;
        }
        .btn-secondary:hover {
            background-color: #374151;
        }

        /* ─── Flash messages ──────────────────────────────────────────── */
        .flash-success {
            color: #166534;
            background: #dcfce7;
            border: 1px solid #6ee7b7;
            padding: 8px 10px;
            border-radius: 4px;
            margin-bottom: 12px;
            font-size: 13px;
        }
        .flash-error {
            color: #991b1b;
            background: #fee2e2;
            border: 1px solid #fecaca;
            padding: 8px 10px;
            border-radius: 4px;
            margin-bottom: 12px;
            font-size: 13px;
        }

        .small-muted {
            font-size: 12px;
            color: #6b7280;
        }

        /* ─── Kill DataTables controls on CPD layout only ─────────────── */
        #cpd-page .dataTables_length,
        #cpd-page .dataTables_filter,
        #cpd-page .dataTables_info,
        #cpd-page .dataTables_paginate {
            display: none !important;
        }
    </style>

    @yield('head')
</head>
<body>
    {{-- Top CPD navbar --}}
    <header class="navbar">
        <div class="brand">
            <span>IDM</span>
            <span class="suffix">Centre for Continuing Professional Development</span>
        </div>
        <nav>
            {{-- Public catalogue --}}
            <a href="{{ route('cpd.sessions.index') }}"
               class="{{ request()->routeIs('cpd.sessions.*') ? 'active' : '' }}">
                Courses
            </a>

            {{-- My courses + auth-aware links --}}
            @auth
                <a href="{{ route('cpd.my_courses') }}"
                   class="{{ request()->routeIs('cpd.my_courses') || request()->routeIs('cpd.learn.*') ? 'active' : '' }}">
                    My CPD courses
                </a>
                <span class="nav-user">
                    {{ auth()->user()->name ?? auth()->user()->email }}
                </span>
            @else
                <a href="{{ route('login') }}"
                   class="{{ request()->routeIs('login') ? 'active' : '' }}">
                    Sign in
                </a>
            @endauth
        </nav>
    </header>

    {{-- Main content card --}}
    <div class="page-wrap" id="cpd-page">
        <div class="container">
            @yield('content')
        </div>
    </div>

    @stack('scripts')
</body>
</html>

