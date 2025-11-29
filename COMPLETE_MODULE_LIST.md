i cc# Complete Module List - Sistem Eperolehan

## Analysis Date
Generated: 2025-01-XX

## Summary
Based on analysis of routes (web.php), database migrations, and current sidebar configuration.

---

## **ALL MODULES IN THE SYSTEM (13 Total)**

### **Currently Visible in Sidebar (11 modules):**

1. ✅ **Dashboard**
   - Route: `/dashboard`
   - Icon: LayoutGrid
   - Purpose: Main overview and statistics

2. ✅ **Purchase Requests**
   - Route: `/purchase-requests`
   - Icon: BookPlus
   - Purpose: Create and manage purchase requests
   - Database: `purchase_requests` table
   - Controller: `PurchaseRequestController`

3. ✅ **Approvals**
   - Route: `/approvals`
   - Icon: CheckSquare
   - Purpose: Approve/reject purchase requests (for managers)
   - Uses: `purchase_requests` table with approval workflow

4. ✅ **Locations**
   - Route: `/locations`
   - Icon: MapPinCheck
   - Purpose: Manage organizational locations
   - Database: `locations` table
   - Controller: `LocationController`

5. ✅ **Vendors**
   - Route: `/vendors`
   - Icon: Users
   - Purpose: Vendor/supplier management
   - Database: `vendors` table
   - Controller: `Web\VendorController`

6. ✅ **Purchase Orders**
   - Route: `/purchase-orders`
   - Icon: ShoppingCart
   - Purpose: Purchase order management
   - Database: `purchase_orders` table
   - Controller: `Web\PurchaseOrderController`

7. ✅ **Delivery Orders**
   - Route: `/delivery-orders`
   - Icon: Truck
   - Purpose: Delivery order tracking and confirmation
   - Database: `delivery_orders` table
   - Controller: `DeliveryOrderController`

8. ✅ **File References**
   - Route: `/file-references`
   - Icon: Settings
   - Purpose: File reference management
   - Database: `file_references` table
   - Controller: `FileReferenceController`

9. ✅ **Vots**
   - Route: `/vots`
   - Icon: Settings
   - Purpose: VOT (Vote of Treasury) management
   - Database: `vots` table
   - Controller: `VotController`

10. ✅ **Type Procurements**
    - Route: `/type-procurements`
    - Icon: Settings
    - Purpose: Procurement type definitions
    - Database: `type_procurements` table
    - Controller: `TypeProcurementController`

11. ✅ **Item Units**
    - Route: `/item-units`
    - Icon: Settings
    - Purpose: Unit of measurement for items (kg, pcs, etc.)
    - Database: `item_units` table
    - Controller: `ItemUnitController`

---

### **Missing from Sidebar (2 modules):**

12. ❌ **Statuses**
    - Database: `statuses` table (migration exists: `2025_11_18_014208_create_statuses_table.php`)
    - Model: `Status.php` exists in `app/Models/`
    - Purpose: Status management for workflow states
    - **Status**: No controller or routes found
    - **Recommendation**: Should be added to sidebar under Settings/Master Data section

13. ❌ **Purchase Items**
    - Database: `purchase_items` table (migration exists: `2025_11_19_040619_create_purchase_items_table.php`)
    - Model: `PurchaseItem.php` exists in `app/Models/`
    - Purpose: Individual line items within purchase requests
    - **Status**: No dedicated controller or routes (managed within Purchase Requests)
    - **Recommendation**: Should NOT be in sidebar - it's a child entity of Purchase Requests

---

## **Recommended Sidebar Organization**

### **Group 1: Main Operations**
- Dashboard
- Purchase Requests
- Approvals
- Purchase Orders
- Delivery Orders

### **Group 2: Master Data / Settings**
- Vendors
- Locations
- Item Units
- **Statuses** (to be added)
- File References
- Vots
- Type Procurements

---

## **Action Items**

### **High Priority:**
1. **Add Statuses Module to Sidebar**
   - Create controller: `app/Http/Controllers/StatusController.php` or `app/Http/Controllers/Web/StatusController.php`
   - Add routes in `routes/web.php`
   - Create frontend pages: `resources/js/pages/statuses/Index.vue`, `Create.vue`, `Edit.vue`
   - Add to sidebar with appropriate icon (e.g., `Tag`, `Flag`, or `CircleDot`)

### **Medium Priority:**
2. **Reorganize Sidebar with Groups**
   - Implement collapsible groups for better organization
   - Use more appropriate icons for settings modules
   - Consider adding a "Settings" parent menu item

### **Low Priority:**
3. **Review Icon Consistency**
   - Currently using generic "Settings" icon for multiple modules
   - Consider unique icons for each module

---

## **Database Tables Summary**

| Table Name | Has Migration | Has Model | Has Controller | Has Routes | In Sidebar |
|------------|---------------|-----------|----------------|------------|------------|
| users | ✅ | ✅ | ✅ | ✅ | ❌ (Auth) |
| purchase_requests | ✅ | ✅ | ✅ | ✅ | ✅ |
| vendors | ✅ | ✅ | ✅ | ✅ | ✅ |
| purchase_orders | ✅ | ✅ | ✅ | ✅ | ✅ |
| locations | ✅ | ✅ | ✅ | ✅ | ✅ |
| vots | ✅ | ✅ | ✅ | ✅ | ✅ |
| file_references | ✅ | ✅ | ✅ | ✅ | ✅ |
| type_procurements | ✅ | ✅ | ✅ | ✅ | ✅ |
| statuses | ✅ | ✅ | ❌ | ❌ | ❌ |
| purchase_items | ✅ | ✅ | ❌ | ❌ | ❌ |
| item_units | ✅ | ✅ | ✅ | ✅ | ✅ |
| delivery_orders | ✅ | ✅ | ✅ | ✅ | ✅ |

---

## **Notes**

- **Purchase Items**: This is a detail/child table of Purchase Requests. It should be managed within the Purchase Request forms, not as a separate module.
- **Statuses**: This appears to be a lookup/reference table for workflow states. It should have CRUD operations and be accessible from the sidebar.
- **User Locations**: There's a `UserLocation` model which appears to be a pivot table for user-location relationships. This doesn't need a separate module.

---

## **Conclusion**

The system currently has **11 modules visible in the sidebar** out of **13 total entities** in the database. The main missing module is **Statuses**, which should be implemented and added to the sidebar. Purchase Items is correctly excluded as it's a child entity of Purchase Requests.
