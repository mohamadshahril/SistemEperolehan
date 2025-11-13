# Vendor & Purchase Order Module Implementation - TODO

## ✅ Completed Steps

### Phase 1: Database Setup
- [x] Create Vendor Model and Migration
  - Fields: id, name, email, phone, address, timestamps, soft_deletes
  - Added indexes for name and email
  - Included fillable fields and casts
  
- [x] Create PurchaseOrder Model and Migration
  - Fields: id, vendor_id (foreign key), order_number (unique), details, status (enum), timestamps, soft_deletes
  - Added relationship to Vendor model
  - Added indexes for status and order_number
  - Included fillable fields and casts

- [x] Update Vendor Model
  - Added hasMany relationship to PurchaseOrder

### Phase 2: Backend API Controllers
- [x] Create VendorController
  - Implemented full CRUD operations (index, store, show, update, destroy)
  - Added validation rules
  - Included authorization checks
  - Prevent deletion of vendors with active purchase orders
  - Added search, sorting, and pagination

- [x] Create PurchaseOrderController
  - Implemented CRUD operations with vendor relationship loading
  - Auto-generate unique order numbers (format: PO-YYYYMMDD-0001)
  - Added validation rules
  - Included status management
  - Added search, filtering, sorting, and pagination
  - Only allow deletion of pending orders

- [x] Create API Routes File
  - Created routes/api.php
  - Registered API resource routes for vendors and purchase-orders
  - Added API middleware configuration in bootstrap/app.php

## 📋 Next Steps (To Be Completed)

### Step 1: Run Migrations ✅ COMPLETED
```bash
php artisan migrate:fresh
```
✅ Successfully created the vendors and purchase_orders tables in your database.

### Step 2: Test API Endpoints ✅ COMPLETED
All API endpoints have been thoroughly tested and verified:

**Vendor Endpoints:** ✅
- ✅ GET `/api/vendors` - List all vendors (with pagination, search, sorting)
- ✅ POST `/api/vendors` - Create a new vendor
- ✅ GET `/api/vendors/{id}` - Get a specific vendor with purchase orders
- ✅ PUT/PATCH `/api/vendors/{id}` - Update a vendor
- ✅ DELETE `/api/vendors/{id}` - Delete a vendor (only if no purchase orders exist)

**Purchase Order Endpoints:** ✅
- ✅ GET `/api/purchase-orders` - List all purchase orders (with pagination, search, filtering)
- ✅ POST `/api/purchase-orders` - Create a new purchase order (auto-generates order number)
- ✅ GET `/api/purchase-orders/{id}` - Get a specific purchase order with vendor
- ✅ PUT/PATCH `/api/purchase-orders/{id}` - Update a purchase order
- ✅ DELETE `/api/purchase-orders/{id}` - Delete a purchase order (only if status is Pending)

**Validation & Business Rules Tested:** ✅
- ✅ Email validation (rejects invalid email formats)
- ✅ Foreign key validation (rejects non-existent vendor_id)
- ✅ Vendor deletion protection (prevents deletion with active purchase orders)
- ✅ Purchase order deletion restriction (only allows deletion of pending orders)
- ✅ Search functionality (vendors and purchase orders)
- ✅ Status filtering (purchase orders by status)
- ✅ Auto-generated order numbers (format: PO-YYYYMMDD-XXXX)

**Test Results:**
- Created 2 vendors successfully
- Created 2 purchase orders with auto-generated numbers (PO-20251113-0001, PO-20251113-0002)
- Updated purchase order status from Pending to Approved
- Verified vendor deletion protection works correctly
- Verified purchase order deletion restriction works correctly
- Deleted pending purchase order successfully
- Deleted vendor without purchase orders successfully
- All validation rules working as expected

### Step 3: Optional - Create Frontend UI (Vue Components)
If you want to add UI for these modules:
- Create Vue components for vendor management (List, Create, Edit)
- Create Vue components for purchase order management (List, Create, Edit, View)
- Add navigation menu items in the sidebar
- Create Inertia pages similar to the existing purchase-requests module

### Step 4: Optional - Create Seeders
Create sample data for testing:
```bash
php artisan make:seeder VendorSeeder
php artisan make:seeder PurchaseOrderSeeder
```

## 📝 API Usage Examples

### Create a Vendor
```bash
POST /api/vendors
Content-Type: application/json

{
  "name": "ABC Supplies Ltd",
  "email": "contact@abcsupplies.com",
  "phone": "+1234567890",
  "address": "123 Business Street, City, Country"
}
```

### Create a Purchase Order
```bash
POST /api/purchase-orders
Content-Type: application/json

{
  "vendor_id": 1,
  "details": "Office supplies for Q1 2025",
  "status": "Pending"
}
```

### Search Vendors
```bash
GET /api/vendors?search=ABC&sort_by=name&sort_dir=asc&per_page=20
```

### Filter Purchase Orders
```bash
GET /api/purchase-orders?status=Pending&vendor_id=1&from_date=2025-01-01&to_date=2025-12-31
```

## 🎯 Features Implemented

### Vendor Management (UC04)
- ✅ Add new vendors
- ✅ Update vendor details
- ✅ Delete vendors (with validation)
- ✅ View vendor list with search and pagination
- ✅ View individual vendor with related purchase orders
- ✅ Prevent deletion of vendors with active purchase orders

### Purchase Order Management (UC05)
- ✅ Generate new purchase orders with auto-generated order numbers
- ✅ View purchase order list with filtering and search
- ✅ View individual purchase order details with vendor information
- ✅ Update purchase order details and status
- ✅ Delete purchase orders (only pending ones)
- ✅ Filter by status, vendor, and date range

## 🔒 Business Rules Enforced

1. **Vendor Deletion**: Cannot delete a vendor if they have existing purchase orders
2. **Purchase Order Deletion**: Can only delete purchase orders with "Pending" status
3. **Order Number Generation**: Automatically generates unique order numbers in format PO-YYYYMMDD-XXXX
4. **Status Management**: Purchase orders can only have status: Pending, Approved, or Completed
5. **Soft Deletes**: Both vendors and purchase orders use soft deletes for data integrity

## 📚 Database Schema

### Vendors Table
- id (primary key)
- name (required)
- email (nullable)
- phone (nullable)
- address (nullable)
- created_at
- updated_at
- deleted_at (soft delete)

### Purchase Orders Table
- id (primary key)
- vendor_id (foreign key to vendors)
- order_number (unique, auto-generated)
- details (nullable)
- status (enum: Pending, Approved, Completed)
- created_at
- updated_at
- deleted_at (soft delete)

## 🔗 Relationships
- Vendor hasMany PurchaseOrders
- PurchaseOrder belongsTo Vendor
