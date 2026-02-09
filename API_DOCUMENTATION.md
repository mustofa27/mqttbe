# ICMQTT REST API v1 Documentation

## Authentication

All API requests (except public endpoints) require a Bearer token in the `Authorization` header:

```
Authorization: Bearer YOUR_API_KEY
```

### Getting an API Key

1. Go to your dashboard
2. Navigate to "API Keys" section
3. Click "Generate New Key"
4. Save the key and secret immediately (they won't be shown again)

## Base URL

```
https://yourdomain.com/api/v1
```

## Response Format

All responses are in JSON format:

### Success Response (2xx)
```json
{
  "data": { /* resource data */ },
  "pagination": { /* if applicable */ }
}
```

### Error Response (4xx, 5xx)
```json
{
  "error": "Error code",
  "message": "Human readable error message"
}
```

## Rate Limiting

Rate limits depend on your subscription plan:
- **Free**: 100 messages/hour
- **Starter**: 1,000 messages/hour
- **Professional**: 10,000 messages/hour
- **Enterprise**: Unlimited

When you exceed the limit, you'll receive a 429 response:
```json
{
  "error": "Rate limit exceeded",
  "message": "You have exceeded your hourly message limit of 1000"
}
```

## Projects

### List All Projects
```
GET /projects
```

Query Parameters:
- `page` (int, optional): Page number for pagination

Response:
```json
{
  "data": [
    {
      "id": 1,
      "name": "Smart Home",
      "description": "IoT home automation",
      "user_id": 1,
      "created_at": "2026-02-09T10:00:00Z",
      "updated_at": "2026-02-09T10:00:00Z"
    }
  ],
  "pagination": {
    "total": 10,
    "per_page": 15,
    "current_page": 1,
    "last_page": 1
  },
  "limits": {
    "max_projects": 20,
    "current_projects": 1
  }
}
```

### Get Project Details
```
GET /projects/{projectId}
```

Response:
```json
{
  "data": {
    "id": 1,
    "name": "Smart Home",
    "description": "IoT home automation",
    "user_id": 1,
    "created_at": "2026-02-09T10:00:00Z",
    "updated_at": "2026-02-09T10:00:00Z"
  },
  "devices_count": 5,
  "topics_count": 3
}
```

### Create Project
```
POST /projects
```

Request Body:
```json
{
  "name": "Smart Home",
  "description": "IoT home automation"
}
```

Response: `201 Created`

### Update Project
```
PUT /projects/{projectId}
```

Request Body:
```json
{
  "name": "Smart Home Updated",
  "description": "Updated description"
}
```

### Delete Project
```
DELETE /projects/{projectId}
```

Response: `200 OK`

---

## Devices

### List Devices
```
GET /devices?project_id=1
```

Query Parameters:
- `project_id` (int, **required**): Filter by project
- `page` (int, optional): Page number

Response:
```json
{
  "data": [
    {
      "id": 1,
      "project_id": 1,
      "name": "Living Room Sensor",
      "device_id": "sensor_001",
      "status": "online",
      "created_at": "2026-02-09T10:00:00Z"
    }
  ],
  "pagination": {
    "total": 5,
    "per_page": 15,
    "current_page": 1
  }
}
```

### Get Device Details
```
GET /devices/{deviceId}
```

### Create Device
```
POST /devices
```

Request Body:
```json
{
  "project_id": 1,
  "name": "Living Room Sensor",
  "device_id": "sensor_001",
  "status": "online"
}
```

Validation Rules:
- `project_id`: Required, must exist
- `name`: Required, max 255 characters
- `device_id`: Required, unique across system
- `status`: Optional, must be one of: `online`, `offline`, `inactive`

### Update Device
```
PUT /devices/{deviceId}
```

Request Body:
```json
{
  "name": "Updated Name",
  "status": "offline"
}
```

### Delete Device
```
DELETE /devices/{deviceId}
```

---

## Messages

### List Messages
```
GET /messages?project_id=1&topic_id=1&limit=50
```

Query Parameters:
- `project_id` (int, **required**): Filter by project
- `topic_id` (int, optional): Filter by topic
- `limit` (int, optional): Max 1000, default 50

Response:
```json
{
  "data": [
    {
      "id": 1,
      "project_id": 1,
      "topic_id": 1,
      "payload": "temperature: 22.5",
      "qos": 1,
      "retained": false,
      "expires_at": "2026-03-10T10:00:00Z",
      "created_at": "2026-02-09T10:00:00Z"
    }
  ]
}
```

### Get Message Details
```
GET /messages/{messageId}
```

### Publish Message
```
POST /messages
```

Request Body:
```json
{
  "project_id": 1,
  "topic_id": 1,
  "payload": "temperature: 22.5",
  "qos": 1,
  "retained": false
}
```

Validation:
- `project_id`: Required
- `topic_id`: Required
- `payload`: Required, string
- `qos`: Optional, 0-2
- `retained`: Optional, boolean

Errors:
- `429`: Rate limit exceeded
- `422`: Device or topic limit exceeded

### Delete Message
```
DELETE /messages/{messageId}
```

---

## API Keys

### List API Keys
```
GET /api-keys
```

Response:
```json
{
  "data": [
    {
      "id": 1,
      "name": "Production Key",
      "key": "sk_test_...",
      "is_active": true,
      "last_used_at": "2026-02-09T15:30:00Z",
      "expires_at": null,
      "created_at": "2026-02-09T10:00:00Z"
    }
  ],
  "pagination": {
    "total": 3,
    "per_page": 10
  }
}
```

### Create API Key
```
POST /api-keys
```

Request Body:
```json
{
  "name": "Production Key",
  "expires_at": "2027-02-09"
}
```

Response: `201 Created`
```json
{
  "data": {
    "id": 1,
    "name": "Production Key",
    "key": "sk_abc123...",
    "secret": "secret_xyz789...",
    "message": "Save your API key and secret. You will not be able to see them again."
  }
}
```

⚠️ **Important**: The API key and secret are only shown once. Save them securely.

### Deactivate API Key
```
POST /api-keys/{keyId}/deactivate
```

### Delete API Key
```
DELETE /api-keys/{keyId}
```

---

## Error Codes

| Code | Meaning |
|------|---------|
| 400 | Bad Request - Missing required parameter |
| 401 | Unauthorized - Invalid or missing API key |
| 403 | Forbidden - You don't have permission to access this resource |
| 404 | Not Found - Resource doesn't exist |
| 422 | Unprocessable Entity - Validation error |
| 429 | Too Many Requests - Rate limit exceeded |
| 500 | Internal Server Error |

---

## Examples

### Python Example
```python
import requests

API_KEY = "your_api_key"
BASE_URL = "https://yourdomain.com/api/v1"

headers = {
    "Authorization": f"Bearer {API_KEY}",
    "Content-Type": "application/json"
}

# List projects
response = requests.get(f"{BASE_URL}/projects", headers=headers)
print(response.json())

# Create a message
data = {
    "project_id": 1,
    "topic_id": 1,
    "payload": "temperature: 22.5",
    "qos": 1
}
response = requests.post(f"{BASE_URL}/messages", json=data, headers=headers)
print(response.json())
```

### JavaScript/Node.js Example
```javascript
const API_KEY = "your_api_key";
const BASE_URL = "https://yourdomain.com/api/v1";

const headers = {
  "Authorization": `Bearer ${API_KEY}`,
  "Content-Type": "application/json"
};

// List projects
fetch(`${BASE_URL}/projects`, { headers })
  .then(r => r.json())
  .then(data => console.log(data));

// Create a message
fetch(`${BASE_URL}/messages`, {
  method: "POST",
  headers,
  body: JSON.stringify({
    project_id: 1,
    topic_id: 1,
    payload: "temperature: 22.5",
    qos: 1
  })
})
  .then(r => r.json())
  .then(data => console.log(data));
```

### cURL Example
```bash
# List projects
curl -X GET https://yourdomain.com/api/v1/projects \
  -H "Authorization: Bearer YOUR_API_KEY"

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
```

---

## Status Codes

- `200 OK`: Request successful
- `201 Created`: Resource created successfully
- `204 No Content`: Request successful, no content to return
- `400 Bad Request`: Invalid request parameters
- `401 Unauthorized`: Missing or invalid API key
- `403 Forbidden`: Access denied
- `404 Not Found`: Resource not found
- `429 Too Many Requests`: Rate limit exceeded
- `500 Internal Server Error`: Server error
