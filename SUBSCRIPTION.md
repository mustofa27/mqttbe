# Subscription System Documentation

## Overview

This MQTT backend system now includes a comprehensive subscription-based monetization system with four tiers: Free, Starter, Professional, and Enterprise.

## Subscription Tiers

### Free Tier (Default)
- **Cost**: $0/month
- **Max Projects**: 1
- **Max Devices per Project**: 5
- **Max Topics per Project**: 3
- **Rate Limit**: 100 messages/hour
- **Data Retention**: 30 days
- **Features**:
  - ❌ Analytics
  - ❌ Webhooks
  - ❌ API Access
  - ❌ Priority Support

### Starter Tier
- **Cost**: $9-19/month (suggested)
- **Max Projects**: 5
- **Max Devices per Project**: 50
- **Max Topics per Project**: 20
- **Rate Limit**: 1,000 messages/hour
- **Data Retention**: 90 days
- **Features**:
  - ✅ Analytics
  - ❌ Webhooks
  - ✅ API Access
  - ❌ Priority Support

### Professional Tier
- **Cost**: $49-99/month (suggested)
- **Max Projects**: 20
- **Max Devices per Project**: 500
- **Max Topics per Project**: 100
- **Rate Limit**: 10,000 messages/hour
- **Data Retention**: 365 days
- **Features**:
  - ✅ Analytics
  - ✅ Webhooks
  - ✅ API Access
  - ✅ Priority Support

### Enterprise Tier
- **Cost**: $299+/month (suggested)
- **Max Projects**: Unlimited
- **Max Devices per Project**: Unlimited
- **Max Topics per Project**: Unlimited
- **Rate Limit**: Unlimited
- **Data Retention**: Unlimited
- **Features**:
  - ✅ Analytics
  - ✅ Webhooks
  - ✅ API Access
  - ✅ Priority Support

## Database Setup

### 1. Run Migration

```bash
php artisan migrate
```

This will add the following fields to the `users` table:
- `subscription_tier` (string, default: 'free')
- `subscription_expires_at` (timestamp, nullable)
- `subscription_active` (boolean, default: true)

### 2. Seed Existing Users

```bash
php artisan db:seed --class=SubscriptionSeeder
```

This sets all existing users to the free tier.

## Implementation Details

### Files Created/Modified

#### New Files:
1. **Migration**: `database/migrations/2026_02_06_000000_add_subscription_fields_to_users_table.php`
2. **Model**: `app/Models/SubscriptionPlan.php` - Defines subscription limits
3. **Middleware**: `app/Http/Middleware/CheckSubscriptionLimit.php` - Enforces limits
4. **Controller**: `app/Http/Controllers/SubscriptionController.php` - Manages subscriptions
5. **Seeder**: `database/seeders/SubscriptionSeeder.php`
6. **Documentation**: `SUBSCRIPTION.md` (this file)

#### Modified Files:
1. **app/Models/User.php** - Added subscription methods
2. **app/Http/Controllers/ProjectController.php** - Added limit checks
3. **app/Http/Controllers/DeviceController.php** - Added limit checks
4. **app/Http/Controllers/TopicController.php** - Added limit checks
5. **routes/web.php** - Added subscription routes
6. **routes/api.php** - Added subscription API endpoint

### User Model Methods

```php
// Check if user has active subscription
$user->hasActiveSubscription(); // returns bool

// Get subscription limits
$user->getSubscriptionLimits(); // returns array

// Check if user can create more resources
$user->canCreateProject(); // returns bool
$user->canAddDevice($project); // returns bool
$user->canAddTopic($project); // returns bool

// Check if user has access to premium features
$user->hasFeature('analytics_enabled'); // returns bool
$user->hasFeature('webhooks_enabled'); // returns bool
$user->hasFeature('api_access'); // returns bool
```

### Middleware Usage

Apply the middleware to routes that need subscription checks:

```php
Route::post('/projects', [ProjectController::class, 'store'])
    ->middleware('subscription:create_project');

Route::post('/devices', [DeviceController::class, 'store'])
    ->middleware('subscription:create_device');

Route::post('/topics', [TopicController::class, 'store'])
    ->middleware('subscription:create_topic');

Route::get('/analytics', [AnalyticsController::class, 'index'])
    ->middleware('subscription:analytics');
```

Available middleware actions:
- `create_project`
- `create_device`
- `create_topic`
- `analytics`
- `webhooks`
- `api`

## Routes

### Web Routes (Dashboard)

```
GET  /subscription           - View subscription status
GET  /subscription/upgrade   - View upgrade options
POST /subscription/upgrade   - Process upgrade
POST /subscription/cancel    - Cancel subscription
```

### API Routes

```
GET  /api/subscription/limits - Get current user's limits (requires auth:sanctum)
```

## API Response Examples

### GET /api/subscription/limits

```json
{
  "subscription_tier": "professional",
  "subscription_active": true,
  "subscription_expires_at": "2026-03-06T12:00:00.000000Z",
  "limits": {
    "max_projects": 20,
    "max_devices_per_project": 500,
    "max_topics_per_project": 100,
    "rate_limit_per_hour": 10000,
    "data_retention_days": 365,
    "analytics_enabled": true,
    "webhooks_enabled": true,
    "api_access": true,
    "priority_support": true
  },
  "usage": {
    "projects": 5,
    "devices_total": 42,
    "topics_total": 15
  }
}
```

## Manual Subscription Management

### Upgrade a User

```php
$user = User::find(1);
$user->update([
    'subscription_tier' => 'professional',
    'subscription_active' => true,
    'subscription_expires_at' => now()->addMonth()
]);
```

### Downgrade to Free

```php
$user->update([
    'subscription_tier' => 'free',
    'subscription_active' => true,
    'subscription_expires_at' => null
]);
```

### Check Usage

```php
$limits = $user->getSubscriptionLimits();
$projectCount = $user->projects()->count();
$canCreate = $user->canCreateProject();
```

## Payment Gateway Integration

The `SubscriptionController::processUpgrade()` method includes a placeholder for payment processing. Integrate with:

- **Stripe**: Use Laravel Cashier
- **PayPal**: Use PayPal SDK
- **Paddle**: Use Paddle SDK

Example with Laravel Cashier (Stripe):

```bash
composer require laravel/cashier
php artisan cashier:install
```

```php
// In SubscriptionController::processUpgrade()
$user->newSubscription('default', 'price_professional_monthly')
    ->create($request->payment_method);

$user->update([
    'subscription_tier' => 'professional',
    'subscription_active' => true,
]);
```

## Automated Subscription Expiry

Create a scheduled command to check for expired subscriptions:

```bash
php artisan make:command CheckExpiredSubscriptions
```

```php
// In app/Console/Commands/CheckExpiredSubscriptions.php
public function handle()
{
    User::where('subscription_active', true)
        ->whereNotNull('subscription_expires_at')
        ->where('subscription_expires_at', '<', now())
        ->update([
            'subscription_active' => false,
            'subscription_tier' => 'free'
        ]);
}

// In app/Console/Kernel.php
protected function schedule(Schedule $schedule)
{
    $schedule->command('subscriptions:check-expired')->daily();
}
```

## Frontend Views (TODO)

Create the following Blade views:

1. `resources/views/dashboard/subscription/index.blade.php` - Subscription status page
2. `resources/views/dashboard/subscription/upgrade.blade.php` - Upgrade options page

## Testing

```php
// Test subscription limits
$user = User::factory()->create(['subscription_tier' => 'free']);
assertTrue($user->canCreateProject());

// Create max projects
Project::factory()->count(1)->create(['user_id' => $user->id]);
assertFalse($user->canCreateProject());

// Upgrade and test again
$user->update(['subscription_tier' => 'professional']);
assertTrue($user->canCreateProject());
```

## Next Steps

1. ✅ Database migration created
2. ✅ Models and controllers created
3. ✅ Middleware implemented
4. ✅ Routes added
5. ⏳ Create Blade views for subscription management
6. ⏳ Integrate payment gateway (Stripe/PayPal)
7. ⏳ Add automated expiry checking
8. ⏳ Implement analytics dashboard (professional+ only)
9. ⏳ Implement webhooks system (professional+ only)
10. ⏳ Add rate limiting per subscription tier

## Support

For questions or issues, please contact your development team or refer to the Laravel documentation.
