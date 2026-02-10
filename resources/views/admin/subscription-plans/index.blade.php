@extends('layouts.app')

@section('content')
<div style="padding: 2rem 0;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <div>
            <h1 style="font-size: 2rem; font-weight: 700; color: #1f2937; margin-bottom: 0.25rem;">📋 Subscription Plans</h1>
            <p style="color: #9ca3af; font-size: 0.9rem;">Manage and customize subscription tiers</p>
        </div>
        <a href="{{ route('admin.subscription-plans.statistics') }}" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; padding: 0.65rem 1.5rem; border-radius: 8px; text-decoration: none; font-weight: 600; transition: all 0.2s ease; box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);">
            📊 Statistics
        </a>
    </div>

    @if (session('success'))
        <div style="background: #f0fdf4; border: 1px solid #86efac; border-radius: 8px; padding: 1rem; margin-bottom: 1.5rem; color: #166534;">
            <strong>✓ Success!</strong> {{ session('success') }}
        </div>
    @endif

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.5rem;">
        @foreach ($plans as $planName => $details)
            <div style="background: white; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); overflow: hidden; border: 1px solid #e5e7eb; transition: all 0.3s ease;" onmouseover="this.style.transform='translateY(-4px)'; this.style.boxShadow='0 12px 24px rgba(0,0,0,0.15)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.08)';">
                <div style="background: linear-gradient(135deg, 
                    @if($planName === 'enterprise') #fbbf24 0%, #f59e0b
                    @elseif($planName === 'professional') #667eea 0%, #764ba2
                    @elseif($planName === 'starter') #06b6d4 0%, #0891b2
                    @else #f3f4f6 0%, #e5e7eb
                    @endif 100%); padding: 1.5rem; color: {{ in_array($planName, ['enterprise', 'professional', 'starter']) ? 'white' : '#1f2937' }};">
                    <h2 style="font-size: 1.5rem; font-weight: 700; margin: 0;">{{ ucfirst($planName) }}</h2>
                </div>
                
                <div style="padding: 1.5rem;">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-bottom: 1.5rem; font-size: 0.9rem;">
                        <div style="grid-column: span 2; margin-bottom: 1rem;">
                            <div style="color: #6b7280; font-size: 0.8rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.25rem;">Price</div>
                            <div style="font-weight: 700; color: #059669; font-size: 1.1rem;">
                                {{ $details['price'] == 0 ? 'Free' : 'Rp ' . number_format($details['price'], 2, ',', '.') }}
                            </div>
                        </div>
                        <div>
                            <div style="color: #6b7280; font-size: 0.8rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.25rem;">Projects</div>
                            <div style="font-weight: 700; color: #1f2937; font-size: 1.1rem;">{{ $details['max_projects'] == -1 ? '∞' : $details['max_projects'] }}</div>
                        </div>
                        <div>
                            <div style="color: #6b7280; font-size: 0.8rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.25rem;">Devices</div>
                            <div style="font-weight: 700; color: #1f2937; font-size: 1.1rem;">{{ $details['max_devices_per_project'] == -1 ? '∞' : $details['max_devices_per_project'] }}</div>
                        </div>
                        <div>
                            <div style="color: #6b7280; font-size: 0.8rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.25rem;">Topics</div>
                            <div style="font-weight: 700; color: #1f2937; font-size: 1.1rem;">{{ $details['max_topics_per_project'] == -1 ? '∞' : $details['max_topics_per_project'] }}</div>
                        </div>
                        <div>
                            <div style="color: #6b7280; font-size: 0.8rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.25rem;">Rate Limit</div>
                            <div style="font-weight: 700; color: #1f2937; font-size: 1.1rem;">{{ $details['rate_limit_per_hour'] == -1 ? '∞' : $details['rate_limit_per_hour'] }}<span style="font-size: 0.8rem; font-weight: 400;">/hr</span></div>
                        </div>
                    </div>

                    <div style="border-top: 1px solid #e5e7eb; padding-top: 1rem; margin-bottom: 1rem;">
                        <div style="display: flex; flex-wrap: wrap; gap: 0.5rem;">
                            @if ($details['analytics_enabled'])
                                <span style="display: inline-block; padding: 0.35rem 0.75rem; border-radius: 6px; font-size: 0.75rem; font-weight: 600; background: #dbeafe; color: #1e40af;">✓ Analytics</span>
                            @endif
                            @if ($details['webhooks_enabled'])
                                <span style="display: inline-block; padding: 0.35rem 0.75rem; border-radius: 6px; font-size: 0.75rem; font-weight: 600; background: #d1fae5; color: #065f46;">✓ Webhooks</span>
                            @endif
                            @if ($details['api_access'])
                                <span style="display: inline-block; padding: 0.35rem 0.75rem; border-radius: 6px; font-size: 0.75rem; font-weight: 600; background: #fce7f3; color: #831843;">✓ API Access</span>
                            @endif
                            @if ($details['priority_support'])
                                <span style="display: inline-block; padding: 0.35rem 0.75rem; border-radius: 6px; font-size: 0.75rem; font-weight: 600; background: #fef3c7; color: #92400e;">✓ Priority Support</span>
                            @endif
                        </div>
                    </div>

                    <div style="display: flex; gap: 0.75rem;">
                        <a href="{{ route('admin.subscription-plans.edit', $planName) }}" style="flex: 1; display: block; text-align: center; padding: 0.65rem; background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); color: white; border-radius: 6px; text-decoration: none; font-size: 0.85rem; font-weight: 600; transition: all 0.2s ease;">
                            ✏️ Edit
                        </a>
                        <form action="{{ route('admin.subscription-plans.reset', $planName) }}" method="POST" style="flex: 1;" onsubmit="return confirm('Reset {{ ucfirst($planName) }} to default values?');">
                            @csrf
                            @method('PATCH')
                            <button type="submit" style="width: 100%; padding: 0.65rem; background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: white; border: none; border-radius: 6px; text-decoration: none; font-size: 0.85rem; font-weight: 600; cursor: pointer; transition: all 0.2s ease;">
                                🔄 Reset
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection
