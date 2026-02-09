<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ICMQTT - Manage Your IoT Infrastructure</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html, body {
            font-family: 'Instrument Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            color: #333;
        }

        .navbar {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            padding: 1rem 2rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .navbar-container {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .navbar-brand {
            font-size: 1.5rem;
            font-weight: 600;
            color: #333;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .navbar-brand span {
            color: #667eea;
        }

        .navbar-nav {
            display: flex;
            gap: 2rem;
            align-items: center;
            list-style: none;
        }

        .menu-toggle {
            display: none;
            align-items: center;
            gap: 0.5rem;
            background: rgba(102, 126, 234, 0.1);
            border: 1px solid rgba(102, 126, 234, 0.4);
            color: #333;
            padding: 0.45rem 0.85rem;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
        }

        .navbar-nav a {
            color: #333;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s;
            border-radius: 6px;
            padding: 0.5rem 1rem;
        }

        .navbar-nav a:hover {
            color: #667eea;
            background: rgba(102, 126, 234, 0.1);
        }

        .navbar-nav button {
            background: none;
            border: none;
            color: #333;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
            border-radius: 6px;
            padding: 0.5rem 1rem;
        }

        .navbar-nav button:hover {
            color: #667eea;
            background: rgba(102, 126, 234, 0.1);
        }

        .btn {
            display: inline-block;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 6px;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            text-align: center;
            font-weight: 600;
            font-family: inherit;
        }

        .btn-primary {
            background: #667eea;
            color: white;
        }

        .btn-primary:hover {
            background: #5568d3;
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }

        .btn-secondary {
            background: white;
            color: #667eea;
            border: 2px solid #667eea;
        }

        .btn-secondary:hover {
            background: rgba(102, 126, 234, 0.1);
            transform: translateY(-2px);
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
        }

        .hero {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 3rem 2rem;
        }

        .hero-content {
            text-align: center;
            color: white;
            max-width: 700px;
        }

        .hero h1 {
            font-size: 3.5rem;
            margin-bottom: 1rem;
            line-height: 1.2;
            font-weight: 700;
        }

        .hero p {
            font-size: 1.25rem;
            margin-bottom: 2rem;
            opacity: 0.95;
            line-height: 1.6;
        }

        .hero-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
            margin-bottom: 3rem;
        }

        .features {
            background: white;
            padding: 4rem 2rem;
            border-radius: 0 0 12px 12px;
        }

        .features h2 {
            text-align: center;
            font-size: 2rem;
            margin-bottom: 3rem;
            color: #333;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .pricing-section {
            background: #f8f9fa;
            padding: 4rem 2rem;
            margin-top: 2rem;
        }

        .pricing-section h2 {
            text-align: center;
            font-size: 2rem;
            margin-bottom: 1rem;
            color: #333;
        }

        .pricing-section p {
            text-align: center;
            color: #666;
            margin-bottom: 3rem;
            font-size: 1.1rem;
        }

        .pricing-grid {
            display: flex;
            gap: 2rem;
            max-width: 100%;
            margin: 0 auto;
            padding: 0 1rem;
        }

        .pricing-card {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            border: 2px solid transparent;
            position: relative;
            min-width: 280px;
            flex: 1;
        }

        .pricing-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.2);
        }

        .pricing-card.popular {
            border-color: #667eea;
            transform: scale(1.05);
        }

        .popular-badge {
            position: absolute;
            top: -12px;
            right: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 0.25rem 1rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .pricing-tier {
            font-size: 1.25rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 0.5rem;
        }

        .pricing-price {
            font-size: 1.5rem;
            font-weight: 700;
            color: #667eea;
            margin-bottom: 0.25rem;
            line-height: 1;
        }

        .pricing-price small {
            font-size: 0.875rem;
            color: #666;
            font-weight: 500;
            margin-left: 0.25rem;
        }

        .pricing-features {
            list-style: none;
            padding: 0;
            margin: 1.5rem 0;
            min-height: 280px;
        }

        .pricing-features li {
            padding: 0.5rem 0;
            color: #666;
            display: flex;
            align-items: start;
            gap: 0.5rem;
            font-size: 0.9rem;
        }

        .pricing-features li::before {
            content: '✓';
            color: #28a745;
            font-weight: bold;
            flex-shrink: 0;
        }

        .pricing-card .btn {
            width: 100%;
            margin-top: 1rem;
        }

        .feature-card {
            background: #f8f9fa;
            padding: 2rem;
            border-radius: 12px;
            border: 2px solid transparent;
            transition: all 0.3s ease;
        }

        .feature-card:hover {
            border-color: #667eea;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.15);
            transform: translateY(-5px);
        }

        .feature-icon {
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }

        .feature-card h3 {
            font-size: 1.25rem;
            margin-bottom: 0.75rem;
            color: #333;
        }

        .feature-card p {
            color: #666;
            line-height: 1.6;
        }

        .cta-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 3rem 2rem;
            border-radius: 12px;
            text-align: center;
            margin-bottom: 2rem;
        }

        .cta-section h3 {
            font-size: 2rem;
            margin-bottom: 1rem;
        }

        .cta-section p {
            font-size: 1.1rem;
            margin-bottom: 2rem;
            opacity: 0.95;
        }

        .footer {
            background: rgba(0, 0, 0, 0.2);
            color: white;
            text-align: center;
            padding: 1.5rem;
            margin-top: auto;
        }

        .footer p {
            opacity: 0.8;
        }

        @media (max-width: 768px) {
            .navbar-container {
                flex-direction: column;
                gap: 0.75rem;
            }

            .navbar-nav {
                width: 100%;
                flex-direction: column;
                align-items: center;
                display: none;
                padding: 0.5rem 0 0.75rem;
            }

            .navbar-nav.is-open {
                display: flex;
            }

            .menu-toggle {
                display: inline-flex;
            }

            .hero h1 {
                font-size: 2rem;
            }

            .hero p {
                font-size: 1rem;
            }

            .navbar-nav {
                gap: 0.75rem;
            }

            .hero-buttons {
                flex-direction: column;
                align-items: center;
            }

            .hero-buttons .btn {
                width: 100%;
                max-width: 300px;
            }

            .pricing-grid {
                flex-direction: column;
            }

            .pricing-card {
                min-width: 100%;
            }

            .pricing-card.popular {
                transform: scale(1);
            }

            .pricing-features {
                min-height: auto;
            }
        }

        @media (max-width: 1024px) and (min-width: 769px) {
            .pricing-grid {
                flex-wrap: wrap;
            }

            .pricing-card {
                min-width: calc(50% - 1rem);
                flex: 1 1 calc(50% - 1rem);
            }

            .pricing-card.popular {
                transform: scale(1);
            }
        }

        @media (min-width: 1400px) {
            .pricing-grid {
                max-width: 1400px;
                margin: 0 auto;
            }
        }
    </style>
    </head>
    <body>
        <div class="navbar">
            <div class="navbar-container">
                <a href="{{ url('/') }}" class="navbar-brand">
                    📡 <span>ICMQTT</span>
                </a>
                @if (Route::has('login'))
                    <button class="menu-toggle" type="button" aria-expanded="false" aria-controls="primary-menu">
                        ☰ Menu
                    </button>
                    <nav id="primary-menu" class="navbar-nav">
                        <a href="{{ route('legal.policies') }}">Syarat & Kebijakan</a>
                        <a href="{{ route('contact.show') }}">Contact</a>
                        @auth
                            <a href="{{ url('/dashboard') }}">Dashboard</a>
                            <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                                @csrf
                                <button type="submit">Logout</button>
                            </form>
                        @else
                            <a href="{{ route('login') }}">Login</a>
                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" style="border: 2px solid #667eea; padding: 0.5rem 1rem;">Register</a>
                            @endif
                        @endauth
                    </nav>
                @endif
            </div>
        </div>

        <div class="hero">
            <!-- IoT Background SVG -->
            <svg class="iot-background" viewBox="0 0 1200 600" preserveAspectRatio="xMidYMid slice" xmlns="http://www.w3.org/2000/svg">
                <defs>
                    <linearGradient id="iotGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                        <stop offset="0%" style="stop-color:#667eea;stop-opacity:0.15" />
                        <stop offset="100%" style="stop-color:#764ba2;stop-opacity:0.15" />
                    </linearGradient>
                    <filter id="glow">
                        <feGaussianBlur stdDeviation="3" result="coloredBlur"/>
                        <feMerge>
                            <feMergeNode in="coloredBlur"/>
                            <feMergeNode in="SourceGraphic"/>
                        </feMerge>
                    </filter>
                </defs>
                
                <!-- Background fill with gradient -->
                <rect width="1200" height="600" fill="url(#iotGradient)"/>
                
                <!-- Left device cluster -->
                <circle cx="150" cy="150" r="70" fill="none" stroke="#667eea" stroke-width="2" opacity="0.3"/>
                <circle cx="150" cy="150" r="50" fill="none" stroke="#667eea" stroke-width="1.5" opacity="0.2"/>
                <circle cx="120" cy="120" r="8" fill="#667eea" opacity="0.8" filter="url(#glow)"/>
                <circle cx="180" cy="120" r="8" fill="#667eea" opacity="0.8" filter="url(#glow)"/>
                <circle cx="150" cy="190" r="8" fill="#667eea" opacity="0.8" filter="url(#glow)"/>
                <line x1="120" y1="120" x2="150" y2="150" stroke="#667eea" stroke-width="1.5" opacity="0.4" stroke-dasharray="3,3"/>
                <line x1="180" y1="120" x2="150" y2="150" stroke="#667eea" stroke-width="1.5" opacity="0.4" stroke-dasharray="3,3"/>
                <line x1="150" y1="190" x2="150" y2="150" stroke="#667eea" stroke-width="1.5" opacity="0.4" stroke-dasharray="3,3"/>
                
                <!-- Central MQTT Hub -->
                <circle cx="600" cy="300" r="90" fill="none" stroke="#667eea" stroke-width="2" opacity="0.25"/>
                <circle cx="600" cy="300" r="60" fill="none" stroke="#667eea" stroke-width="1.5" opacity="0.15"/>
                <circle cx="600" cy="300" r="20" fill="#667eea" opacity="0.9" filter="url(#glow)"/>
                <text x="600" y="308" text-anchor="middle" font-size="10" fill="white" font-weight="bold" opacity="0.8">MQTT</text>
                
                <!-- Right device cluster -->
                <circle cx="1050" cy="450" r="60" fill="none" stroke="#764ba2" stroke-width="2" opacity="0.3"/>
                <circle cx="1050" cy="450" r="40" fill="none" stroke="#764ba2" stroke-width="1.5" opacity="0.2"/>
                <circle cx="1020" cy="420" r="7" fill="#764ba2" opacity="0.8" filter="url(#glow)"/>
                <circle cx="1080" cy="420" r="7" fill="#764ba2" opacity="0.8" filter="url(#glow)"/>
                <circle cx="1050" cy="480" r="7" fill="#764ba2" opacity="0.8" filter="url(#glow)"/>
                <line x1="1020" y1="420" x2="1050" y2="450" stroke="#764ba2" stroke-width="1.5" opacity="0.4" stroke-dasharray="3,3"/>
                <line x1="1080" y1="420" x2="1050" y2="450" stroke="#764ba2" stroke-width="1.5" opacity="0.4" stroke-dasharray="3,3"/>
                <line x1="1050" y1="480" x2="1050" y2="450" stroke="#764ba2" stroke-width="1.5" opacity="0.4" stroke-dasharray="3,3"/>
                
                <!-- Connection lines from clusters to hub -->
                <line x1="150" y1="150" x2="600" y2="300" stroke="#667eea" stroke-width="2" opacity="0.2" stroke-dasharray="8,4"/>
                <line x1="1050" y1="450" x2="600" y2="300" stroke="#764ba2" stroke-width="2" opacity="0.2" stroke-dasharray="8,4"/>
                
                <!-- Additional floating devices -->
                <circle cx="300" cy="480" r="6" fill="#667eea" opacity="0.6" filter="url(#glow)"/>
                <circle cx="900" cy="100" r="6" fill="#764ba2" opacity="0.6" filter="url(#glow)"/>
                <circle cx="400" cy="550" r="5" fill="#667eea" opacity="0.5"/>
                <circle cx="800" cy="50" r="5" fill="#764ba2" opacity="0.5"/>
                <circle cx="500" cy="250" r="5" fill="#667eea" opacity="0.4"/>
                <circle cx="700" cy="400" r="5" fill="#764ba2" opacity="0.4"/>
            </svg>
            
            <!-- Hero Content -->
            <div class="hero-content" style="position: relative; z-index: 10;">
                <h1 style="text-shadow: 2px 2px 8px rgba(0,0,0,0.3);">Start your IOT Project Here</h1>
                <p style="text-shadow: 1px 1px 4px rgba(0,0,0,0.3);">Manage IoT devices and real-time MQTT data with ease</p>
                <div class="hero-buttons">
                    @guest
                        <a href="{{ route('login') }}" class="btn btn-primary">Sign In</a>
                        <a href="{{ route('register') }}" class="btn btn-secondary">Create Account</a>
                    @else
                        <a href="{{ url('/dashboard') }}" class="btn btn-primary">Go to Dashboard</a>
                    @endguest
                </div>
            </div>
        </div>

        <div class="features">
            <h2>Key Features</h2>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">📡</div>
                    <h3>Real-time Messaging</h3>
                    <p>Publish and subscribe to MQTT topics instantly with low-latency communication.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">🔒</div>
                    <h3>Secure Authentication</h3>
                    <p>Authenticate your IoT devices with robust access control and permissions management.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">📊</div>
                    <h3>Device Management</h3>
                    <p>Organize, monitor, and manage all your IoT devices from a single dashboard.</p>
                </div>
            </div>
        </div>

        <div class="pricing-section">
            <div class="container">
                <h2>Simple, Transparent Pricing</h2>
                <p>Choose the plan that fits your needs. Start free, upgrade anytime.</p>
                
                <div class="pricing-grid">
                    <!-- Free Plan -->
                    <div class="pricing-card">
                        <div class="pricing-tier">Free</div>
                        <div class="pricing-price">Rp 0<small>/bulan</small></div>
                        <ul class="pricing-features">
                            <li>1 Project</li>
                            <li>5 Devices per project</li>
                            <li>3 Topics per project</li>
                            <li>100 msg/hour</li>
                            <li>30 days retention</li>
                            <li>Basic support</li>
                        </ul>
                        @guest
                            <a href="{{ route('register') }}" class="btn btn-secondary">Start Free</a>
                        @else
                            <a href="{{ url('/dashboard') }}" class="btn btn-secondary">Go to Dashboard</a>
                        @endguest
                    </div>

                    <!-- Starter Plan -->
                    <div class="pricing-card">
                        <div class="pricing-tier">Starter</div>
                        <div class="pricing-price">Rp 299.000<small>/bulan</small></div>
                        <ul class="pricing-features">
                            <li>5 Projects</li>
                            <li>50 Devices per project</li>
                            <li>20 Topics per project</li>
                            <li>1,000 msg/hour</li>
                            <li>90 days retention</li>
                            <li>Analytics dashboard</li>
                            <li>API access</li>
                            <li>Email support</li>
                        </ul>
                        @guest
                            <a href="{{ route('register') }}" class="btn btn-primary">Get Started</a>
                        @else
                            <a href="{{ route('subscription.upgrade') }}" class="btn btn-primary">Upgrade</a>
                        @endguest
                    </div>

                    <!-- Professional Plan -->
                    <div class="pricing-card popular">
                        <div class="popular-badge">POPULAR</div>
                        <div class="pricing-tier">Professional</div>
                        <div class="pricing-price">Rp 1.199.000<small>/bulan</small></div>
                        <ul class="pricing-features">
                            <li>20 Projects</li>
                            <li>500 Devices per project</li>
                            <li>100 Topics per project</li>
                            <li>10,000 msg/hour</li>
                            <li>365 days retention</li>
                            <li>Advanced analytics</li>
                            <li>Webhooks integration</li>
                            <li>Full API access</li>
                            <li>Priority support</li>
                        </ul>
                        @guest
                            <a href="{{ route('register') }}" class="btn btn-primary">Get Started</a>
                        @else
                            <a href="{{ route('subscription.upgrade') }}" class="btn btn-primary">Upgrade</a>
                        @endguest
                    </div>

                    <!-- Enterprise Plan -->
                    <div class="pricing-card">
                        <div class="pricing-tier">Enterprise</div>
                        <div class="pricing-price">Rp 4.499.000<small>/bulan</small></div>
                        <ul class="pricing-features">
                            <li>Unlimited projects</li>
                            <li>Unlimited devices</li>
                            <li>Unlimited topics</li>
                            <li>Unlimited rate limit</li>
                            <li>Unlimited retention</li>
                            <li>Advanced analytics</li>
                            <li>Webhooks integration</li>
                            <li>Full API access</li>
                            <li>Dedicated support</li>
                            <li>Custom SLA</li>
                        </ul>
                        @guest
                            <a href="{{ route('register') }}" class="btn btn-primary">Contact Sales</a>
                        @else
                            <a href="{{ route('subscription.upgrade') }}" class="btn btn-primary">Upgrade</a>
                        @endguest
                    </div>
                </div>
            </div>
        </div>

        <div class="cta-section">
            <h3>Ready to get started?</h3>
            <p>Deploy your MQTT infrastructure and connect thousands of IoT devices</p>
            @guest
                <a href="{{ route('register') }}" class="btn btn-secondary" style="background: white; color: #667eea;">Get Started Free</a>
            @endguest
        </div>

        <div class="footer">
            <p>&copy; 2026 ICMQTT. All rights reserved.</p>
        </div>

        <style>
            .iot-background {
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                z-index: 1;
                opacity: 0.8;
            }
            
            .hero-content h1 {
                font-size: 3.5rem;
                font-weight: 700;
                margin-bottom: 1rem;
                line-height: 1.2;
            }
            
            .hero-content p {
                font-size: 1.25rem;
                margin-bottom: 2rem;
                opacity: 0.95;
            }
        </style>

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

        @if (Route::has('login'))
            <div class="h-14.5 hidden lg:block"></div>
        @endif
    </body>
</html>
