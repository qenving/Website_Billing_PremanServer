# API Documentation

## Overview

The HBM Billing System provides a comprehensive RESTful API for integrating with third-party applications, mobile apps, and automation tools. The API uses Laravel Sanctum for authentication and follows REST principles.

**Base URL**: `https://yourdomain.com/api/v1`

## Authentication

### Obtaining an API Token

To access protected endpoints, you must first obtain an API token by logging in.

**Endpoint**: `POST /api/v1/auth/login`

**Request Body**:
```json
{
    "email": "user@example.com",
    "password": "your-password",
    "device_name": "My Application"
}
```

**Response** (200 OK):
```json
{
    "token": "1|abc123...",
    "token_type": "Bearer",
    "user": {
        "id": 1,
        "name": "John Doe",
        "email": "user@example.com",
        "role": "client"
    }
}
```

**Error Response** (422 Unprocessable Entity):
```json
{
    "message": "The provided credentials are incorrect.",
    "errors": {
        "email": ["The provided credentials are incorrect."]
    }
}
```

### Using the Token

Include the token in the `Authorization` header for all subsequent requests:

```
Authorization: Bearer 1|abc123...
```

### Token Abilities

Tokens are issued with specific abilities based on user role:

**Admin Abilities**:
- `*` (all abilities)

**Client Abilities**:
- `services:read`, `services:update`
- `invoices:read`
- `tickets:read`, `tickets:create`, `tickets:update`
- `profile:read`, `profile:update`

## Authentication Endpoints

### Login
`POST /api/v1/auth/login`

Obtain an API token.

### Logout Current Token
`POST /api/v1/auth/logout`

Revoke the current access token.

**Response**:
```json
{
    "message": "Token revoked successfully"
}
```

### Logout All Tokens
`POST /api/v1/auth/logout-all`

Revoke all access tokens for the authenticated user.

**Response**:
```json
{
    "message": "All tokens revoked successfully"
}
```

### Get Current User
`GET /api/v1/auth/me`

Get information about the authenticated user.

**Response**:
```json
{
    "user": {
        "id": 1,
        "name": "John Doe",
        "email": "user@example.com",
        "role": "client",
        "language": "en",
        "created_at": "2024-01-15 10:30:00"
    }
}
```

## Client API Endpoints

### Services

#### List Services
`GET /api/v1/services`

Get all services for the authenticated client.

**Query Parameters**:
- `status` (optional): Filter by status (active, suspended, cancelled, etc.)
- `per_page` (optional): Results per page (default: 15)
- `page` (optional): Page number

**Response**:
```json
{
    "data": [
        {
            "id": 1,
            "product": {
                "id": 5,
                "name": "Web Hosting Starter"
            },
            "domain": "example.com",
            "username": "example",
            "status": "active",
            "billing_cycle": "monthly",
            "price": "9.99",
            "registration_date": "2024-01-15 10:00:00",
            "next_due_date": "2024-02-15 10:00:00",
            "termination_date": null,
            "dedicated_ip": "192.168.1.100",
            "created_at": "2024-01-15 10:00:00",
            "updated_at": "2024-01-15 10:00:00"
        }
    ],
    "links": {...},
    "meta": {...}
}
```

#### Get Service Details
`GET /api/v1/services/{service}`

Get details of a specific service.

**Response**: Same structure as individual service object above.

#### Perform Service Action
`POST /api/v1/services/{service}/action`

Perform an action on a service (e.g., restart, reboot).

**Request Body**:
```json
{
    "action": "restart"
}
```

**Supported Actions**:
- `restart`: Restart service
- `reboot`: Reboot server (if applicable)

**Response**:
```json
{
    "success": true,
    "message": "Service restart initiated successfully"
}
```

### Invoices

#### List Invoices
`GET /api/v1/invoices`

Get all invoices for the authenticated client.

**Query Parameters**:
- `status` (optional): Filter by status (unpaid, paid, cancelled)
- `per_page` (optional): Results per page
- `page` (optional): Page number

**Response**:
```json
{
    "data": [
        {
            "id": 1,
            "invoice_number": "INV-001",
            "items": [
                {
                    "id": 1,
                    "description": "Web Hosting - January 2024",
                    "quantity": 1,
                    "unit_price": "9.99",
                    "amount": "9.99"
                }
            ],
            "subtotal": "9.99",
            "tax": "0.00",
            "total": "9.99",
            "status": "unpaid",
            "invoice_date": "2024-01-15",
            "due_date": "2024-01-22",
            "paid_date": null,
            "notes": null,
            "created_at": "2024-01-15 10:00:00",
            "updated_at": "2024-01-15 10:00:00"
        }
    ],
    "links": {...},
    "meta": {...}
}
```

#### Get Invoice Details
`GET /api/v1/invoices/{invoice}`

Get details of a specific invoice.

**Response**: Same structure as individual invoice object above.

### Tickets

#### List Tickets
`GET /api/v1/tickets`

Get all support tickets for the authenticated client.

**Query Parameters**:
- `status` (optional): Filter by status (open, pending, closed)
- `department` (optional): Filter by department
- `per_page` (optional): Results per page
- `page` (optional): Page number

**Response**:
```json
{
    "data": [
        {
            "id": 1,
            "subject": "Need help with email setup",
            "department": "technical",
            "priority": "medium",
            "status": "open",
            "created_at": "2024-01-15 10:00:00",
            "updated_at": "2024-01-15 10:00:00"
        }
    ],
    "links": {...},
    "meta": {...}
}
```

#### Get Ticket Details
`GET /api/v1/tickets/{ticket}`

Get details and replies of a specific ticket.

**Response**:
```json
{
    "id": 1,
    "subject": "Need help with email setup",
    "department": "technical",
    "priority": "medium",
    "status": "open",
    "replies": [
        {
            "id": 1,
            "message": "I need help setting up email for my domain",
            "user": {
                "id": 1,
                "name": "John Doe"
            },
            "created_at": "2024-01-15 10:00:00"
        }
    ],
    "created_at": "2024-01-15 10:00:00",
    "updated_at": "2024-01-15 10:00:00"
}
```

#### Create Ticket
`POST /api/v1/tickets`

Create a new support ticket.

**Request Body**:
```json
{
    "subject": "Need help with email setup",
    "department": "technical",
    "priority": "medium",
    "message": "I need help setting up email for my domain"
}
```

**Response** (201 Created):
```json
{
    "id": 1,
    "subject": "Need help with email setup",
    "department": "technical",
    "priority": "medium",
    "status": "open",
    "created_at": "2024-01-15 10:00:00"
}
```

#### Reply to Ticket
`POST /api/v1/tickets/{ticket}/reply`

Add a reply to an existing ticket.

**Request Body**:
```json
{
    "message": "I've tried the steps you suggested but still having issues"
}
```

**Response** (201 Created):
```json
{
    "id": 2,
    "message": "I've tried the steps you suggested but still having issues",
    "user": {
        "id": 1,
        "name": "John Doe"
    },
    "created_at": "2024-01-15 11:00:00"
}
```

## Admin API Endpoints

**Note**: All admin endpoints require an admin role and the `admin` middleware.

### Dashboard Stats
`GET /api/v1/admin/stats`

Get dashboard statistics.

**Response**:
```json
{
    "total_clients": 150,
    "active_services": 342,
    "total_revenue": "25430.50",
    "unpaid_invoices": 45,
    "open_tickets": 23,
    "mrr": "8500.00"
}
```

### Financial Overview
`GET /api/v1/admin/financial`

Get financial overview and metrics.

**Response**:
```json
{
    "revenue_today": "1250.00",
    "revenue_this_month": "18500.00",
    "revenue_this_year": "125000.00",
    "outstanding_balance": "5600.00",
    "mrr": "8500.00",
    "arr": "102000.00"
}
```

## Rate Limiting

API requests are rate-limited to prevent abuse:

- **Authenticated Requests**: 60 requests per minute
- **Authentication Endpoint**: 5 requests per minute

When rate limit is exceeded, you'll receive a `429 Too Many Requests` response:

```json
{
    "message": "Too Many Attempts."
}
```

The response includes headers indicating rate limit status:
- `X-RateLimit-Limit`: Total requests allowed
- `X-RateLimit-Remaining`: Remaining requests
- `Retry-After`: Seconds until rate limit resets

## Error Responses

The API uses standard HTTP status codes:

| Code | Meaning |
|------|---------|
| 200 | OK - Request successful |
| 201 | Created - Resource created successfully |
| 400 | Bad Request - Invalid request |
| 401 | Unauthorized - Invalid or missing token |
| 403 | Forbidden - Insufficient permissions |
| 404 | Not Found - Resource not found |
| 422 | Unprocessable Entity - Validation error |
| 429 | Too Many Requests - Rate limit exceeded |
| 500 | Internal Server Error - Server error |

**Error Response Format**:
```json
{
    "message": "Error message here",
    "errors": {
        "field_name": [
            "Validation error message"
        ]
    }
}
```

## Pagination

List endpoints return paginated results:

```json
{
    "data": [...],
    "links": {
        "first": "https://api.example.com/v1/services?page=1",
        "last": "https://api.example.com/v1/services?page=5",
        "prev": null,
        "next": "https://api.example.com/v1/services?page=2"
    },
    "meta": {
        "current_page": 1,
        "from": 1,
        "last_page": 5,
        "per_page": 15,
        "to": 15,
        "total": 75
    }
}
```

## Examples

### cURL Example

```bash
# Login and get token
curl -X POST https://yourdomain.com/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"email":"user@example.com","password":"password","device_name":"curl"}'

# Use token to get services
curl -X GET https://yourdomain.com/api/v1/services \
  -H "Authorization: Bearer 1|abc123..." \
  -H "Accept: application/json"
```

### PHP Example

```php
// Login
$ch = curl_init('https://yourdomain.com/api/v1/auth/login');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    'email' => 'user@example.com',
    'password' => 'password',
    'device_name' => 'php-app'
]));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json'
]);

$response = json_decode(curl_exec($ch), true);
$token = $response['token'];

// Get services
$ch = curl_init('https://yourdomain.com/api/v1/services');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $token,
    'Accept: application/json'
]);

$services = json_decode(curl_exec($ch), true);
```

### JavaScript Example

```javascript
// Login
const login = await fetch('https://yourdomain.com/api/v1/auth/login', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
    },
    body: JSON.stringify({
        email: 'user@example.com',
        password: 'password',
        device_name: 'js-app'
    })
});

const { token } = await login.json();

// Get services
const services = await fetch('https://yourdomain.com/api/v1/services', {
    headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
    }
});

const data = await services.json();
console.log(data);
```

### Python Example

```python
import requests

# Login
response = requests.post('https://yourdomain.com/api/v1/auth/login', json={
    'email': 'user@example.com',
    'password': 'password',
    'device_name': 'python-app'
}, headers={
    'Accept': 'application/json'
})

token = response.json()['token']

# Get services
services = requests.get('https://yourdomain.com/api/v1/services', headers={
    'Authorization': f'Bearer {token}',
    'Accept': 'application/json'
})

print(services.json())
```

## Webhooks

The API supports webhooks for payment gateway callbacks.

**Endpoint**: `POST /api/v1/webhooks/payment/{gateway}`

Example: `POST /api/v1/webhooks/payment/stripe`

Webhooks are not rate-limited and do not require authentication (they use gateway-specific verification).

## Best Practices

1. **Store tokens securely**: Never expose API tokens in client-side code
2. **Use HTTPS**: Always use HTTPS for API requests
3. **Handle rate limits**: Implement exponential backoff for rate limit errors
4. **Check response status**: Always check HTTP status codes
5. **Use pagination**: Don't try to fetch all records at once
6. **Log errors**: Log API errors for debugging
7. **Token rotation**: Regularly rotate API tokens for security
8. **Validate data**: Validate data before sending to API

## Changelog

### v1.0.0 (2024-01-15)
- Initial API release
- Authentication endpoints
- Client endpoints (services, invoices, tickets)
- Admin endpoints (stats, financial)
- Rate limiting
- Sanctum integration

## Support

For API support:
- Documentation: `/API_DOCUMENTATION.md`
- Issues: Submit via GitHub
- Email: support@example.com

## License

This API is part of the HBM Billing System and follows the same license.
