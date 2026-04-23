@component('mail::message')
# Subscription Expiring Soon

Hello {{ $user->name }},

Your {{ ucfirst($user->subscription_tier) }} subscription will expire in {{ $daysRemaining }} days, on {{ $expiresAt?->format('F j, Y') }}.

@component('mail::button', ['url' => route('subscription.index')])
Renew Subscription
@endcomponent

If you do not renew before the expiry date, your account will be moved to the free plan automatically.

Thanks,
ICMQTT Team
@endcomponent