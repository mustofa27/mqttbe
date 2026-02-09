# Phase 3 Quick Reference

## What's New

### 🌱 Seed Data
Comprehensive test data with:
- 2 test users (Professional & Enterprise tiers)
- 3 projects with realistic MQTT setup
- 13 devices, 11 topics
- **30 days of message history** (50-300 messages/day)
- Hourly & daily usage logs
- API keys, webhooks, alerts

**Run**: `php artisan db:seed --class=TestDataSeeder`

### 📥 Export Functionality
Export analytics and message data as CSV:
- Messages (with all metadata)
- Usage logs
- Analytics summary
- Device activity report
- Hourly statistics

**Web**: Click "📥 Export Data" button on analytics dashboard
**API**: GET `/export/project/{id}/{type}`

### 🔍 Advanced Filtering
Filter messages, analyze device activity, view trends:
- Filter by: date, device, topic, QoS, retained flag
- Sort by: created date, QoS, payload size
- Pagination support
- Time series analysis (hourly/daily/weekly)

**API**: GET `/api/v1/filter/project/{id}/...`

---

## Files Added

```
app/Services/ExportService.php                 Export & filtering logic
app/Http/Controllers/ExportController.php      Export endpoints
app/Http/Controllers/Api/FilterController.php  Advanced filtering API
database/seeders/TestDataSeeder.php            Test data
resources/views/dashboard/analytics/advanced.blade.php  Enhanced UI
```

## Documentation

- `EXPORT_FILTERING_GUIDE.md` - Complete usage guide (API + Web examples)
- `PHASE_3_SUMMARY.md` - Implementation details and architecture

---

## Quick Commands

```bash
# Load test data
php artisan db:seed --class=TestDataSeeder

# Fresh database with all seeders
php artisan migrate:fresh --seed

# Export data via API
curl "http://localhost:8000/export/project/1/messages?start_date=2026-02-01" \
  -o export.csv
```

---

## Test Credentials

```
Email: testuser@example.com
Password: password

Email: admin@example.com
Password: password
```

---

## Key Features

✅ **Export as CSV** - All analytics and message data
✅ **Advanced Filters** - Complex queries with multiple criteria
✅ **Device Analytics** - Per-device performance metrics
✅ **Time Series** - Hourly/daily/weekly trends
✅ **Device Activity** - Device performance rankings
✅ **Real-time Charts** - Chart.js powered dashboards
✅ **Responsive UI** - Mobile-friendly interface
✅ **API Integration** - Full REST API for filtering
✅ **Authorization** - Respects user permissions
✅ **Performance** - Optimized queries and pagination

---

## API Endpoints Summary

```
Filter Endpoints (all under /api/v1/filter/project/{id}):
  GET /messages              - Filter with pagination & sorting
  GET /options               - Available filters for UI
  GET /summary               - Summary statistics
  GET /device-activity       - Per-device report
  GET /time-series           - Trends and aggregates

Export Endpoints (all under /export/project/{id}):
  GET /messages              - Export messages CSV
  GET /usage                 - Export usage logs CSV
  GET /analytics             - Export summary CSV
  GET /devices               - Export device activity CSV
  GET /hourly-stats          - Export hourly stats CSV
```

---

## Configuration

All features use existing configuration:
- Database: `config/database.php`
- Authentication: `config/auth.php`
- No new environment variables required

Optional: Adjust export limits in `ExportController.php` if needed.

---

## Performance Tips

1. **Date Ranges** - Always use specific date ranges for filters
2. **Pagination** - Use `per_page` parameter (default 50, max 100)
3. **Device Filter** - Add device_id to narrow results
4. **Indexing** - Database already indexes key columns
5. **Caching** - Consider caching filter options with Redis

---

## Support & Examples

See full documentation:
- **Usage Guide**: `EXPORT_FILTERING_GUIDE.md`
- **Architecture**: `PHASE_3_SUMMARY.md`
- **Code**: Check controller PHPDoc blocks for detailed parameters

Complete examples for:
- Web dashboard usage
- cURL API calls
- JavaScript/Node.js integration
- Database queries

