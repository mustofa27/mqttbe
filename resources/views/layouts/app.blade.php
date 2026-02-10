<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'ICMQTT')</title>
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
            background: linear-gradient(135deg, #ffffff 0%, #f9fafb 100%);
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            padding: 0.75rem 1.5rem;
            position: sticky;
            top: 0;
            z-index: 1000;
            border-bottom: 1px solid #e5e7eb;
        }

        .navbar-content {
            max-width: 100%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
        }

        .navbar-brand {
            font-size: 1.3rem;
            font-weight: 700;
            color: #1f2937;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .navbar-nav {
            display: flex;
            gap: 0.5rem;
            align-items: center;
            margin-left: auto;
        }

        .navbar-nav a, .navbar-nav button {
            color: #6b7280;
            text-decoration: none;
            font-size: 0.9rem;
            transition: all 0.2s ease;
            background: none;
            border: none;
            cursor: pointer;
            padding: 0.4rem 0.8rem;
        }

        .navbar-nav a:hover, .navbar-nav button:hover {
            color: #4f46e5;
            background: rgba(79, 70, 229, 0.08);
            border-radius: 6px;
        }

        .main-wrapper {
            display: flex;
            flex: 1;
        }

        .sidebar-toggle {
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #f3f4f6, #e5e7eb);
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 1.1rem;
            cursor: pointer;
            color: #4b5563;
            padding: 0.5rem 0.75rem;
            transition: all 0.2s ease;
            width: 38px;
            height: 38px;
            min-width: 38px;
        }

        .sidebar-toggle:hover {
            background: linear-gradient(135deg, #e5e7eb, #d1d5db);
            color: #1f2937;
            border-color: #9ca3af;
        }

        .sidebar-toggle:active {
            transform: scale(0.95);
        }

        .user-menu-container {
            position: relative;
        }

        .user-menu-toggle {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.4rem 0.8rem;
            background: rgba(79, 70, 229, 0.08);
            border: 1px solid rgba(79, 70, 229, 0.2);
            border-radius: 8px;
            cursor: pointer;
            color: #4f46e5;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .user-menu-toggle:hover {
            background: rgba(79, 70, 229, 0.15);
            border-color: rgba(79, 70, 229, 0.4);
        }

        .user-menu-icon {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 0.9rem;
            font-weight: 700;
        }

        .user-menu-dropdown {
            position: absolute;
            right: 0;
            top: calc(100% + 0.5rem);
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            min-width: 200px;
            z-index: 1001;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: all 0.2s ease;
        }

        .user-menu-dropdown.active {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .user-menu-dropdown-header {
            padding: 0.75rem 1rem;
            border-bottom: 1px solid #f3f4f6;
        }

        .user-menu-dropdown-name {
            font-weight: 700;
            color: #1f2937;
            font-size: 0.9rem;
        }

        .user-menu-dropdown-email {
            font-size: 0.8rem;
            color: #9ca3af;
            margin-top: 0.25rem;
        }

        .user-menu-dropdown-items {
            padding: 0.5rem 0;
        }

        .user-menu-dropdown a, .user-menu-dropdown form {
            display: block;
        }

        .user-menu-dropdown-item {
            padding: 0.7rem 1rem;
            color: #6b7280;
            text-decoration: none;
            transition: all 0.2s ease;
            border-left: 3px solid transparent;
            font-size: 0.9rem;
        }

        .user-menu-dropdown a.user-menu-dropdown-item:hover {
            background: #f9fafb;
            color: #4f46e5;
            border-left-color: #4f46e5;
            padding-left: 1.2rem;
        }

        .user-menu-dropdown form button.user-menu-dropdown-item {
            width: 100%;
            text-align: left;
            background: none;
            border: none;
            cursor: pointer;
            font-family: inherit;
        }

        .user-menu-dropdown form button.user-menu-dropdown-item:hover {
            background: #f9fafb;
            color: #dc2626;
            border-left-color: #dc2626;
            padding-left: 1.2rem;
        }

        .sidebar {
            width: 250px;
            background: linear-gradient(180deg, #ffffff 0%, #f9fafb 100%);
            border-right: 1px solid #e5e7eb;
            padding: 1.5rem 0;
            overflow-y: auto;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 2px 0 8px rgba(0, 0, 0, 0.04);
        }

        .sidebar.collapsed {
            width: 0;
            padding: 0;
            border-right: none;
            overflow: hidden;
            box-shadow: none;
        }
        }

        .sidebar-menu {
            list-style: none;
        }

        .sidebar-menu li {
            margin: 0;
        }

        .sidebar-menu a {
            display: block;
            padding: 0.7rem 1.5rem;
            color: #6b7280;
            text-decoration: none;
            transition: all 0.2s ease;
            border-left: 3px solid transparent;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .sidebar-menu a:hover {
            background: #f3f4f6;
            color: #4f46e5;
            border-left-color: #4f46e5;
            padding-left: 1.7rem;
        }

        .sidebar-menu a.active {
            background: linear-gradient(90deg, #eef2ff 0%, #f9fafb 100%);
            color: #4f46e5;
            border-left-color: #4f46e5;
            font-weight: 600;
            padding-left: 1.7rem;
        }

        .sidebar-section {
            margin: 0;
            padding: 1rem 0;
        }

        .sidebar-section-title {
            padding: 1rem 1.5rem 0.5rem;
            font-weight: 700;
            font-size: 0.7rem;
            text-transform: uppercase;
            color: #9ca3af;
            letter-spacing: 0.8px;
            margin-top: 0.5rem;
        }

        .sidebar-section:first-child .sidebar-section-title {
            margin-top: 0;
        }

        .content-wrapper {
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow-y: auto;
            background: #f9fafb;
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
                position: fixed;
                left: 0;
                top: 0;
                height: 100%;
                width: 250px;
                z-index: 999;
                border-bottom: none;
                border-right: 1px solid #e0e0e0;
                padding-top: 3.5rem;
                overflow-y: auto;
            }

            .sidebar.collapsed {
                width: 0;
                padding: 0;
                border-right: none;
            }

            .main-wrapper {
                flex-direction: column;
            }

            .container {
                padding: 1rem;
            }

            .sidebar-overlay {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(0, 0, 0, 0.3);
                z-index: 998;
                transition: opacity 0.3s ease;
            }

            .sidebar-overlay.active {
                display: block;
            }
        }
    </style>
</head>
<body>
    <!-- Top Navigation -->
    <nav class="navbar">
        <div class="navbar-content">
            <button class="sidebar-toggle" id="sidebarToggle" aria-label="Toggle sidebar" title="Toggle Menu">
                ☰
            </button>
            <a href="{{ url('/') }}" class="navbar-brand">📡 ICMQTT</a>
            <div class="navbar-nav">
                @auth
                    <div class="user-menu-container">
                        <button class="user-menu-toggle" id="userMenuToggle">
                            <div class="user-menu-icon">{{ substr(Auth::user()->name, 0, 1) }}</div>
                            <span>{{ Auth::user()->name }}</span>
                            <span style="font-size: 0.8rem;">▼</span>
                        </button>
                        <div class="user-menu-dropdown" id="userMenuDropdown">
                            <div class="user-menu-dropdown-header">
                                <div class="user-menu-dropdown-name">{{ Auth::user()->name }}</div>
                                <div class="user-menu-dropdown-email">{{ Auth::user()->email }}</div>
                            </div>
                            <div class="user-menu-dropdown-items">
                                <a href="{{ route('profile') }}" class="user-menu-dropdown-item">👤 My Profile</a>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="user-menu-dropdown-item">🚪 Logout</button>
                                </form>
                            </div>
                        </div>
                    </div>
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
                    <a href="{{ route('usage.dashboard') }}" class="@if(Route::currentRouteName() === 'usage.dashboard') active @endif">
                        📈 Usage & Analytics
                    </a>
                </li>
                <li>
                    <a href="{{ route('analytics.dashboard') }}" class="@if(Route::currentRouteName() === 'analytics.dashboard') active @endif">
                        📊 Advanced Analytics
                    </a>
                </li>
                <li>
                    <a href="{{ route('subscription.index') }}" class="@if(strpos(Route::currentRouteName(), 'subscription.') !== false) active @endif">
                        💳 Subscription
                    </a>
                </li>
                <li>
                    <a href="{{ route('api-keys.index') }}" class="@if(Route::currentRouteName() === 'api-keys.index') active @endif">
                        🔑 API Keys
                    </a>
                </li>
                <li>
                    <a href="{{ route('webhooks.index') }}" class="@if(Route::currentRouteName() === 'webhooks.index') active @endif">
                        🪝 Webhooks
                    </a>
                </li>
                <li>
                    <a href="{{ route('alerts.index') }}" class="@if(Route::currentRouteName() === 'alerts.index') active @endif">
                        🔔 Alerts
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


                @auth
                    @if (auth()->user()->is_admin)
                        <li class="sidebar-section">
                            <div class="sidebar-section-title">Admin</div>
                        </li>
                        <li>
                            <a href="{{ route('admin.users.index') }}" class="@if(strpos(Route::currentRouteName(), 'admin.users') !== false) active @endif">
                                👥 Users
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.subscription-plans.index') }}" class="@if(strpos(Route::currentRouteName(), 'admin.subscription-plans') !== false) active @endif">
                                📋 Plans
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.subscription-plans.statistics') }}" class="@if(Route::currentRouteName() === 'admin.subscription-plans.statistics') active @endif">
                                📊 Plan Stats
                            </a>
                        </li>
                    @endif
                @endauth
            </ul>
        </aside>

        <!-- Sidebar Overlay -->
        <div class="sidebar-overlay" id="sidebarOverlay"></div>

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

        // Sidebar toggle functionality
        document.addEventListener('DOMContentLoaded', () => {
            const sidebarToggle = document.getElementById('sidebarToggle');
            const sidebar = document.querySelector('.sidebar');
            const sidebarOverlay = document.getElementById('sidebarOverlay');

            if (!sidebarToggle || !sidebar) {
                return;
            }

            // Restore sidebar state from localStorage
            const isSidebarCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
            const isMobile = window.innerWidth <= 768;

            if (isSidebarCollapsed) {
                sidebar.classList.add('collapsed');
                if (isMobile) {
                    sidebarOverlay.classList.remove('active');
                }
            }

            // Toggle sidebar
            sidebarToggle.addEventListener('click', (e) => {
                e.stopPropagation();
                const isCollapsed = sidebar.classList.toggle('collapsed');
                localStorage.setItem('sidebarCollapsed', isCollapsed);
                
                // Only show overlay on mobile
                if (window.innerWidth <= 768) {
                    sidebarOverlay.classList.toggle('active');
                }
            });

            // Close sidebar when clicking overlay
            sidebarOverlay.addEventListener('click', () => {
                sidebar.classList.add('collapsed');
                sidebarOverlay.classList.remove('active');
                localStorage.setItem('sidebarCollapsed', 'true');
            });

            // Close sidebar when clicking a link (mobile only)
            const sidebarLinks = sidebar.querySelectorAll('a');
            sidebarLinks.forEach(link => {
                link.addEventListener('click', () => {
                    if (window.innerWidth <= 768) {
                        sidebar.classList.add('collapsed');
                        sidebarOverlay.classList.remove('active');
                        localStorage.setItem('sidebarCollapsed', 'true');
                    }
                });
            });

            // Handle window resize
            window.addEventListener('resize', () => {
                if (window.innerWidth > 768) {
                    // On desktop, restore saved state
                    const shouldBeCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
                    if (shouldBeCollapsed) {
                        sidebar.classList.add('collapsed');
                    } else {
                        sidebar.classList.remove('collapsed');
                    }
                    sidebarOverlay.classList.remove('active');
                } else {
                    // On mobile, reset overlay state
                    sidebarOverlay.classList.remove('active');
                }
            });
        });

        // User menu dropdown functionality
        document.addEventListener('DOMContentLoaded', () => {
            const userMenuToggle = document.getElementById('userMenuToggle');
            const userMenuDropdown = document.getElementById('userMenuDropdown');

            if (!userMenuToggle || !userMenuDropdown) {
                return;
            }

            // Toggle dropdown
            userMenuToggle.addEventListener('click', (e) => {
                e.stopPropagation();
                userMenuDropdown.classList.toggle('active');
            });

            // Close dropdown when clicking outside
            document.addEventListener('click', (e) => {
                if (!userMenuToggle.contains(e.target) && !userMenuDropdown.contains(e.target)) {
                    userMenuDropdown.classList.remove('active');
                }
            });

            // Close dropdown when clicking a link
            const dropdownLinks = userMenuDropdown.querySelectorAll('a, button');
            dropdownLinks.forEach(link => {
                link.addEventListener('click', () => {
                    userMenuDropdown.classList.remove('active');
                });
            });
        });
    </script>
</body>
</html>
