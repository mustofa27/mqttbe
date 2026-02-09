# Usage Dashboard & Analytics - Implementation Summary

## What's Been Added

### 1. **Database & Models**

**Migrations:**
- `2026_02_09_100000_create_usage_logs_table.php` - Tracks hourly/daily message usage per project
- `2026_02_09_100001_create_messages_table.php` - Stores MQTT messages with auto-expiration
- `2026_02_09_100002_create_api_keys_table.php` - API authentication tokens

**Models:**
- `UsageLog` - Represents usage records
- `Message` - Represents stored MQTT messages
- `ApiKey` - Represents API authentication keys

---

### 2. **Services**

**UsageTrackingService** (`app/Services/UsageTrackingService.php`)

Methods:
- `recordMessage($projectId, $count = 1)` - Log message sent
- `getCurrentHourUsage($projectId)` - Get messages sent in current hour
- `getCurrentDayUsage($projectId)` - Get messages sent today
- `hasExceededRateLimit($projectId)` - Check if rate limit reached
- `getUserLimits($user)` - Get subscription plan limits
- `getTotalHourlyUsage($userId)` - Get total usage across all projects
- `getUsageSummary($projectId, $from, $to)` - Get usage analytics with daily breakdown

---

### 3. **Controllers**

**UsageController** (`app/Http/Controllers/UsageController.php`)

Routes:
- `GET /usage` → `dashboard()` - Main usage dashboard
- `GET /usage/project/{id}` → `projectUsage()` - Project-specific usage details

---

### 4. **Views**

**Usage Dashboard** (`resources/views/dashboard/usage.blade.php`)
- Current hour usage with progress bar
- Current subscription plan info
- Project count and rate limit display
- Projects usage table with links to detailed views
- Plan features & limits reference card
- Upgrade prompt for free users

**Project Usage Details** (`resources/views/dashboard/project-usage.blade.php`)
- Current hour usage for project
- Daily usage breakdown with bar chart
- Filterable date range
- Daily usage table with percentages
- Project topics list

---

### 5. **Routes Added**

```php
Route::get('/usage', [UsageController::class, 'dashboard'])->name('usage.dashboard');
Route::get('/usage/project/{project}', [UsageController::class, 'projectUsage'])->name('usage.project');
```

---

### 6. **REST API Implementation**

**API Routes** (`routes/api.php` - prefix `/api/v1`)

**Authentication:** Bearer token via `Authorization` header

Endpoints:
```
GET    /api/v1/projects              - List projects
POST   /api/v1/projects              - Create project
GET    /api/v1/projects/{id}         - Get project details
PUT    /api/v1/projects/{id}         - Update project
DELETE /api/v1/projects/{id}         - Delete project

GET    /api/v1/devices               - List devices (requires project_id)
POST   /api/v1/devices               - Create device
GET    /api/v1/devices/{id}          - Get device details
PUT    /api/v1/devices/{id}          - Update device
DELETE /api/v1/devices/{id}          - Delete device

GET    /api/v1/messages              - List messages (requires project_id)
POST   /api/v1/messages              - Publish message (enforces rate limits)
GET    /api/v1/messages/{id}         - Get message details
DELETE /api/v1/messages/{id}         - Delete message

GET    /api/v1/api-keys              - List API keys
POST   /api/v1/api-keys              - Generate new API key
POST   /api/v1/api-keys/{id}/deactivate - Deactivate key
DELETE /api/v1/api-keys/{id}         - Delete key
```

**API Controllers:**
- `app/Http/Controllers/Api/ProjectController.php`
- `app/Http/Controllers/Api/DeviceController.php`
- `app/Http/Controllers/Api/MessageController.php`
- `app/Http/Controllers/Api/ApiKeyController.php`

**Middleware:**
- `app/Http/Middleware/ValidateApiKey.php` - Authenticate API requests
- `app/Http/Middleware/EnforcePlanLimits.php` - Check subscription status and limits

---

### 7. **Features**

✅ **Usage Tracking**
- Hourly message logging
- Per-project and per-user tracking
- Rate limit enforcement

✅ **Plan Enforcement**
- Subscription expiration checks
- Feature availability by plan
- Device/topic/project limits
- Rate limit checking

✅ **Analytics Dashboard**
- Current usage summary
- Per-project usage stats
- 30-day usage history
- Daily breakdown charts
- Date range filtering

✅ **API Management**
- Generate API keys
- Deactivate/delete keys
- Track last used time
- Expiration support

✅ **Message Storage**
- Auto-expiration based on plan
- Full message retrieval via API
- Message deletion support

---

### 8. **Usage Examples**

**Getting API Key:**
1. Login to dashboard
2. Go to Dashboard → Usage & Analytics
3. API key management section (coming soon - need to add view)
4. Generate new key
5. Copy and save key (shown only once)

**Using API:**
```bash
# Create a message
curl -X POST https://yourdomain.com/api/v1/messages \
  -H "Authorization: Bearer YOUR_API_KEY" \
  -H "Content-Type: application/json" \
  -d '{
    "project_id": 1,
    "topic_id": 1,
    "payload": "temperature: 22.5",
    "qos": 1
  }'

# List messages
curl https://yourdomain.com/api/v1/messages?project_id=1 \
  -H "Authorization: Bearer YOUR_API_KEY"
```

**Viewing Usage:**
1. Login to dashboard
2. Click "Usage & Analytics" in sidebar
3. View overall stats and projects table
4. Click "View Details" on any project for detailed analytics

---

### 9. **Next Steps**

To complete the system:
1. Run migrations: `php artisan migrate`
2. Create API key management view for dashboard
3. Implement webhook integration (Phase 2)
4. Add advanced analytics charts (Phase 2)
5. Test API endpoints with Postman/curl

---

### 10. **Database Structure**

**usage_logs** table:
- id, project_id, user_id, message_count, period_start, period_end, period_type, timestamps

**messages** table:
- id, project_id, topic_id, payload, qos, retained, expires_at, timestamps

**api_keys** table:
- id, user_id, name, key (hashed), secret (hashed), last_used_at, expires_at, is_active, timestamps

---

## Sidebar Menu Updated

Added "📈 Usage & Analytics" menu item in dashboard sidebar for quick access to:
- Overall usage dashboard
- Project-specific analytics
- Plan limits and features
- API key management (will be added)

---

## API Documentation

See [API_DOCUMENTATION.md](../API_DOCUMENTATION.md) for complete API reference with examples in Python, JavaScript, and cURL.
