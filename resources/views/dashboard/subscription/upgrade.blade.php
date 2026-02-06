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
</style>

<div class="pricing-header">
    <h1>Choose Your Plan</h1>
    <p style="color: #666; font-size: 1.1rem;">Select the plan that best fits your needs</p>
    <p style="color: #999; margin-top: 0.5rem;">Currently on: <strong>{{ ucfirst($currentTier) }}</strong> plan</p>
</div>

<div class="pricing-grid">
    <!-- Starter Plan -->
    <div class="pricing-card {{ $currentTier === 'starter' ? 'current' : '' }}">
        @if($currentTier === 'starter')
            <div class="current-badge">CURRENT PLAN</div>
        @endif
        
        <div class="tier-name">Starter</div>
        <div class="tier-price">Rp 299.000<small>/bulan</small></div>
        <div class="tier-description">Perfect for small projects and growing teams</div>

        <ul class="features-list">
            <li>5 Projects</li>
            <li>50 Devices per project</li>
            <li>20 Topics per project</li>
            <li>1,000 msg/hour rate limit</li>
            <li>90 days data retention</li>
            <li>Analytics Dashboard</li>
            <li>API Access</li>
            <li>Email Support</li>
        </ul>

        @if($currentTier === 'starter')
            <button class="btn-select-plan disabled" disabled>Current Plan</button>
        @elseif(in_array($currentTier, ['professional', 'enterprise']))
            <a href="#" class="btn-select-plan secondary" onclick="alert('Please cancel your current subscription first to downgrade.'); return false;">
                Downgrade to Starter
            </a>
        @else
            <a href="#" class="btn-select-plan primary" onclick="openPaymentModal('starter', 299000); return false;">
                Upgrade to Starter
            </a>
        @endif
    </div>

    <!-- Professional Plan -->
    <div class="pricing-card recommended {{ $currentTier === 'professional' ? 'current' : '' }}">
        @if($currentTier !== 'professional')
            <div class="recommended-badge">RECOMMENDED</div>
        @else
            <div class="current-badge">CURRENT PLAN</div>
        @endif
        
        <div class="tier-name">Professional</div>
        <div class="tier-price">Rp 1.199.000<small>/bulan</small></div>
        <div class="tier-description">For professional developers and businesses</div>

        <ul class="features-list">
            <li>20 Projects</li>
            <li>500 Devices per project</li>
            <li>100 Topics per project</li>
            <li>10,000 msg/hour rate limit</li>
            <li>365 days data retention</li>
            <li>Advanced Analytics</li>
            <li>Webhooks Integration</li>
            <li>Full API Access</li>
            <li>Priority Support</li>
        </ul>

        @if($currentTier === 'professional')
            <button class="btn-select-plan disabled" disabled>Current Plan</button>
        @elseif($currentTier === 'enterprise')
            <a href="#" class="btn-select-plan secondary" onclick="alert('Please cancel your current subscription first to downgrade.'); return false;">
                Downgrade to Professional
            </a>
        @else
            <a href="#" class="btn-select-plan primary" onclick="openPaymentModal('professional', 1199000); return false;">
                Upgrade to Professional
            </a>
        @endif
    </div>

    <!-- Enterprise Plan -->
    <div class="pricing-card {{ $currentTier === 'enterprise' ? 'current' : '' }}">
        @if($currentTier === 'enterprise')
            <div class="current-badge">CURRENT PLAN</div>
        @endif
        
        <div class="tier-name">Enterprise</div>
        <div class="tier-price">Rp 4.499.000<small>/bulan</small></div>
        <div class="tier-description">Unlimited power for large-scale deployments</div>

        <ul class="features-list">
            <li>Unlimited Projects</li>
            <li>Unlimited Devices</li>
            <li>Unlimited Topics</li>
            <li>Unlimited rate limit</li>
            <li>Unlimited data retention</li>
            <li>Advanced Analytics</li>
            <li>Webhooks Integration</li>
            <li>Full API Access</li>
            <li>Dedicated Support</li>
            <li>Custom SLA</li>
            <li>White-label options</li>
        </ul>

        @if($currentTier === 'enterprise')
            <button class="btn-select-plan disabled" disabled>Current Plan</button>
        @else
            <a href="#" class="btn-select-plan primary" onclick="openPaymentModal('enterprise', 4499000); return false;">
                Upgrade to Enterprise
            </a>
        @endif
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
    function openPaymentModal(tier, price) {
        const modal = document.getElementById('paymentModal');
        const modalTitle = document.getElementById('modalTitle');
        const selectedTier = document.getElementById('selectedTier');
        const displayTier = document.getElementById('displayTier');
        const displayPrice = document.getElementById('displayPrice');

        modalTitle.textContent = 'Upgrade to ' + tier.charAt(0).toUpperCase() + tier.slice(1);
        selectedTier.value = tier;
        displayTier.value = tier.charAt(0).toUpperCase() + tier.slice(1);
        displayPrice.value = 'Rp ' + price.toLocaleString('id-ID') + '/bulan';

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
