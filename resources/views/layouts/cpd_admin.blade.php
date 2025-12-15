<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'IDM CPD Admin')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <style>
        :root {
            --idm-primary: #007a3d;
            --idm-primary-dark: #005627;
            --idm-accent: #f4b000;
            --idm-bg: #f3f7f5;
            --idm-border: #d1d5db;
            --idm-text: #0b1a13;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            background: var(--idm-bg);
            color: var(--idm-text);
        }

        /* Top green bar */
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
            flex-direction: column;
        }
        .navbar .brand span.suffix {
            font-weight: 400;
            opacity: 0.9;
            font-size: 11px;
        }
        .navbar nav {
            display: flex;
            align-items: center;
            gap: 18px;
            font-size: 13px;
        }
        .navbar nav a {
            color: #e5e7eb;
            text-decoration: none;
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

        .page-wrap {
            padding: 20px 10px 30px;
        }
        .container-cpd-admin {
            max-width: 1380px;
            width: 98%;
            margin: 0 auto;
            background: #ffffff;
            padding: 20px 24px 24px;
            border-radius: 10px;
            border: 1px solid var(--idm-border);
            box-shadow: 0 10px 25px rgba(15, 23, 42, 0.06);
        }

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
    </style>

    @yield('head')
</head>
<body>
<header class="navbar">
    <div class="brand">
        <span>IDM CPD</span>
        <span class="suffix">Admin – Centre for Continuing Professional Development</span>
    </div>
    <nav>
        <a href="{{ route('admin.cpd.dashboard') }}"
           class="{{ request()->routeIs('admin.cpd.dashboard') ? 'active' : '' }}">
            Dashboard
        </a>
        <a href="{{ route('admin.cpd.courses.index') }}"
           class="{{ request()->routeIs('admin.cpd.courses.*') ? 'active' : '' }}">
            Courses
        </a>
        <a href="{{ route('cpd.sessions.index') }}"
           class="{{ request()->routeIs('cpd.sessions.*') ? 'active' : '' }}">
            Sessions
        </a>
        {{-- You can add Enrolments / Payments menus later --}}
    </nav>
</header>

<div class="page-wrap">
    <div class="container-cpd-admin">
        @if (session('status'))
            <div class="flash-success">
                {{ session('status') }}
            </div>
        @endif

        @yield('content')
    </div>
</div>

@stack('scripts')
</body>
</html>
