@extends('layouts.app')

@section('content')
<div style="padding: 2rem 0;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <div>
            <h1 style="font-size: 2rem; font-weight: 700; color: #1f2937; margin-bottom: 0.25rem;">📊 Plan Statistics</h1>
            <p style="color: #9ca3af; font-size: 0.9rem;">View subscription distribution and user analytics</p>
        </div>
        <a href="{{ route('admin.subscription-plans.index') }}" style="background: #f3f4f6; color: #374151; padding: 0.65rem 1.5rem; border-radius: 8px; text-decoration: none; font-weight: 600; transition: all 0.2s ease;">
            ← Back to Plans
        </a>
    </div>

    <!-- Stats Cards -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
        @foreach ($stats as $planName => $count)
            <div style="background: linear-gradient(135deg, 
                @if($planName === 'enterprise') #fbbf24 0%, #f59e0b
                @elseif($planName === 'professional') #667eea 0%, #764ba2
                @elseif($planName === 'starter') #06b6d4 0%, #0891b2
                @else #f3f4f6 0%, #e5e7eb
                @endif 100%); border-radius: 10px; padding: 2rem; color: {{ in_array($planName, ['enterprise', 'professional', 'starter']) ? 'white' : '#1f2937' }}; box-shadow: 0 4px 12px rgba(0,0,0,0.08);">
                <div style="font-size: 0.9rem; font-weight: 600; opacity: 0.9; margin-bottom: 0.5rem; text-transform: uppercase; letter-spacing: 0.5px;">{{ ucfirst($planName) }} Plan</div>
                <div style="font-size: 3rem; font-weight: 700; line-height: 1;">{{ $count }}</div>
                <div style="font-size: 0.85rem; margin-top: 0.5rem; opacity: 0.85;">{{ $count == 1 ? 'user' : 'users' }}</div>
            </div>
        @endforeach
    </div>

    <!-- Summary Card -->
    <div style="background: white; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); padding: 2rem; border: 1px solid #e5e7eb;">
        <h2 style="font-size: 1.3rem; font-weight: 700; color: #1f2937; margin-bottom: 1.5rem;">📈 Breakdown</h2>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
            <!-- Summary Stats -->
            <div>
                <div style="margin-bottom: 1.5rem;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                        <span style="color: #6b7280; font-weight: 600;">Total Users</span>
                        <span style="font-size: 1.8rem; font-weight: 700; color: #667eea;">{{ array_sum($stats) }}</span>
                    </div>
                </div>

                <div style="margin-bottom: 1.5rem; padding: 1.25rem; background: #f9fafb; border-radius: 8px; border-left: 4px solid #f3f4f6;">
                    <div style="color: #6b7280; font-size: 0.9rem; margin-bottom: 0.25rem;">Free Tier</div>
                    <div style="font-size: 1.5rem; font-weight: 700; color: #1f2937;">{{ $stats['free'] }}</div>
                    <div style="color: #9ca3af; font-size: 0.85rem; margin-top: 0.25rem;">{{ round($stats['free'] / max(array_sum($stats), 1) * 100, 1) }}% of users</div>
                </div>

                <div style="margin-bottom: 1.5rem; padding: 1.25rem; background: linear-gradient(135deg, #f0f9ff 0%, #f0fdfa 100%); border-radius: 8px; border-left: 4px solid #06b6d4;">
                    <div style="color: #0891b2; font-size: 0.9rem; margin-bottom: 0.25rem; font-weight: 600;">Starter Tier</div>
                    <div style="font-size: 1.5rem; font-weight: 700; color: #0891b2;">{{ $stats['starter'] }}</div>
                    <div style="color: #06b6d4; font-size: 0.85rem; margin-top: 0.25rem;">{{ round($stats['starter'] / max(array_sum($stats), 1) * 100, 1) }}% of users</div>
                </div>
            </div>

            <div>
                <div style="margin-bottom: 1.5rem; padding: 1.25rem; background: linear-gradient(135deg, #f3f4f6 0%, #eef2ff 100%); border-radius: 8px; border-left: 4px solid #667eea;">
                    <div style="color: #4f46e5; font-size: 0.9rem; margin-bottom: 0.25rem; font-weight: 600;">Professional Tier</div>
                    <div style="font-size: 1.5rem; font-weight: 700; color: #4f46e5;">{{ $stats['professional'] }}</div>
                    <div style="color: #667eea; font-size: 0.85rem; margin-top: 0.25rem;">{{ round($stats['professional'] / max(array_sum($stats), 1) * 100, 1) }}% of users</div>
                </div>

                <div style="padding: 1.25rem; background: linear-gradient(135deg, #fefce8 0%, #fef3c7 100%); border-radius: 8px; border-left: 4px solid #f59e0b;">
                    <div style="color: #d97706; font-size: 0.9rem; margin-bottom: 0.25rem; font-weight: 600;">Enterprise Tier</div>
                    <div style="font-size: 1.5rem; font-weight: 700; color: #d97706;">{{ $stats['enterprise'] }}</div>
                    <div style="color: #f59e0b; font-size: 0.85rem; margin-top: 0.25rem;">{{ round($stats['enterprise'] / max(array_sum($stats), 1) * 100, 1) }}% of users</div>
                </div>
            </div>
        </div>

        <!-- Chart Representation -->
        <div style="margin-top: 2rem; padding-top: 2rem; border-top: 1px solid #e5e7eb;">
            <h3 style="font-size: 1rem; font-weight: 600; color: #1f2937; margin-bottom: 1rem;">Distribution</h3>
            
            @foreach ($stats as $planName => $count)
                <div style="margin-bottom: 1rem;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                        <span style="color: #6b7280; font-weight: 500; font-size: 0.95rem;">{{ ucfirst($planName) }}</span>
                        <span style="color: #1f2937; font-weight: 700;">{{ round($count / max(array_sum($stats), 1) * 100, 1) }}%</span>
                    </div>
                    <div style="height: 8px; background: #e5e7eb; border-radius: 4px; overflow: hidden;">
                        <div style="height: 100%; background: linear-gradient(90deg, 
                            @if($planName === 'enterprise') #fbbf24 0%, #f59e0b
                            @elseif($planName === 'professional') #667eea 0%, #764ba2
                            @elseif($planName === 'starter') #06b6d4 0%, #0891b2
                            @else #d1d5db 0%, #9ca3af
                            @endif 100%); width: {{ round($count / max(array_sum($stats), 1) * 100, 1) }}%; transition: width 0.3s ease;"></div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
