# Seed Data, Export & Advanced Filtering Guide

## Overview

This guide covers the new features added to the ICMQTT platform:
1. **Comprehensive Seed Data** - Pre-populated test data for development
2. **Export Functionality** - CSV export for messages, usage, and analytics
3. **Advanced Filtering** - Filter, sort, and analyze data with multiple criteria

---

## Part 1: Seed Data

### What's Included

The `TestDataSeeder` creates realistic demo data:

#### Test Users
- **Test User** (testuser@example.com)
  - Tier: Professional
  - Limit: 10,000 messages/month
  - Retention: 365 days

- **Admin User** (admin@example.com)
  - Tier: Enterprise
  - Limit: 100,000 messages/month
  - Retention: 365 days

#### Projects
1. **Smart Home System** (smart_home_2026)
   - 5 devices (Living room, Kitchen, Bedroom, AC, Camera)
   - 4 MQTT topics (temperature, humidity, status, commands)

2. **Weather Monitoring Station** (weather_station)
   - 4 devices (Temperature, Humidity, Pressure, Wind sensors)
   - 4 topics (weather data endpoints)

3. **Industrial IoT Platform** (industrial_iot)
   - 4 devices (Production lines, Temperature monitor, Energy meter)
   - Multiple monitoring topics

#### Data Volume
- **30 days of message history**
- **50-300 messages per day** (realistic distribution)
- **Hourly & daily usage logs** for analytics
- **API keys, webhooks, and alerts** configured

### Running the Seeder

```bash
# Run all seeders
php artisan db:seed

# Run only test data seeder
php artisan db:seed --class=TestDataSeeder

# Fresh database with seeders
php artisan migrate:fresh --seed
```

### Test Credentials

```
Email: testuser@example.com
Email: admin@example.com
Password: password
```

---

## Part 2: Export Functionality

### Available Exports

#### 1. Messages Export
**Route**: `/export/project/{project}/messages`

**Format**: CSV

**Fields**:
- ID
- Project ID
- Device ID
- Topic ID
- Payload (JSON)
- QoS level
- Retained flag
- MQTT topic path
- Created/Expires dates

**Filters**:
```
GET /export/project/1/messages?start_date=2026-02-01&end_date=2026-02-09&device_id=5
```

#### 2. Usage Logs Export
**Route**: `/export/project/{project}/usage`

**Format**: CSV

**Fields**:
- Period (hourly, daily)
- Message count per period
- Timestamp range

#### 3. Analytics Summary
**Route**: `/export/project/{project}/analytics`

**Format**: CSV (includes summary stats)

**Includes**:
- Total messages
- Device/topic breakdown
- QoS distribution
- Payload analysis

#### 4. Device Activity Report
**Route**: `/export/project/{project}/devices`

**Format**: CSV

**Fields Per Device**:
- Device ID & name
- Total messages
- First & last activity
- Average QoS
- QoS distribution (0/1/2 counts)

#### 5. Hourly Statistics
**Route**: `/export/project/{project}/hourly-stats`

**Format**: CSV

**Fields**:
- Hour timestamp
- Message count
- Date (for grouping)

### Using Exports

#### From Web Dashboard
1. Click "📥 Export Data" button
2. Select export type (Messages, Usage, Analytics, Devices, Hourly)
3. Check "Apply current filters" to use active filters
4. Click "Export CSV"

#### Via API
```bash
# Messages export with filters
curl -X GET "http://localhost:8000/export/project/1/messages?start_date=2026-02-01&end_date=2026-02-09" \
  -H "Authorization: Bearer YOUR_API_KEY"

# Device activity with 30-day range
curl -X GET "http://localhost:8000/export/project/1/devices?start_date=2026-01-11&end_date=2026-02-09"

# Analytics summary
curl -X GET "http://localhost:8000/export/project/1/analytics"
```

---

## Part 3: Advanced Filtering

### Filter API Endpoints

#### 1. Filter Messages
**Route**: `/api/v1/filter/project/{project}/messages`

**Parameters**:
```json
{
  "start_date": "2026-02-01",      // ISO date
  "end_date": "2026-02-09",        // ISO date
  "device_id": 5,                  // Optional
  "topic_id": 10,                  // Optional
  "qos": 1,                        // 0, 1, or 2
  "retained": true,                // true/false
  "sort_by": "created_at",         // created_at, qos, payload_size
  "sort_order": "desc",            // asc or desc
  "per_page": 50,                  // 1-100
  "page": 1                        // Pagination
}
```

**Response**:
```json
{
  "data": [
    {
      "id": 1,
      "device_id": 5,
      "topic_id": 10,
      "payload": "{...}",
      "qos": 1,
      "retained": false,
      "payload_size": 256,
      "created_at": "2026-02-09T10:30:00Z"
    }
  ],
  "pagination": {
    "total": 1500,
    "per_page": 50,
    "page": 1,
    "last_page": 30
  },
  "filters": {
    "start_date": "2026-02-01",
    "end_date": "2026-02-09",
    "device_id": 5,
    "qos": 1
  }
}
```

#### 2. Get Filter Options
**Route**: `/api/v1/filter/project/{project}/options`

**Returns**:
```json
{
  "devices": [
    { "id": 1, "device_id": "sensor_01", "device_name": "Living Room Sensor" }
  ],
  "topics": [
    { "id": 1, "topic": "sensors/{deviceId}/temperature" }
  ],
  "qos_options": [
    { "value": 0, "label": "QoS 0 (At Most Once)" }
  ],
  "sort_options": [
    { "value": "created_at", "label": "Date Created" }
  ]
}
```

#### 3. Get Summary Statistics
**Route**: `/api/v1/filter/project/{project}/summary`

**Parameters**:
```json
{
  "start_date": "2026-02-01",
  "end_date": "2026-02-09",
  "device_id": 5  // Optional
}
```

**Response**:
```json
{
  "total_messages": 1500,
  "unique_devices": 5,
  "unique_topics": 8,
  "avg_payload_size": 256.50,
  "qos_distribution": {
    "qos_0": 500,
    "qos_1": 700,
    "qos_2": 300
  },
  "retained_messages": 45,
  "period_start": "2026-02-01T00:00:00Z",
  "period_end": "2026-02-09T23:59:59Z"
}
```

#### 4. Device Activity Report
**Route**: `/api/v1/filter/project/{project}/device-activity`

**Parameters**:
```json
{
  "start_date": "2026-02-01",
  "end_date": "2026-02-09",
  "sort_by": "message_count",    // message_count, last_activity, avg_qos
  "sort_order": "desc"           // asc or desc
}
```

**Response**:
```json
{
  "data": [
    {
      "device_id": "sensor_01",
      "device_name": "Living Room Sensor",
      "message_count": 450,
      "first_message": "2026-02-01T08:00:00Z",
      "last_message": "2026-02-09T22:30:00Z",
      "avg_qos": 1.5,
      "qos_0_count": 150,
      "qos_1_count": 200,
      "qos_2_count": 100
    }
  ],
  "total": 5
}
```

#### 5. Time Series Analysis
**Route**: `/api/v1/filter/project/{project}/time-series`

**Parameters**:
```json
{
  "start_date": "2026-02-01",
  "end_date": "2026-02-09",
  "interval": "daily",    // hourly, daily, weekly
  "device_id": 5          // Optional - single device
}
```

**Response**:
```json
{
  "data": [
    {
      "time": "2026-02-01",
      "count": 125,
      "avg_qos": 1.2
    }
  ],
  "interval": "daily",
  "total_messages": 1500
}
```

### Web Dashboard Advanced Filtering

1. **Open Filters**: Click "🔍 Advanced Filters" button
2. **Set Criteria**:
   - Project selection
   - Date range (start/end)
   - Device filter
   - QoS level
   - Time interval (hourly/daily/weekly)

3. **Apply**: Click "Apply Filters"
4. **Reset**: Click "Reset" to clear all filters

5. **Export**: Click "📥 Export Data" - filters automatically apply if checkbox enabled

### Example Queries

#### Last 7 days of QoS 2 messages for Device 5
```javascript
const params = new URLSearchParams({
  start_date: new Date(Date.now() - 7*24*60*60*1000).toISOString(),
  end_date: new Date().toISOString(),
  device_id: 5,
  qos: 2,
  sort_by: 'created_at',
  sort_order: 'desc',
  per_page: 100
});

fetch(`/api/v1/filter/project/1/messages?${params}`)
  .then(r => r.json())
  .then(data => console.log(data));
```

#### Device activity for last 30 days, sorted by message count
```javascript
const params = new URLSearchParams({
  start_date: new Date(Date.now() - 30*24*60*60*1000).toISOString(),
  end_date: new Date().toISOString(),
  sort_by: 'message_count',
  sort_order: 'desc'
});

fetch(`/api/v1/filter/project/1/device-activity?${params}`)
  .then(r => r.json())
  .then(data => console.log(data));
```

#### Hourly statistics for time series chart
```javascript
const params = new URLSearchParams({
  start_date: new Date(Date.now() - 24*60*60*1000).toISOString(),
  end_date: new Date().toISOString(),
  interval: 'hourly'
});

fetch(`/api/v1/filter/project/1/time-series?${params}`)
  .then(r => r.json())
  .then(data => {
    // data.data contains hourly breakdown
  });
```

---

## API Usage in Code

### Using ExportService

```php
<?php
use App\Services\ExportService;

class ReportController extends Controller {
    public function __construct(private ExportService $exportService) {}

    public function generateReport() {
        $from = Carbon::now()->subDays(30);
        $to = Carbon::now();
        
        // Get filtered messages
        $messages = $this->exportService->getFilteredMessages(
            projectId: 1,
            startDate: $from,
            endDate: $to,
            deviceId: 5,
            qos: 1
        );
        
        // Get summary statistics
        $summary = $this->exportService->getProjectSummary(1, $from, $to);
        
        // Get device activity report
        $devices = $this->exportService->getDeviceActivityReport(1, $from, $to);
        
        // Export as CSV
        $csv = $this->exportService->messagesAsCSV(1, $from, $to, 5);
    }
}
```

### Using FilterController (API)

The `FilterController` is automatically integrated with the REST API and provides:
- Advanced message filtering
- Summary statistics
- Device activity reports
- Time series data
- Dynamic filter options

All endpoints require Bearer token authentication with API key.

---

## File Structure

### New Files Created
```
app/
  Services/
    ExportService.php           # Export and filtering logic
  Http/Controllers/
    ExportController.php        # Export route handlers
    Api/FilterController.php    # Advanced filtering API
resources/views/dashboard/analytics/
  advanced.blade.php           # Enhanced analytics dashboard
database/seeders/
  TestDataSeeder.php           # Test data population
```

### Updated Files
```
database/seeders/DatabaseSeeder.php  # Added TestDataSeeder call
routes/web.php                       # Added export routes
routes/api.php                       # Added filter routes
```

---

## Best Practices

### Exporting Large Datasets
- Use date range filters to limit data
- Export hourly/daily aggregates instead of raw messages
- Consider device-specific exports for better performance

### Filtering Performance
- Use specific date ranges (avoid all-time queries)
- Combine device_id filter for focused analysis
- Sort by indexed columns (created_at) for speed

### API Rate Limiting
- Apply subscription plan limits to export requests
- Cache frequently accessed summaries
- Use time-series aggregates for dashboards

---

## Troubleshooting

### Export Returns Empty
- Check date range overlaps with actual data
- Verify project ID is correct
- Ensure user has permission to view project

### Slow Filter Queries
- Narrow date range
- Add device/topic filters
- Check database indexes on created_at column

### Missing Data in Seeder
- Run `php artisan migrate:fresh --seed`
- Verify database connection
- Check file permissions on seeder class

---

## Next Steps

### Potential Enhancements
1. **PDF Reports** - Generate PDF reports with charts
2. **Scheduled Exports** - Automatic daily/weekly emails
3. **Data Retention Policies** - Automated cleanup
4. **Advanced Alerts** - Filter-based notifications
5. **Custom Reports** - Saved filter templates
6. **Data Warehouse** - BigQuery/ClickHouse integration
7. **Real-time Dashboards** - WebSocket updates

