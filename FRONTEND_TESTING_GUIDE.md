# Frontend UI Testing Guide - Vendor & Purchase Order Modules

## Prerequisites
1. Ensure database is set up and migrations are run: `php artisan migrate`
2. Start the development server: `npm run dev` (in a separate terminal)
3. Ensure Laravel Herd is serving the application at `http://sistemperolehan.test`
4. Log in to the application with a valid user account

---

## Test Suite 1: Vendor Management Module

### 1.1 Vendor List Page (`/vendors`)
**URL**: http://sistemperolehan.test/vendors

**Test Steps**:
- [ ] Navigate to `/vendors` from the sidebar menu
- [ ] Verify "Vendors" menu item is highlighted in sidebar
- [ ] Verify page title shows "Vendors"
- [ ] Verify breadcrumb shows "Vendors"
- [ ] Verify "New Vendor" button is visible in top-right
- [ ] Verify table headers: Name, Email, Phone, Address, Purchase Orders, Created, Actions
- [ ] If no vendors exist, verify "No vendors found" message displays

**Expected Results**:
✅ Page loads without errors
✅ All UI elements are properly styled and aligned
✅ Navigation works correctly

---

### 1.2 Create Vendor (`/vendors/create`)
**URL**: http://sistemperolehan.test/vendors/create

**Test Steps**:
- [ ] Click "New Vendor" button from vendors list
- [ ] Verify breadcrumb shows "Vendors > Create"
- [ ] Verify form has fields: Name (required), Email, Phone, Address
- [ ] Verify required field indicator (red asterisk) on Name field

**Test Case 1: Validation Errors**
- [ ] Leave Name field empty
- [ ] Click "Create Vendor" button
- [ ] Verify error message appears under Name field
- [ ] Verify form does not submit

**Test Case 2: Successful Creation**
- [ ] Fill in Name: "Test Vendor 1"
- [ ] Fill in Email: "vendor1@example.com"
- [ ] Fill in Phone: "123-456-7890"
- [ ] Fill in Address: "123 Test Street"
- [ ] Click "Create Vendor" button
- [ ] Verify redirect to vendors list
- [ ] Verify success message appears
- [ ] Verify new vendor appears in the list

**Test Case 3: Email Validation**
- [ ] Fill in Name: "Test Vendor 2"
- [ ] Fill in Email: "invalid-email"
- [ ] Click "Create Vendor" button
- [ ] Verify email validation error appears

**Expected Results**:
✅ Validation works correctly
✅ Success message displays after creation
✅ New vendor appears in list

---

### 1.3 Search Functionality
**URL**: http://sistemperolehan.test/vendors

**Test Steps**:
- [ ] Create at least 3 vendors with different names, emails, phones
- [ ] In search box, type part of a vendor's name
- [ ] Press Enter or click "Apply"
- [ ] Verify only matching vendors appear
- [ ] Clear search and verify all vendors appear again
- [ ] Search by email address
- [ ] Verify correct results
- [ ] Search by phone number
- [ ] Verify correct results

**Expected Results**:
✅ Search works across all vendor fields
✅ Results update correctly
✅ Reset button clears search

---

### 1.4 Sorting Functionality
**URL**: http://sistemperolehan.test/vendors

**Test Steps**:
- [ ] Click "Name" column header
- [ ] Verify vendors sort alphabetically (A-Z)
- [ ] Click "Name" again
- [ ] Verify vendors sort reverse alphabetically (Z-A)
- [ ] Click "Email" column header
- [ ] Verify sorting works
- [ ] Click "Phone" column header
- [ ] Verify sorting works
- [ ] Click "Created" column header
- [ ] Verify sorting by date works

**Expected Results**:
✅ All columns sort correctly
✅ Sort direction toggles on repeated clicks
✅ Sort indicator shows current sort column and direction

---

### 1.5 Pagination
**URL**: http://sistemperolehan.test/vendors

**Test Steps**:
- [ ] Create more than 10 vendors (to trigger pagination)
- [ ] Verify pagination controls appear at bottom
- [ ] Verify "Per page" dropdown shows (10, 25, 50)
- [ ] Click "Next" page button
- [ ] Verify page 2 loads with different vendors
- [ ] Click "Previous" button
- [ ] Verify page 1 loads
- [ ] Change "Per page" to 25
- [ ] Verify more vendors display per page

**Expected Results**:
✅ Pagination works correctly
✅ Per page selector updates results
✅ Page numbers are accurate

---

### 1.6 Edit Vendor (`/vendors/{id}/edit`)
**URL**: http://sistemperolehan.test/vendors/1/edit

**Test Steps**:
- [ ] From vendors list, click "Edit" on a vendor
- [ ] Verify breadcrumb shows "Vendors > Edit"
- [ ] Verify form is pre-populated with vendor data
- [ ] Verify vendor ID is shown in page header

**Test Case 1: Update Vendor**
- [ ] Change Name to "Updated Vendor Name"
- [ ] Change Email to "updated@example.com"
- [ ] Click "Update Vendor" button
- [ ] Verify redirect to vendors list
- [ ] Verify success message appears
- [ ] Verify vendor shows updated information

**Test Case 2: Validation on Update**
- [ ] Clear Name field
- [ ] Click "Update Vendor" button
- [ ] Verify validation error appears
- [ ] Verify form does not submit

**Expected Results**:
✅ Edit form loads with correct data
✅ Updates save successfully
✅ Validation works on update

---

### 1.7 Delete Vendor (Without Purchase Orders)
**URL**: http://sistemperolehan.test/vendors

**Test Steps**:
- [ ] Create a new vendor (ensure it has NO purchase orders)
- [ ] Click "Delete" button for that vendor
- [ ] Verify confirmation dialog appears
- [ ] Click "OK" to confirm
- [ ] Verify vendor is removed from list
- [ ] Verify success message appears

**Expected Results**:
✅ Delete confirmation appears
✅ Vendor is deleted successfully
✅ Success message displays

---

### 1.8 Delete Vendor (With Purchase Orders) - Protection
**URL**: http://sistemperolehan.test/vendors

**Test Steps**:
- [ ] Create a vendor
- [ ] Create a purchase order for that vendor (see Purchase Order tests)
- [ ] Return to vendors list
- [ ] Verify "Purchase Orders" column shows count > 0
- [ ] Verify "Delete" button is grayed out/disabled
- [ ] Hover over disabled delete button
- [ ] Verify tooltip shows "Cannot delete vendor with existing purchase orders"
- [ ] Try to click delete button
- [ ] Verify nothing happens (button is disabled)

**Expected Results**:
✅ Delete button is visually disabled
✅ Tooltip explains why deletion is blocked
✅ Vendor cannot be deleted

---

## Test Suite 2: Purchase Order Management Module

### 2.1 Purchase Order List Page (`/purchase-orders`)
**URL**: http://sistemperolehan.test/purchase-orders

**Test Steps**:
- [ ] Navigate to `/purchase-orders` from sidebar menu
- [ ] Verify "Purchase Orders" menu item is highlighted
- [ ] Verify page title shows "Purchase Orders"
- [ ] Verify breadcrumb shows "Purchase Orders"
- [ ] Verify "New Purchase Order" button is visible
- [ ] Verify table headers: Order Number, Vendor, Details, Status, Created, Actions
- [ ] If no orders exist, verify "No purchase orders found" message

**Expected Results**:
✅ Page loads without errors
✅ All UI elements are properly styled
✅ Navigation works correctly

---

### 2.2 Create Purchase Order (`/purchase-orders/create`)
**URL**: http://sistemperolehan.test/purchase-orders/create

**Prerequisites**: At least one vendor must exist

**Test Steps**:
- [ ] Click "New Purchase Order" button
- [ ] Verify breadcrumb shows "Purchase Orders > Create"
- [ ] Verify form has fields: Vendor (dropdown), Details (textarea), Status (dropdown)
- [ ] Verify required field indicators on Vendor and Status
- [ ] Verify info message about auto-generated order number

**Test Case 1: Validation Errors**
- [ ] Leave Vendor field empty
- [ ] Click "Create Purchase Order" button
- [ ] Verify error message appears under Vendor field

**Test Case 2: Successful Creation**
- [ ] Select a vendor from dropdown
- [ ] Enter Details: "Test purchase order for office supplies"
- [ ] Select Status: "Pending"
- [ ] Click "Create Purchase Order" button
- [ ] Verify redirect to purchase orders list
- [ ] Verify success message appears
- [ ] Verify new purchase order appears with auto-generated order number (format: PO-YYYYMMDD-XXXX)

**Test Case 3: Multiple Status Options**
- [ ] Verify Status dropdown has options: Pending, Approved, Completed
- [ ] Create orders with different statuses
- [ ] Verify all statuses work correctly

**Expected Results**:
✅ Validation works correctly
✅ Order number is auto-generated
✅ Success message displays
✅ New order appears in list

---

### 2.3 Search Functionality
**URL**: http://sistemperolehan.test/purchase-orders

**Test Steps**:
- [ ] Create at least 3 purchase orders
- [ ] In search box, type part of an order number
- [ ] Press Enter or click "Apply"
- [ ] Verify only matching orders appear
- [ ] Search by order details text
- [ ] Verify correct results
- [ ] Search by order ID number
- [ ] Verify correct results

**Expected Results**:
✅ Search works across order number, details, and ID
✅ Results update correctly

---

### 2.4 Status Filter
**URL**: http://sistemperolehan.test/purchase-orders

**Test Steps**:
- [ ] Create orders with different statuses (Pending, Approved, Completed)
- [ ] Select "Pending" from Status dropdown
- [ ] Click "Apply"
- [ ] Verify only Pending orders appear
- [ ] Select "Approved" from Status dropdown
- [ ] Click "Apply"
- [ ] Verify only Approved orders appear
- [ ] Select "All" from Status dropdown
- [ ] Verify all orders appear

**Expected Results**:
✅ Status filter works correctly
✅ Only matching status orders display

---

### 2.5 Vendor Filter
**URL**: http://sistemperolehan.test/purchase-orders

**Test Steps**:
- [ ] Create orders for different vendors
- [ ] Select a specific vendor from Vendor dropdown
- [ ] Click "Apply"
- [ ] Verify only orders for that vendor appear
- [ ] Select "All" from Vendor dropdown
- [ ] Verify all orders appear

**Expected Results**:
✅ Vendor filter works correctly
✅ Only orders for selected vendor display

---

### 2.6 Date Range Filter
**URL**: http://sistemperolehan.test/purchase-orders

**Test Steps**:
- [ ] Create orders on different dates (if possible, or use existing orders)
- [ ] Set "From Date" to a specific date
- [ ] Click "Apply"
- [ ] Verify only orders from that date onwards appear
- [ ] Set "To Date" to a specific date
- [ ] Click "Apply"
- [ ] Verify only orders within date range appear
- [ ] Clear both date fields
- [ ] Click "Apply"
- [ ] Verify all orders appear

**Expected Results**:
✅ Date range filter works correctly
✅ Results match the specified date range

---

### 2.7 Combined Filters
**URL**: http://sistemperolehan.test/purchase-orders

**Test Steps**:
- [ ] Apply multiple filters simultaneously:
  - Search text
  - Status filter
  - Vendor filter
  - Date range
- [ ] Click "Apply"
- [ ] Verify results match ALL filter criteria
- [ ] Click "Reset" button
- [ ] Verify all filters are cleared
- [ ] Verify all orders appear

**Expected Results**:
✅ Multiple filters work together correctly
✅ Reset button clears all filters

---

### 2.8 Sorting Functionality
**URL**: http://sistemperolehan.test/purchase-orders

**Test Steps**:
- [ ] Click "Order Number" column header
- [ ] Verify orders sort by order number
- [ ] Click "Order Number" again
- [ ] Verify sort direction reverses
- [ ] Click "Status" column header
- [ ] Verify sorting works
- [ ] Click "Created" column header
- [ ] Verify sorting by date works

**Expected Results**:
✅ All sortable columns work correctly
✅ Sort direction toggles properly

---

### 2.9 Status Badges
**URL**: http://sistemperolehan.test/purchase-orders

**Test Steps**:
- [ ] Create orders with all three statuses
- [ ] Verify "Pending" status shows yellow badge
- [ ] Verify "Approved" status shows green badge
- [ ] Verify "Completed" status shows blue badge
- [ ] Verify badge text is readable

**Expected Results**:
✅ Status badges display with correct colors
✅ All statuses are visually distinct

---

### 2.10 Pagination
**URL**: http://sistemperolehan.test/purchase-orders

**Test Steps**:
- [ ] Create more than 10 purchase orders
- [ ] Verify pagination controls appear
- [ ] Test page navigation (Next, Previous, specific page numbers)
- [ ] Change "Per page" to 25
- [ ] Verify more orders display per page
- [ ] Verify pagination updates correctly

**Expected Results**:
✅ Pagination works correctly
✅ Per page selector updates results

---

### 2.11 Edit Purchase Order (`/purchase-orders/{id}/edit`)
**URL**: http://sistemperolehan.test/purchase-orders/1/edit

**Test Steps**:
- [ ] From purchase orders list, click "Edit" on an order
- [ ] Verify breadcrumb shows "Purchase Orders > Edit"
- [ ] Verify form is pre-populated with order data
- [ ] Verify order number is displayed (read-only)

**Test Case 1: Update Purchase Order**
- [ ] Change Vendor to a different vendor
- [ ] Update Details text
- [ ] Change Status to "Approved"
- [ ] Click "Update Purchase Order" button
- [ ] Verify redirect to purchase orders list
- [ ] Verify success message appears
- [ ] Verify order shows updated information

**Test Case 2: Validation on Update**
- [ ] Clear Vendor field (set to empty)
- [ ] Click "Update Purchase Order" button
- [ ] Verify validation error appears

**Expected Results**:
✅ Edit form loads with correct data
✅ Updates save successfully
✅ Order number remains unchanged
✅ Validation works on update

---

### 2.12 Delete Purchase Order (Pending Status)
**URL**: http://sistemperolehan.test/purchase-orders

**Test Steps**:
- [ ] Create a purchase order with "Pending" status
- [ ] Click "Delete" button for that order
- [ ] Verify confirmation dialog appears
- [ ] Click "OK" to confirm
- [ ] Verify order is removed from list
- [ ] Verify success message appears

**Expected Results**:
✅ Delete confirmation appears
✅ Pending order is deleted successfully
✅ Success message displays

---

### 2.13 Delete Purchase Order (Non-Pending Status) - Protection
**URL**: http://sistemperolehan.test/purchase-orders

**Test Steps**:
- [ ] Create a purchase order with "Approved" status
- [ ] Verify "Delete" button is grayed out/disabled
- [ ] Hover over disabled delete button
- [ ] Verify tooltip shows "Only pending orders can be deleted"
- [ ] Try to click delete button
- [ ] Verify nothing happens
- [ ] Repeat with "Completed" status order

**Expected Results**:
✅ Delete button is visually disabled for non-pending orders
✅ Tooltip explains the restriction
✅ Orders cannot be deleted

---

## Test Suite 3: Navigation & Integration

### 3.1 Sidebar Navigation
**URL**: http://sistemperolehan.test/dashboard

**Test Steps**:
- [ ] Verify sidebar shows all menu items:
  - Dashboard (LayoutGrid icon)
  - Purchase Requests (Folder icon)
  - Vendors (Users icon)
  - Purchase Orders (ShoppingCart icon)
- [ ] Click "Vendors" menu item
- [ ] Verify navigation to `/vendors`
- [ ] Verify "Vendors" is highlighted in sidebar
- [ ] Click "Purchase Orders" menu item
- [ ] Verify navigation to `/purchase-orders`
- [ ] Verify "Purchase Orders" is highlighted in sidebar

**Expected Results**:
✅ All menu items are visible with correct icons
✅ Navigation works correctly
✅ Active menu item is highlighted

---

### 3.2 Breadcrumb Navigation
**Test Steps**:
- [ ] Navigate to `/vendors`
- [ ] Verify breadcrumb shows "Vendors"
- [ ] Navigate to `/vendors/create`
- [ ] Verify breadcrumb shows "Vendors > Create"
- [ ] Click "Vendors" in breadcrumb
- [ ] Verify navigation back to vendors list
- [ ] Navigate to `/purchase-orders/1/edit`
- [ ] Verify breadcrumb shows "Purchase Orders > Edit"
- [ ] Click "Purchase Orders" in breadcrumb
- [ ] Verify navigation back to purchase orders list

**Expected Results**:
✅ Breadcrumbs display correctly on all pages
✅ Breadcrumb links work correctly

---

### 3.3 Responsive Design
**Test Steps**:
- [ ] Resize browser window to mobile size (< 768px)
- [ ] Verify tables are horizontally scrollable
- [ ] Verify forms stack vertically
- [ ] Verify buttons are accessible
- [ ] Resize to tablet size (768px - 1024px)
- [ ] Verify layout adjusts appropriately
- [ ] Resize to desktop size (> 1024px)
- [ ] Verify full layout displays correctly

**Expected Results**:
✅ UI is responsive on all screen sizes
✅ No content is cut off or inaccessible
✅ Tables scroll horizontally on small screens

---

## Test Suite 4: End-to-End Workflows

### 4.1 Complete Vendor & Purchase Order Workflow
**Test Steps**:
1. [ ] Create a new vendor "ABC Company"
2. [ ] Verify vendor appears in vendors list
3. [ ] Navigate to Purchase Orders
4. [ ] Create a new purchase order for "ABC Company"
5. [ ] Verify order is created with auto-generated number
6. [ ] Return to Vendors list
7. [ ] Verify "ABC Company" shows 1 purchase order in count
8. [ ] Try to delete "ABC Company"
9. [ ] Verify deletion is blocked
10. [ ] Navigate to Purchase Orders
11. [ ] Delete the purchase order (if Pending)
12. [ ] Return to Vendors list
13. [ ] Verify "ABC Company" shows 0 purchase orders
14. [ ] Delete "ABC Company"
15. [ ] Verify deletion succeeds

**Expected Results**:
✅ Complete workflow works end-to-end
✅ Vendor deletion protection works correctly
✅ Purchase order count updates correctly

---

### 4.2 Multiple Purchase Orders Workflow
**Test Steps**:
1. [ ] Create 3 vendors
2. [ ] Create 5 purchase orders with different:
   - Vendors
   - Statuses (mix of Pending, Approved, Completed)
   - Dates
3. [ ] Test filtering by each status
4. [ ] Test filtering by each vendor
5. [ ] Test sorting by different columns
6. [ ] Test pagination if > 10 orders
7. [ ] Edit one order and change its status
8. [ ] Verify filters still work correctly
9. [ ] Try to delete Pending order (should work)
10. [ ] Try to delete Approved order (should be blocked)

**Expected Results**:
✅ All filters work with multiple orders
✅ Sorting works correctly
✅ Status-based deletion rules are enforced

---

### 4.3 Flash Messages
**Test Steps**:
- [ ] Create a vendor
- [ ] Verify success message appears and is visible
- [ ] Update a vendor
- [ ] Verify success message appears
- [ ] Delete a vendor (without purchase orders)
- [ ] Verify success message appears
- [ ] Try to delete a vendor with purchase orders
- [ ] Verify error message appears
- [ ] Create a purchase order
- [ ] Verify success message appears
- [ ] Try to delete a non-pending purchase order
- [ ] Verify error message appears

**Expected Results**:
✅ Success messages appear for successful operations
✅ Error messages appear for blocked operations
✅ Messages are clearly visible and readable

---

## Testing Checklist Summary

### Vendor Module
- [ ] List page loads correctly
- [ ] Create form works with validation
- [ ] Search functionality works
- [ ] Sorting works on all columns
- [ ] Pagination works
- [ ] Edit form works with validation
- [ ] Delete works (without purchase orders)
- [ ] Delete is blocked (with purchase orders)

### Purchase Order Module
- [ ] List page loads correctly
- [ ] Create form works with validation
- [ ] Order numbers auto-generate correctly
- [ ] Search functionality works
- [ ] Status filter works
- [ ] Vendor filter works
- [ ] Date range filter works
- [ ] Combined filters work
- [ ] Sorting works on all columns
- [ ] Pagination works
- [ ] Status badges display correctly
- [ ] Edit form works with validation
- [ ] Delete works (Pending orders)
- [ ] Delete is blocked (non-Pending orders)

### Navigation & Integration
- [ ] Sidebar menu items appear correctly
- [ ] Navigation between modules works
- [ ] Breadcrumbs display correctly
- [ ] Responsive design works on all screen sizes

### End-to-End Workflows
- [ ] Complete vendor-to-purchase-order workflow
- [ ] Multiple purchase orders workflow
- [ ] Flash messages display correctly

---

## Bug Reporting Template

If you find any issues during testing, please document them using this template:

**Bug Title**: [Brief description]

**Steps to Reproduce**:
1. [Step 1]
2. [Step 2]
3. [Step 3]

**Expected Result**: [What should happen]

**Actual Result**: [What actually happened]

**Severity**: [Critical / High / Medium / Low]

**Screenshots**: [If applicable]

---

## Testing Complete!

Once all tests pass, the Vendor and Purchase Order modules are ready for production use.

**Estimated Testing Time**: 2-3 hours for thorough testing
**Recommended**: Test in order (Vendors first, then Purchase Orders, then Integration)
