<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'MQTT Dashboard')</title>
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html, body {
            height: 100%;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: #f5f5f5;
            color: #333;
            display: flex;
            flex-direction: column;
        }

        .navbar {
            background: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 1rem 2rem;
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .navbar-content {
            max-width: 100%;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .navbar-brand {
            font-size: 1.5rem;
            font-weight: bold;
            color: #333;
            text-decoration: none;
        }

        .navbar-nav {
            display: flex;
            gap: 2rem;
            align-items: center;
        }

        .navbar-nav a, .navbar-nav button {
            color: #333;
            text-decoration: none;
            font-size: 0.95rem;
            transition: color 0.3s;
            background: none;
            border: none;
            cursor: pointer;
        }

        .navbar-nav a:hover, .navbar-nav button:hover {
            color: #667eea;
        }

        .main-wrapper {
            display: flex;
            flex: 1;
        }

        .sidebar {
            width: 250px;
            background: white;
            border-right: 1px solid #e0e0e0;
            padding: 1.5rem 0;
            overflow-y: auto;
        }

        .sidebar-menu {
            list-style: none;
        }

        .sidebar-menu li {
            margin: 0;
        }

        .sidebar-menu a {
            display: block;
            padding: 0.75rem 1.5rem;
            color: #666;
            text-decoration: none;
            transition: all 0.3s;
            border-left: 3px solid transparent;
            font-size: 0.95rem;
        }

        .sidebar-menu a:hover {
            background: #f5f5f5;
            color: #667eea;
            border-left-color: #667eea;
        }

        .sidebar-menu a.active {
            background: #f0f0ff;
            color: #667eea;
            border-left-color: #667eea;
            font-weight: 600;
        }

        .sidebar-section {
            margin: 0;
            padding: 1rem 0;
        }

        .sidebar-section-title {
            padding: 0.5rem 1.5rem;
            font-weight: 600;
            font-size: 0.75rem;
            text-transform: uppercase;
            color: #999;
            letter-spacing: 0.5px;
        }

        .content-wrapper {
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow-y: auto;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem;
            width: 100%;
        }

        .btn {
            display: inline-block;
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            font-size: 0.95rem;
            transition: all 0.3s;
        }

        .btn-primary {
            background: #667eea;
            color: white;
        }

        .btn-primary:hover {
            background: #5568d3;
        }

        .btn-secondary {
            background: #f0f0f0;
            color: #333;
        }

        .btn-secondary:hover {
            background: #e0e0e0;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #333;
        }

        input[type="text"],
        input[type="email"],
        input[type="password"],
        textarea,
        select {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #e6e6e6;
            border-radius: 6px;
            font-size: 1rem;
            font-family: inherit;
            transition: border-color 0.15s, box-shadow 0.15s;
        }

        input[type="text"]:focus,
        input[type="email"]:focus,
        input[type="password"]:focus,
        textarea:focus,
        select:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.06);
        }

        .error {
            color: #dc3545;
            font-size: 0.9rem;
            margin-top: 0.25rem;
        }

        .card {
            background: white;
            border-radius: 8px;
            padding: 1.5rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 1.5rem;
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            text-align: center;
        }

        .stat-card h3 {
            color: #999;
            font-size: 0.9rem;
            text-transform: uppercase;
            margin-bottom: 0.5rem;
        }

        .stat-card .value {
            font-size: 2.5rem;
            font-weight: bold;
            color: #667eea;
        }

        .password-toggle-wrapper {
            position: relative;
            display: flex;
            align-items: center;
            width: 100%;
        }

        .password-toggle-wrapper input {
            width: 100%;
            padding-right: 2.5rem;
        }

        .password-toggle-btn {
            position: absolute;
            right: 0.75rem;
            background: none;
            border: none;
            cursor: pointer;
            color: #999;
            font-size: 1rem;
            padding: 0.25rem 0.5rem;
            transition: color 0.2s;
        }

        .password-toggle-btn:hover {
            color: #667eea;
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                border-right: none;
                border-bottom: 1px solid #e0e0e0;
            }

            .main-wrapper {
                flex-direction: column;
            }

            .container {
                padding: 1rem;
            }
        }
    </style>
</head>
<body>
    <!-- Top Navigation -->
    <nav class="navbar">
        <div class="navbar-content">
            <a href="{{ url('/') }}" class="navbar-brand">📡 MQTT Dashboard</a>
            <div class="navbar-nav">
                @auth
                    <span>{{ Auth::user()->name }}</span>
                    <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                        @csrf
                        <button type="submit">Logout</button>
                    </form>
                @else
                    <a href="{{ route('login') }}">Login</a>
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="btn btn-secondary">Register</a>
                    @endif
                @endauth
            </div>
        </div>
    </nav>

    <!-- Main Content Area -->
    <div class="main-wrapper">
        <!-- Sidebar Menu -->
        <aside class="sidebar">
            <ul class="sidebar-menu">
                <li class="sidebar-section">
                    <div class="sidebar-section-title">Dashboard</div>
                </li>
                <li>
                    <a href="{{ route('dashboard') }}" class="@if(Route::currentRouteName() === 'dashboard') active @endif">
                        📊 Dashboard
                    </a>
                </li>
                <li>
                    <a href="{{ route('profile') }}" class="@if(Route::currentRouteName() === 'profile') active @endif">
                        👤 My Profile
                    </a>
                </li>

                <li class="sidebar-section">
                    <div class="sidebar-section-title">Management</div>
                </li>
                <li>
                    <a href="{{ route('projects.index') }}" class="@if(strpos(Route::currentRouteName(), 'projects') !== false) active @endif">
                        📁 Projects
                    </a>
                </li>
                <li>
                    <a href="{{ route('devices.index') }}" class="@if(strpos(Route::currentRouteName(), 'devices') !== false) active @endif">
                        🔧 Devices
                    </a>
                </li>
                <li>
                    <a href="{{ route('topics.index') }}" class="@if(strpos(Route::currentRouteName(), 'topics') !== false) active @endif">
                        📬 Topics
                    </a>
                </li>
                <li>
                    <a href="{{ route('permissions.index') }}" class="@if(strpos(Route::currentRouteName(), 'permissions') !== false) active @endif">
                        🔐 Permissions
                    </a>
                </li>
            </ul>
        </aside>

        <!-- Main Content -->
        <div class="content-wrapper">
            <div class="container">
                @if ($errors->any())
                    <div style="background: #fee; padding: 1rem; border-radius: 4px; margin-bottom: 1rem; color: #c33;">
                        <strong>Error!</strong>
                        <ul style="margin: 0.5rem 0 0 1.5rem;">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if (session('success'))
                    <div style="background: #efe; padding: 1rem; border-radius: 4px; margin-bottom: 1rem; color: #3c3;">
                        {{ session('success') }}
                    </div>
                @endif

                @yield('content')
            </div>
        </div>
    </div>

    <script>
        function togglePasswordVisibility(inputId) {
            const input = document.getElementById(inputId);
            const btn = document.querySelector(`[data-toggle="${inputId}"]`);
            if (input.type === 'password') {
                input.type = 'text';
                btn.textContent = '🙈';
            } else {
                input.type = 'password';
                btn.textContent = '👁️';
            }
        }
    </script>
</body>
</html>
