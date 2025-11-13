# Vendor & Purchase Order API Documentation

## Base URL
```
http://your-domain.test/api
```

## Authentication
Currently, the API endpoints are open. To add authentication, wrap routes in `auth:sanctum` middleware in `routes/api.php`.

---

## Vendor Management API

### 1. List All Vendors
**Endpoint:** `GET /api/vendors`

**Query Parameters:**
- `search` (optional): Search by name, email, phone, or address
- `sort_by` (optional): Sort by field (name, email, phone, created_at). Default: created_at
- `sort_dir` (optional): Sort direction (asc, desc). Default: desc
- `page` (optional): Page number. Default: 1
- `per_page` (optional): Items per page (1-100). Default: 10

**Example Request:**
```bash
GET /api/vendors?search=ABC&sort_by=name&sort_dir=asc&per_page=20
```

**Success Response (200 OK):**
```json
{
  "current_page": 1,
  "data": [
    {
      "id": 1,
      "name": "ABC Supplies Ltd",
      "email": "contact@abcsupplies.com",
      "phone": "+1234567890",
      "address": "123 Business Street, City, Country",
      "created_at": "2025-11-13T07:35:00.000000Z",
      "updated_at": "2025-11-13T07:35:00.000000Z",
      "deleted_at": null
    }
  ],
  "first_page_url": "http://your-domain.test/api/vendors?page=1",
  "from": 1,
  "last_page": 1,
  "last_page_url": "http://your-domain.test/api/vendors?page=1",
  "links": [...],
  "next_page_url": null,
  "path": "http://your-domain.test/api/vendors",
  "per_page": 10,
  "prev_page_url": null,
  "to": 1,
  "total": 1
}
```

---

### 2. Create a New Vendor
**Endpoint:** `POST /api/vendors`

**Request Headers:**
```
Content-Type: application/json
Accept: application/json
```

**Request Body:**
```json
{
  "name": "ABC Supplies Ltd",
  "email": "contact@abcsupplies.com",
  "phone": "+1234567890",
  "address": "123 Business Street, City, Country"
}
```

**Validation Rules:**
- `name`: required, string, max 255 characters
- `email`: optional, valid email, max 255 characters
- `phone`: optional, string, max 20 characters
- `address`: optional, string, max 500 characters

**Success Response (201 Created):**
```json
{
  "message": "Vendor created successfully.",
  "vendor": {
    "id": 1,
    "name": "ABC Supplies Ltd",
    "email": "contact@abcsupplies.com",
    "phone": "+1234567890",
    "address": "123 Business Street, City, Country",
    "created_at": "2025-11-13T07:35:00.000000Z",
    "updated_at": "2025-11-13T07:35:00.000000Z",
    "deleted_at": null
  }
}
```

**Error Response (422 Unprocessable Entity):**
```json
{
  "message": "The name field is required.",
  "errors": {
    "name": ["The name field is required."]
  }
}
```

---

### 3. Get a Specific Vendor
**Endpoint:** `GET /api/vendors/{id}`

**Example Request:**
```bash
GET /api/vendors/1
```

**Success Response (200 OK):**
```json
{
  "id": 1,
  "name": "ABC Supplies Ltd",
  "email": "contact@abcsupplies.com",
  "phone": "+1234567890",
  "address": "123 Business Street, City, Country",
  "created_at": "2025-11-13T07:35:00.000000Z",
  "updated_at": "2025-11-13T07:35:00.000000Z",
  "deleted_at": null,
  "purchase_orders": [
    {
      "id": 1,
      "vendor_id": 1,
      "order_number": "PO-20251113-0001",
      "details": "Office supplies for Q1 2025",
      "status": "Pending",
      "created_at": "2025-11-13T08:00:00.000000Z",
      "updated_at": "2025-11-13T08:00:00.000000Z",
      "deleted_at": null
    }
  ]
}
```

**Error Response (404 Not Found):**
```json
{
  "message": "No query results for model [App\\Models\\Vendor] 999"
}
```

---

### 4. Update a Vendor
**Endpoint:** `PUT /api/vendors/{id}` or `PATCH /api/vendors/{id}`

**Request Headers:**
```
Content-Type: application/json
Accept: application/json
```

**Request Body:**
```json
{
  "name": "ABC Supplies Ltd (Updated)",
  "email": "newemail@abcsupplies.com",
  "phone": "+9876543210",
  "address": "456 New Address, City, Country"
}
```

**Success Response (200 OK):**
```json
{
  "message": "Vendor updated successfully.",
  "vendor": {
    "id": 1,
    "name": "ABC Supplies Ltd (Updated)",
    "email": "newemail@abcsupplies.com",
    "phone": "+9876543210",
    "address": "456 New Address, City, Country",
    "created_at": "2025-11-13T07:35:00.000000Z",
    "updated_at": "2025-11-13T08:30:00.000000Z",
    "deleted_at": null
  }
}
```

---

### 5. Delete a Vendor
**Endpoint:** `DELETE /api/vendors/{id}`

**Success Response (200 OK):**
```json
{
  "message": "Vendor deleted successfully."
}
```

**Error Response (422 Unprocessable Entity):**
```json
{
  "message": "Cannot delete vendor with existing purchase orders."
}
```

---

## Purchase Order Management API

### 1. List All Purchase Orders
**Endpoint:** `GET /api/purchase-orders`

**Query Parameters:**
- `search` (optional): Search by order number, details, or vendor name
- `status` (optional): Filter by status (Pending, Approved, Completed)
- `vendor_id` (optional): Filter by vendor ID
- `from_date` (optional): Filter from date (YYYY-MM-DD)
- `to_date` (optional): Filter to date (YYYY-MM-DD)
- `sort_by` (optional): Sort by field (order_number, status, created_at). Default: created_at
- `sort_dir` (optional): Sort direction (asc, desc). Default: desc
- `page` (optional): Page number. Default: 1
- `per_page` (optional): Items per page (1-100). Default: 10

**Example Request:**
```bash
GET /api/purchase-orders?status=Pending&vendor_id=1&from_date=2025-01-01&to_date=2025-12-31
```

**Success Response (200 OK):**
```json
{
  "current_page": 1,
  "data": [
    {
      "id": 1,
      "vendor_id": 1,
      "order_number": "PO-20251113-0001",
      "details": "Office supplies for Q1 2025",
      "status": "Pending",
      "created_at": "2025-11-13T08:00:00.000000Z",
      "updated_at": "2025-11-13T08:00:00.000000Z",
      "deleted_at": null,
      "vendor": {
        "id": 1,
        "name": "ABC Supplies Ltd",
        "email": "contact@abcsupplies.com",
        "phone": "+1234567890",
        "address": "123 Business Street, City, Country"
      }
    }
  ],
  "first_page_url": "http://your-domain.test/api/purchase-orders?page=1",
  "from": 1,
  "last_page": 1,
  "last_page_url": "http://your-domain.test/api/purchase-orders?page=1",
  "links": [...],
  "next_page_url": null,
  "path": "http://your-domain.test/api/purchase-orders",
  "per_page": 10,
  "prev_page_url": null,
  "to": 1,
  "total": 1
}
```

---

### 2. Create a New Purchase Order
**Endpoint:** `POST /api/purchase-orders`

**Request Headers:**
```
Content-Type: application/json
Accept: application/json
```

**Request Body:**
```json
{
  "vendor_id": 1,
  "details": "Office supplies for Q1 2025",
  "status": "Pending"
}
```

**Validation Rules:**
- `vendor_id`: required, must exist in vendors table
- `details`: optional, string, max 1000 characters
- `status`: optional, must be one of: Pending, Approved, Completed (default: Pending)

**Note:** The `order_number` is automatically generated in the format: `PO-YYYYMMDD-XXXX`

**Success Response (201 Created):**
```json
{
  "message": "Purchase order created successfully.",
  "purchase_order": {
    "id": 1,
    "vendor_id": 1,
    "order_number": "PO-20251113-0001",
    "details": "Office supplies for Q1 2025",
    "status": "Pending",
    "created_at": "2025-11-13T08:00:00.000000Z",
    "updated_at": "2025-11-13T08:00:00.000000Z",
    "deleted_at": null,
    "vendor": {
      "id": 1,
      "name": "ABC Supplies Ltd",
      "email": "contact@abcsupplies.com",
      "phone": "+1234567890",
      "address": "123 Business Street, City, Country"
    }
  }
}
```

**Error Response (422 Unprocessable Entity):**
```json
{
  "message": "The vendor id field is required.",
  "errors": {
    "vendor_id": ["The vendor id field is required."]
  }
}
```

---

### 3. Get a Specific Purchase Order
**Endpoint:** `GET /api/purchase-orders/{id}`

**Example Request:**
```bash
GET /api/purchase-orders/1
```

**Success Response (200 OK):**
```json
{
  "id": 1,
  "vendor_id": 1,
  "order_number": "PO-20251113-0001",
  "details": "Office supplies for Q1 2025",
  "status": "Pending",
  "created_at": "2025-11-13T08:00:00.000000Z",
  "updated_at": "2025-11-13T08:00:00.000000Z",
  "deleted_at": null,
  "vendor": {
    "id": 1,
    "name": "ABC Supplies Ltd",
    "email": "contact@abcsupplies.com",
    "phone": "+1234567890",
    "address": "123 Business Street, City, Country"
  }
}
```

---

### 4. Update a Purchase Order
**Endpoint:** `PUT /api/purchase-orders/{id}` or `PATCH /api/purchase-orders/{id}`

**Request Headers:**
```
Content-Type: application/json
Accept: application/json
```

**Request Body:**
```json
{
  "vendor_id": 1,
  "details": "Updated office supplies for Q1 2025",
  "status": "Approved"
}
```

**Success Response (200 OK):**
```json
{
  "message": "Purchase order updated successfully.",
  "purchase_order": {
    "id": 1,
    "vendor_id": 1,
    "order_number": "PO-20251113-0001",
    "details": "Updated office supplies for Q1 2025",
    "status": "Approved",
    "created_at": "2025-11-13T08:00:00.000000Z",
    "updated_at": "2025-11-13T09:00:00.000000Z",
    "deleted_at": null,
    "vendor": {
      "id": 1,
      "name": "ABC Supplies Ltd",
      "email": "contact@abcsupplies.com",
      "phone": "+1234567890",
      "address": "123 Business Street, City, Country"
    }
  }
}
```

---

### 5. Delete a Purchase Order
**Endpoint:** `DELETE /api/purchase-orders/{id}`

**Success Response (200 OK):**
```json
{
  "message": "Purchase order deleted successfully."
}
```

**Error Response (422 Unprocessable Entity):**
```json
{
  "message": "Only pending purchase orders can be deleted."
}
```

---

## Testing with cURL

### Create a Vendor
```bash
curl -X POST http://your-domain.test/api/vendors \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "name": "ABC Supplies Ltd",
    "email": "contact@abcsupplies.com",
    "phone": "+1234567890",
    "address": "123 Business Street, City, Country"
  }'
```

### List All Vendors
```bash
curl -X GET "http://your-domain.test/api/vendors?search=ABC&per_page=20" \
  -H "Accept: application/json"
```

### Create a Purchase Order
```bash
curl -X POST http://your-domain.test/api/purchase-orders \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "vendor_id": 1,
    "details": "Office supplies for Q1 2025",
    "status": "Pending"
  }'
```

### Update Purchase Order Status
```bash
curl -X PUT http://your-domain.test/api/purchase-orders/1 \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "vendor_id": 1,
    "details": "Office supplies for Q1 2025",
    "status": "Approved"
  }'
```

### Delete a Vendor
```bash
curl -X DELETE http://your-domain.test/api/vendors/1 \
  -H "Accept: application/json"
```

---

## Error Codes

| Status Code | Description |
|-------------|-------------|
| 200 | OK - Request successful |
| 201 | Created - Resource created successfully |
| 422 | Unprocessable Entity - Validation error |
| 404 | Not Found - Resource not found |
| 500 | Internal Server Error - Server error |

---

## Notes

1. **Order Number Format**: Purchase order numbers are automatically generated in the format `PO-YYYYMMDD-XXXX` where XXXX is a sequential number for that day.

2. **Soft Deletes**: Both vendors and purchase orders use soft deletes, meaning deleted records are not permanently removed from the database.

3. **Vendor Deletion Protection**: You cannot delete a vendor if they have existing purchase orders (even soft-deleted ones).

4. **Purchase Order Deletion**: Only purchase orders with "Pending" status can be deleted.

5. **Pagination**: All list endpoints support pagination. Use `page` and `per_page` query parameters to control pagination.

6. **Search**: Search functionality performs a LIKE query across multiple fields for flexible searching.
