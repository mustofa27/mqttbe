@extends('layouts.app')

@section('title', 'Upgrade Subscription')

@section('content')
<style>
    .pricing-header {
        text-align: center;
        margin-bottom: 3rem;
    }

    .pricing-header h1 {
        font-size: 2.5rem;
        margin-bottom: 0.5rem;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .pricing-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 2rem;
        margin-bottom: 2rem;
    }

    .pricing-card {
        background: white;
        border-radius: 12px;
        padding: 2rem;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        transition: transform 0.3s, box-shadow 0.3s;
        position: relative;
        border: 2px solid transparent;
    }

    .pricing-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 16px rgba(0,0,0,0.15);
    }

    .pricing-card.current {
        border-color: #667eea;
        background: linear-gradient(to bottom, #f8f9ff, white);
    }

    .pricing-card.recommended {
        border-color: #667eea;
        transform: scale(1.05);
    }

    .recommended-badge {
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

    .current-badge {
        position: absolute;
        top: -12px;
        left: 20px;
        background: #28a745;
        color: white;
        padding: 0.25rem 1rem;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .tier-name {
        font-size: 1.5rem;
        font-weight: bold;
        margin-bottom: 0.5rem;
        color: #333;
    }

    .tier-price {
        font-size: 2.5rem;
        font-weight: bold;
        color: #667eea;
        margin-bottom: 0.25rem;
        line-height: 1;
    }

    .tier-price small {
        font-size: 1.25rem;
        color: #666;
        font-weight: 500;
        margin-left: 0.25rem;
    }

    .tier-description {
        color: #666;
        margin-bottom: 1.5rem;
        min-height: 3rem;
    }

    .features-list {
        list-style: none;
        padding: 0;
        margin: 1.5rem 0;
    }

    .features-list li {
        padding: 0.5rem 0;
        color: #333;
        display: flex;
        align-items: start;
        gap: 0.5rem;
    }

    .features-list li::before {
        content: '✓';
        color: #28a745;
        font-weight: bold;
        flex-shrink: 0;
    }

    .btn-select-plan {
        width: 100%;
        padding: 0.75rem;
        border: none;
        border-radius: 6px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
        text-decoration: none;
        display: inline-block;
        text-align: center;
    }

    .btn-select-plan.primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }

    .btn-select-plan.primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
    }

    .btn-select-plan.secondary {
        background: #f0f0f0;
        color: #666;
    }

    .btn-select-plan.disabled {
        background: #e0e0e0;
        color: #999;
        cursor: not-allowed;
    }

    .payment-modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.5);
        z-index: 1000;
        align-items: center;
        justify-content: center;
    }

    .payment-modal.active {
        display: flex;
    }

    .payment-modal-content {
        background: white;
        padding: 2rem;
        border-radius: 12px;
        max-width: 500px;
        width: 90%;
        box-shadow: 0 20px 60px rgba(0,0,0,0.3);
    }

    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
    }

    .modal-close {
        background: none;
        border: none;
        font-size: 1.5rem;
        cursor: pointer;
        color: #999;
    }

    .addon-options {
        margin-top: 1rem;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        padding: 0.75rem;
        display: grid;
        gap: 0.5rem;
        max-height: 180px;
        overflow-y: auto;
        background: #fafafa;
    }

    .addon-option {
        display: flex;
        gap: 0.5rem;
        align-items: start;
        font-size: 0.9rem;
    }

    .addon-option small {
        color: #64748b;
    }
</style>

<div class="pricing-header">
    <h1>Choose Your Plan</h1>
    <p style="color: #666; font-size: 1.1rem;">Select the plan that best fits your needs</p>
    <p style="color: #999; margin-top: 0.5rem;">Currently on: <strong>{{ ucfirst($currentTier) }}</strong> plan</p>
</div>

<div class="pricing-grid">
    @php
        $upgradePlans = \App\Models\SubscriptionPlan::where('tier', '!=', 'free')->orderBy('price')->get();
    @endphp
    @foreach ($upgradePlans as $plan)
        <div class="pricing-card {{ $plan->tier === 'professional' ? 'recommended' : '' }} {{ $currentTier === $plan->tier ? 'current' : '' }}">
            @if($plan->tier === 'professional' && $currentTier !== 'professional')
                <div class="recommended-badge">RECOMMENDED</div>
            @elseif($currentTier === $plan->tier)
                <div class="current-badge">CURRENT PLAN</div>
            @endif
            <div class="tier-name">{{ ucfirst($plan->tier) }}</div>
            <div class="tier-price">{{ $plan->price == 0 ? 'Free' : 'Rp ' . number_format($plan->price, 0, ',', '.') }}<small>/bulan</small></div>
            <div class="tier-description">{{ $plan->tier === 'starter' ? 'Perfect for small projects and growing teams' : ($plan->tier === 'professional' ? 'For professional developers and businesses' : 'Unlimited power for large-scale deployments') }}</div>
            <ul class="features-list">
                <li>{{ $plan->max_projects == -1 ? 'Unlimited Projects' : $plan->max_projects . ' Projects' }}</li>
                <li>{{ $plan->max_devices_per_project == -1 ? 'Unlimited Devices' : $plan->max_devices_per_project . ' Devices per project' }}</li>
                <li>{{ $plan->max_topics_per_project == -1 ? 'Unlimited Topics' : $plan->max_topics_per_project . ' Topics per project' }}</li>
                <li>{{ $plan->rate_limit_per_hour == -1 ? 'Unlimited rate limit' : number_format($plan->rate_limit_per_hour) . ' msg/hour rate limit' }}</li>
                <li>{{ $plan->data_retention_days == -1 ? 'Unlimited data retention' : $plan->data_retention_days . ' days data retention' }}</li>
                @if ($plan->analytics_enabled)
                    <li>Analytics Dashboard</li>
                @endif
                @if ($plan->advanced_analytics_enabled)
                    <li>Advanced Dashboard</li>
                @endif
                @if ($plan->webhooks_enabled)
                    <li>Webhooks Integration</li>
                @endif
                @if ($plan->api_access)
                    <li>API Access</li>
                @endif
                @if ($plan->priority_support)
                    <li>Priority Support</li>
                @endif
                @if ($plan->tier === 'enterprise')
                    <li>Dedicated Support</li>
                    <li>Custom SLA</li>
                    <li>White-label options</li>
                @endif
                <li>Secure Connection (SSL/TLS)</li>
            </ul>
            @if($currentTier === $plan->tier)
                <button class="btn-select-plan disabled" disabled>Current Plan</button>
            @elseif(in_array($currentTier, ['professional', 'enterprise']) && $plan->tier === 'starter')
                <a href="#" class="btn-select-plan secondary" onclick="alert('Please cancel your current subscription first to downgrade.'); return false;">
                    Downgrade to Starter
                </a>
            @elseif($currentTier === 'enterprise' && $plan->tier === 'professional')
                <a href="#" class="btn-select-plan secondary" onclick="alert('Please cancel your current subscription first to downgrade.'); return false;">
                    Downgrade to Professional
                </a>
            @else
                <a href="#" class="btn-select-plan primary" onclick="openPaymentModal('{{ $plan->tier }}', {{ $plan->price }}); return false;">
                    Upgrade to {{ ucfirst($plan->tier) }}
                </a>
            @endif
        </div>
    @endforeach
</div>

<div style="margin: 3rem 0; background: white; border-radius: 12px; padding: 2rem; box-shadow: 0 4px 6px rgba(0,0,0,0.08);">
    <h2 style="margin-bottom: 1rem; color: #1f2937;">Plan Limits Comparison</h2>
    @php
        $comparePlans = \App\Models\SubscriptionPlan::whereIn('tier', \App\Models\SubscriptionPlan::getTiers())
            ->orderByRaw("FIELD(tier, 'free', 'starter', 'professional', 'enterprise')")
            ->get()
            ->keyBy('tier');
    @endphp
    <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; min-width: 900px;">
            <thead>
                <tr style="border-bottom: 2px solid #e5e7eb; text-align: left;">
                    <th style="padding: 0.75rem;">Feature</th>
                    @foreach(['free', 'starter', 'professional', 'enterprise'] as $tier)
                        <th style="padding: 0.75rem; text-align: center;">{{ ucfirst($tier) }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @php
                    $rows = [
                        ['label' => 'Projects', 'key' => 'max_projects', 'format' => 'count'],
                        ['label' => 'Devices per Project', 'key' => 'max_devices_per_project', 'format' => 'count'],
                        ['label' => 'Topics per Project', 'key' => 'max_topics_per_project', 'format' => 'count'],
                        ['label' => 'Monthly Messages', 'key' => 'max_monthly_messages', 'format' => 'number'],
                        ['label' => 'API Keys', 'key' => 'max_api_keys', 'format' => 'count'],
                        ['label' => 'Webhooks', 'key' => 'max_webhooks_per_project', 'format' => 'count'],
                        ['label' => 'Dashboard Widgets', 'key' => 'max_advance_dashboard_widgets', 'format' => 'count'],
                        ['label' => 'API RPM', 'key' => 'api_rpm', 'format' => 'number'],
                        ['label' => 'Data Retention', 'key' => 'data_retention_days', 'format' => 'days'],
                    ];

                    $featureRows = [
                        ['label' => 'Analytics Dashboard', 'key' => 'analytics_enabled'],
                        ['label' => 'Advanced Dashboard', 'key' => 'advanced_analytics_enabled'],
                        ['label' => 'API Access', 'key' => 'api_access'],
                        ['label' => 'Priority Support', 'key' => 'priority_support'],
                    ];
                @endphp
                @foreach($rows as $row)
                    <tr style="border-bottom: 1px solid #f0f0f0;">
                        <td style="padding: 0.75rem;">{{ $row['label'] }}</td>
                        @foreach(['free', 'starter', 'professional', 'enterprise'] as $tier)
                            @php $plan = $comparePlans->get($tier); @endphp
                            <td style="text-align: center; padding: 0.75rem; {{ $currentTier === $tier ? 'background: #f0f0ff; font-weight: bold;' : '' }}">
                                @if(!$plan)
                                    -
                                @else
                                    @php
                                        $value = $plan->{$row['key']} ?? null;
                                    @endphp
                                    @if($value === -1)
                                        Unlimited
                                    @elseif($row['format'] === 'days')
                                        {{ $value }} days
                                    @elseif($row['format'] === 'number')
                                        {{ number_format((int) $value) }}
                                    @else
                                        {{ $value }}
                                    @endif
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @endforeach
                @foreach($featureRows as $row)
                    <tr style="border-bottom: 1px solid #f0f0f0;">
                        <td style="padding: 0.75rem;">{{ $row['label'] }}</td>
                        @foreach(['free', 'starter', 'professional', 'enterprise'] as $tier)
                            @php $plan = $comparePlans->get($tier); @endphp
                            <td style="text-align: center; padding: 0.75rem; {{ $currentTier === $tier ? 'background: #f0f0ff; font-weight: bold;' : '' }}">
                                {{ !empty($plan?->{$row['key']}) ? '✓ Enabled' : '✗ Disabled' }}
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<div style="text-align: center; margin-top: 3rem; padding: 2rem; background: #f8f9fa; border-radius: 8px;">
    <p style="color: #666; margin-bottom: 1rem;">All plans include:</p>
    <div style="display: flex; justify-content: center; gap: 2rem; flex-wrap: wrap;">
        <span>✓ MQTT Authentication</span>
        <span>✓ ACL Control</span>
        <span>✓ Dashboard Access</span>
        <span>✓ Regular Updates</span>
        <span>✓ Secure Infrastructure</span>
    </div>
</div>

<!-- Payment Modal -->
<div id="paymentModal" class="payment-modal">
    <div class="payment-modal-content">
        <div class="modal-header">
            <h2 id="modalTitle">Upgrade to Professional</h2>
            <button class="modal-close" onclick="closePaymentModal()">×</button>
        </div>

        <form method="POST" action="{{ route('subscription.processUpgrade') }}" id="paymentForm">
            @csrf
            <input type="hidden" name="tier" id="selectedTier">
            <div class="form-group">
                <label>Selected Plan</label>
                <input type="text" id="displayTier" readonly style="background: #f8f9fa;">
            </div>
            <div class="form-group">
                <label>Monthly Price</label>
                <input type="text" id="displayPrice" readonly style="background: #f8f9fa;">
            </div>
            <div class="form-group">
                <label for="months">Duration (months)</label>
                <input type="number" name="months" id="months" min="1" value="1" style="background: #f8f9fa; width: 100%; padding: 0.5rem; border-radius: 6px; border: 1px solid #d1d5db;" required>
            </div>
            <div class="form-group">
                <label>Total Price</label>
                <input type="text" id="displayTotal" readonly style="background: #f8f9fa;">
            </div>

            @if(isset($addons) && $addons->isNotEmpty())
                <div class="form-group">
                    <label>Optional Add-ons</label>
                    <div class="addon-options">
                        @foreach($addons as $addon)
                            <label class="addon-option">
                                <input type="checkbox"
                                       name="addon_codes[]"
                                       value="{{ $addon->code }}"
                                       data-addon-price="{{ (float) $addon->price }}"
                                       data-addon-recurring="{{ $addon->is_recurring ? '1' : '0' }}"
                                       class="addon-checkbox">
                                <span>
                                    <strong>{{ $addon->name }}</strong>
                                    <small>
                                        {{ $addon->unit_type_label }} - +{{ $addon->included_units }}
                                        ({{ $addon->code }}) - Rp {{ number_format($addon->price, 0, ',', '.') }}
                                        {{ $addon->is_recurring ? '/bulan' : 'sekali bayar' }}
                                    </small>
                                </span>
                            </label>
                        @endforeach
                    </div>
                </div>
            @endif

            <p style="color: #666; margin: 1rem 0; font-size: 0.9rem;">
                You will be redirected to our secure payment gateway (Paypool) to complete the payment.
            </p>
            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 1rem; margin-top: 1rem;">
                Proceed to Payment
            </button>
        </form>

        <p style="text-align: center; margin-top: 1rem; color: #999; font-size: 0.85rem;">
            🔒 Secure payment processing. Your data is encrypted.
        </p>
    </div>
</div>

<script>

    // Prevent double submission
    document.addEventListener('DOMContentLoaded', function() {
        const paymentForm = document.getElementById('paymentForm');
        if (paymentForm) {
            paymentForm.addEventListener('submit', function(e) {
                const submitBtn = paymentForm.querySelector('button[type="submit"]');
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.textContent = 'Processing...';
                }
            });
        }
    });

    let currentPlanPrice = 0;

    function recalculateTotal() {
        const monthsInput = document.getElementById('months');
        const displayTotal = document.getElementById('displayTotal');
        let months = parseInt(monthsInput.value, 10);

        if (isNaN(months) || months < 1) {
            months = 1;
            monthsInput.value = 1;
        }

        const baseTotal = currentPlanPrice * months;
        let addonTotal = 0;

        document.querySelectorAll('.addon-checkbox:checked').forEach((checkbox) => {
            const price = parseFloat(checkbox.dataset.addonPrice || '0');
            const recurring = checkbox.dataset.addonRecurring === '1';
            addonTotal += recurring ? (price * months) : price;
        });

        const total = baseTotal + addonTotal;
        displayTotal.value = 'Rp ' + total.toLocaleString('id-ID') + ' / ' + months + ' bulan';
    }

    function openPaymentModal(tier, price) {
        const modal = document.getElementById('paymentModal');
        const modalTitle = document.getElementById('modalTitle');
        const selectedTier = document.getElementById('selectedTier');
        const displayTier = document.getElementById('displayTier');
        const displayPrice = document.getElementById('displayPrice');
        const displayTotal = document.getElementById('displayTotal');
        const monthsInput = document.getElementById('months');

        currentPlanPrice = price;
        modalTitle.textContent = 'Upgrade to ' + tier.charAt(0).toUpperCase() + tier.slice(1);
        selectedTier.value = tier;
        displayTier.value = tier.charAt(0).toUpperCase() + tier.slice(1);
        displayPrice.value = 'Rp ' + price.toLocaleString('id-ID') + '/bulan';
        monthsInput.value = 1;
        document.querySelectorAll('.addon-checkbox').forEach((checkbox) => {
            checkbox.checked = false;
        });

        monthsInput.oninput = recalculateTotal;
        document.querySelectorAll('.addon-checkbox').forEach((checkbox) => {
            checkbox.onchange = recalculateTotal;
        });

        recalculateTotal();

        modal.classList.add('active');
    }

    function closePaymentModal() {
        const modal = document.getElementById('paymentModal');
        modal.classList.remove('active');
    }

    // Close modal when clicking outside
    document.getElementById('paymentModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closePaymentModal();
        }
    });
</script>
@endsection
