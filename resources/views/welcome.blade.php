<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ICMQTT - IoT Message Infrastructure</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=manrope:400,500,600,700,800" rel="stylesheet" />

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/welcome.css'])
    @else
        <link rel="stylesheet" href="{{ asset('css/welcome.css') }}">
    @endif
</head>
<body class="landing-page">
    <header class="landing-header">
        <div class="landing-shell nav-shell">
            <a href="{{ route('home') }}" class="brand-mark" aria-label="ICMQTT home">
                <span class="brand-icon">◆</span>
                <span class="brand-text">ICMQTT</span>
            </a>

            @if (Route::has('login'))
                <button class="menu-toggle" type="button" aria-expanded="false" aria-controls="primary-menu">
                    Menu
                </button>
                <nav id="primary-menu" class="menu-list">
                    <a href="{{ route('legal.policies') }}">Syarat & Kebijakan</a>
                    <a href="{{ route('contact.show') }}">Contact</a>
                    @auth
                        <a href="{{ route('home.dashboard') }}">Dashboard</a>
                        <form method="POST" action="{{ route('logout') }}" class="menu-inline-form">
                            @csrf
                            <button type="submit">Logout</button>
                        </form>
                    @else
                        <a href="{{ route('login') }}">Login</a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="menu-register">Register</a>
                        @endif
                    @endauth
                </nav>
            @endif
        </div>
    </header>

    <main>
        <section class="hero-block">
            <div class="landing-shell hero-grid">
                <div class="hero-copy">
                    <p class="hero-kicker">Reliable IoT Platform</p>
                    <h1>Ship MQTT-powered products with operational confidence.</h1>
                    <p>
                        Provision projects, stream telemetry, apply policy-driven limits,
                        and scale from prototype to production without changing your stack.
                    </p>

                    <div class="hero-actions">
                        @guest
                            <a href="{{ route('register') }}" class="action-btn action-primary">Get Started Free</a>
                            <a href="{{ route('login') }}" class="action-btn action-ghost">Sign In</a>
                        @else
                            <a href="{{ route('home.dashboard') }}" class="action-btn action-primary">Open Dashboard</a>
                        @endguest
                    </div>
                </div>

                <div class="hero-panel" aria-hidden="true">
                    <div class="signal-card">
                        <p>Realtime Delivery</p>
                        <strong>99.97%</strong>
                        <span>last 30 days</span>
                    </div>
                    <div class="signal-card">
                        <p>Avg Processing Latency</p>
                        <strong>39 ms</strong>
                        <span>message to consumer</span>
                    </div>
                    <div class="signal-card highlight">
                        <p>Connected Devices</p>
                        <strong>10K+</strong>
                        <span>under active monitoring</span>
                    </div>
                </div>
            </div>
        </section>

        <section class="trust-strip">
            <div class="landing-shell trust-items">
                <span>Message Retention Controls</span>
                <span>Webhooks & API Access</span>
                <span>Usage Quotas Per Plan</span>
                <span>Analytics & Alerting</span>
            </div>
        </section>

        <section class="feature-block">
            <div class="landing-shell">
                <div class="section-heading">
                    <p>Core Capabilities</p>
                    <h2>Everything teams need to operate IoT workloads</h2>
                </div>

                <div class="feature-grid">
                    <article class="feature-tile">
                        <h3>Realtime Topic Routing</h3>
                        <p>Publish and subscribe to high-volume topics with predictable throughput and clear project boundaries.</p>
                    </article>
                    <article class="feature-tile">
                        <h3>Device & Permission Controls</h3>
                        <p>Manage credentials, policies, and project access from one interface with audit-friendly ownership.</p>
                    </article>
                    <article class="feature-tile">
                        <h3>Alerting & Automation</h3>
                        <p>Trigger webhooks and notifications when thresholds are crossed or anomalies appear in telemetry streams.</p>
                    </article>
                    <article class="feature-tile">
                        <h3>Growth-Oriented Plans</h3>
                        <p>Start with free limits and scale into higher monthly volume, richer APIs, and stronger support options.</p>
                    </article>
                </div>
            </div>
        </section>

        <section class="pricing-block">
            <div class="landing-shell">
                <div class="section-heading">
                    <p>Pricing</p>
                    <h2>Simple plans with visible limits</h2>
                </div>

                <div class="pricing-grid">
                    @php
                        $plans = \App\Models\SubscriptionPlan::orderBy('price')->get();
                    @endphp

                    @foreach ($plans as $plan)
                        <article class="pricing-card {{ $plan->tier === 'professional' ? 'featured' : '' }}">
                            @if ($plan->tier === 'professional')
                                <div class="pricing-tag">Most Popular</div>
                            @endif

                            <h3>{{ ucfirst($plan->tier) }}</h3>
                            <p class="price-line">
                                {{ $plan->price == 0 ? 'Free' : 'Rp ' . number_format($plan->price, 0, ',', '.') }}
                                <span>/bulan</span>
                            </p>

                            <ul>
                                <li>Secure Connection (SSL/TLS)</li>
                                <li>{{ $plan->max_projects == -1 ? 'High Fair Use projects' : $plan->max_projects . ' Projects' }}</li>
                                <li>{{ $plan->max_devices_per_project == -1 ? 'High Fair Use devices' : $plan->max_devices_per_project . ' Devices per project' }}</li>
                                <li>{{ $plan->max_topics_per_project == -1 ? 'High Fair Use topics' : $plan->max_topics_per_project . ' Topics per project' }}</li>
                                <li>{{ $plan->rate_limit_per_hour == -1 ? 'High Fair Use rate limit' : number_format($plan->rate_limit_per_hour) . ' msg/hour' }}</li>
                                <li>{{ $plan->max_monthly_messages == -1 ? 'High Fair Use monthly messages' : number_format($plan->max_monthly_messages) . ' monthly messages' }}</li>
                                @if ($plan->data_retention_days != 0)
                                    <li>{{ $plan->data_retention_days == -1 ? 'High Fair Use retention' : $plan->data_retention_days . ' days retention' }}</li>
                                @endif
                                <li>
                                    {{ $plan->webhooks_enabled ? (($plan->max_webhooks_per_project == -1 ? 'High Fair Use' : $plan->max_webhooks_per_project) . ' webhooks per project') : 'Webhooks not included' }}
                                </li>
                                <li>
                                    {{ $plan->advanced_analytics_enabled ? (($plan->max_advance_dashboard_widgets == -1 ? 'High Fair Use' : $plan->max_advance_dashboard_widgets) . ' dashboard widgets') : 'Advanced dashboard widgets not included' }}
                                </li>
                                <li>
                                    {{ $plan->api_access ? (($plan->max_api_keys == -1 ? 'High Fair Use' : $plan->max_api_keys) . ' API keys') : 'API keys not included' }}
                                </li>
                                <li>
                                    {{ $plan->api_access ? (($plan->api_rpm == -1 ? 'High Fair Use' : number_format($plan->api_rpm)) . ' API RPM') : 'API access not included' }}
                                </li>
                                @if ($plan->analytics_enabled)
                                    <li>Analytics dashboard</li>
                                @endif
                                @if ($plan->advanced_analytics_enabled)
                                    <li>Advanced Dashboard</li>
                                @endif
                                @if ($plan->webhooks_enabled)
                                    <li>Webhooks integration</li>
                                @endif
                                @if ($plan->api_access)
                                    <li>API access</li>
                                @endif
                                @if ($plan->priority_support)
                                    <li>Priority support</li>
                                @endif
                            </ul>

                            @guest
                                <a href="{{ route('register') }}" class="action-btn {{ $plan->price == 0 ? 'action-ghost' : 'action-primary' }}">
                                    {{ $plan->price == 0 ? 'Start Free' : ($plan->tier === 'enterprise' ? 'Contact Sales' : 'Get Started') }}
                                </a>
                            @else
                                <a href="{{ $plan->price == 0 ? route('home.dashboard') : route('subscription.upgrade') }}" class="action-btn {{ $plan->price == 0 ? 'action-ghost' : 'action-primary' }}">
                                    {{ $plan->price == 0 ? 'Go to Home' : 'Upgrade' }}
                                </a>
                            @endguest
                        </article>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="cta-block">
            <div class="landing-shell cta-card">
                <h2>Build your first MQTT workload in minutes</h2>
                <p>Spin up projects, onboard devices, and move from testing to production with clear operational controls.</p>
                @guest
                    <a href="{{ route('register') }}" class="action-btn action-primary">Create Free Account</a>
                @endguest
            </div>
        </section>
    </main>

    <footer class="landing-footer">
        <div class="landing-shell">
            <p>© 2026 ICMQTT. Built for practical IoT operations.</p>
        </div>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const toggle = document.querySelector('.menu-toggle');
            const menu = document.getElementById('primary-menu');

            if (!toggle || !menu) {
                return;
            }

            toggle.addEventListener('click', () => {
                const isOpen = menu.classList.toggle('is-open');
                toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            });
        });
    </script>
</body>
</html>
