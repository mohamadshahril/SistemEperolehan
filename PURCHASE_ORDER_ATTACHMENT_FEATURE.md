# Purchase Order Attachment Feature

## Overview

Added file upload functionality to Purchase Orders, matching the pattern used in Purchase Requests. This allows users to attach supporting documents like quotations, invoices, contracts, or other relevant files to purchase orders.

---

## Changes Implemented

### 1. Database Migration ✅

**File**: `database/migrations/2025_11_13_083340_add_attachment_to_purchase_orders_table.php`

Added `attachment_path` column to `purchase_orders` table:
```php
$table->string('attachment_path')->nullable()->after('details');
```

**Migration Status**: ✅ Executed successfully

---

### 2. Model Update ✅

**File**: `app/Models/PurchaseOrder.php`

Added `attachment_path` to fillable fields:
```php
protected $fillable = [
    'vendor_id',
    'order_number',
    'details',
    'status',
    'attachment_path', // NEW
];
```

---

### 3. Backend Controllers ✅

#### API Controller
**File**: `app/Http/Controllers/PurchaseOrderController.php`

**Changes:**
- Added `Storage` facade import
- Added file validation in `store()` and `update()` methods
- Implemented file upload handling
- Implemented file deletion on update/destroy

**Validation Rule:**
```php
'attachment' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx', 'max:5120']
```

**File Storage:**
- Directory: `storage/app/public/purchase_orders/`
- Public URL: `/storage/purchase_orders/{filename}`

#### Web Controller
**File**: `app/Http/Controllers/Web/PurchaseOrderController.php`

Same changes as API controller, ensuring consistency across both interfaces.

---

### 4. Frontend Pages ✅

#### Create Page
**File**: `resources/js/pages/purchase-orders/Create.vue`

**Changes:**
- Added `attachment` field to form state (type: `File | null`)
- Added file input field with:
  - Accept attribute for allowed file types
  - Change handler to update form state
  - Error display
  - Helper text showing accepted formats and size limit

**UI:**
```vue
<input
  type="file"
  accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx"
  @change="(e) => form.attachment = (e.target as HTMLInputElement).files?.[0] || null"
  class="mt-1 block w-full rounded-md border p-2"
/>
<p class="mt-1 text-xs text-gray-500">
  Accepted: pdf, jpg, jpeg, png, doc, docx, xls, xlsx. Max 5 MB.
</p>
```

#### Edit Page
**File**: `resources/js/pages/purchase-orders/Edit.vue`

**Changes:**
- Added `attachment_path` to TypeScript interface
- Added `attachment` field to form state
- Shows current attachment with download link (if exists)
- Allows uploading new file to replace existing one
- Added file input field with same validation as Create page

**UI Features:**
- Displays "Current file: View attachment" link if file exists
- Shows appropriate helper text based on whether file exists
- Clicking attachment link opens file in new tab

#### Index Page
**File**: `resources/js/pages/purchase-orders/Index.vue`

**Changes:**
- Added `attachment_path` to TypeScript interface
- Added attachment indicator (📎 icon) next to order number
- Icon is clickable and opens attachment in new tab
- Only shows icon if attachment exists

**UI:**
```vue
<a 
  v-if="row.attachment_path" 
  :href="`/storage/${row.attachment_path}`" 
  target="_blank"
  title="View attachment"
>
  📎
</a>
```

---

## File Upload Specifications

### Accepted File Types
- **Documents**: PDF, DOC, DOCX
- **Spreadsheets**: XLS, XLSX
- **Images**: JPG, JPEG, PNG

### File Size Limit
- **Maximum**: 5 MB (5120 KB)

### Storage Location
- **Disk**: `public`
- **Directory**: `purchase_orders/`
- **Full Path**: `storage/app/public/purchase_orders/`
- **Public URL**: `/storage/purchase_orders/{filename}`

### File Naming
- Laravel automatically generates unique filenames to prevent conflicts
- Original filename is preserved in the path

---

## Features

### Create Purchase Order
1. User selects a file using file input
2. File is validated on submit (type and size)
3. If valid, file is uploaded to storage
4. File path is saved in database
5. Success message displayed

### Edit Purchase Order
1. If attachment exists, shows "View attachment" link
2. User can upload new file to replace existing one
3. Old file is automatically deleted when new file is uploaded
4. File path is updated in database

### Delete Purchase Order
1. When purchase order is deleted (only Pending status)
2. Associated attachment file is automatically deleted from storage
3. Prevents orphaned files

### View Attachments
1. **From Index Page**: Click 📎 icon next to order number
2. **From Edit Page**: Click "View attachment" link
3. File opens in new browser tab
4. Browser handles file display/download based on type

---

## Security Considerations

### File Validation
- ✅ File type validation (MIME types)
- ✅ File size validation (max 5 MB)
- ✅ Server-side validation in controllers
- ✅ Client-side validation in HTML5 accept attribute

### Storage Security
- ✅ Files stored in `storage/app/public/` (not web root)
- ✅ Accessed via symbolic link (`/storage`)
- ✅ Laravel handles file serving securely

### File Deletion
- ✅ Old files deleted when replaced
- ✅ Files deleted when purchase order is deleted
- ✅ Prevents storage bloat

---

## Testing Guide

### Manual Testing Steps

#### 1. Create Purchase Order with Attachment
```
1. Navigate to /purchase-orders/create
2. Fill in required fields (Vendor, Status)
3. Click "Choose file" and select a PDF
4. Click "Create Purchase Order"
5. Verify success message
6. Check Index page - should see 📎 icon
7. Click 📎 icon - file should open in new tab
```

#### 2. Edit Purchase Order - Replace Attachment
```
1. Navigate to /purchase-orders/{id}/edit
2. Verify "Current file: View attachment" is shown
3. Click "View attachment" - file should open
4. Upload a new file
5. Click "Update Purchase Order"
6. Verify new file is accessible
7. Verify old file is deleted from storage
```

#### 3. Create Purchase Order without Attachment
```
1. Navigate to /purchase-orders/create
2. Fill in required fields
3. Leave attachment field empty
4. Click "Create Purchase Order"
5. Verify success (attachment is optional)
6. Check Index page - no 📎 icon should appear
```

#### 4. File Validation Testing
```
Test invalid file type:
1. Try uploading .exe file
2. Should show validation error

Test file too large:
1. Try uploading 10 MB file
2. Should show validation error

Test valid file:
1. Upload 2 MB PDF
2. Should succeed
```

#### 5. Delete Purchase Order with Attachment
```
1. Create purchase order with attachment
2. Note the filename in storage
3. Delete the purchase order
4. Verify file is deleted from storage/app/public/purchase_orders/
```

### API Testing

```bash
# Create with attachment
curl -X POST http://sistemeperolehan.test/api/purchase-orders \
  -F "vendor_id=1" \
  -F "status=Pending" \
  -F "details=Test order" \
  -F "attachment=@/path/to/file.pdf"

# Update with attachment
curl -X POST http://sistemeperolehan.test/api/purchase-orders/1 \
  -F "_method=PUT" \
  -F "vendor_id=1" \
  -F "status=Approved" \
  -F "attachment=@/path/to/newfile.pdf"
```

---

## Comparison with Purchase Request

Both modules now have identical file upload functionality:

| Feature | Purchase Request | Purchase Order |
|---------|-----------------|----------------|
| File Upload | ✅ | ✅ |
| Accepted Types | pdf, jpg, jpeg, png, doc, docx, xls, xlsx | pdf, jpg, jpeg, png, doc, docx, xls, xlsx |
| Max Size | 5 MB | 5 MB |
| Storage Directory | `purchase_requests/` | `purchase_orders/` |
| View from Index | ✅ | ✅ (📎 icon) |
| Replace on Edit | ✅ | ✅ |
| Delete on Destroy | ✅ | ✅ |
| Optional Field | ✅ | ✅ |

---

## Benefits

1. **Document Management**: Store quotations, invoices, contracts with orders
2. **Audit Trail**: Keep supporting documents for compliance
3. **Accessibility**: Easy access to attachments from list and edit pages
4. **Storage Efficiency**: Automatic cleanup of old/deleted files
5. **User Experience**: Consistent with Purchase Request module
6. **Flexibility**: Optional field - not required for all orders

---

## Future Enhancements (Optional)

1. **Multiple Attachments**: Allow multiple files per order
2. **File Preview**: Show thumbnail/preview in list
3. **Download Counter**: Track how many times file is downloaded
4. **File Versioning**: Keep history of replaced files
5. **Drag & Drop**: Improve UX with drag-and-drop upload
6. **File Categories**: Tag files (quotation, invoice, contract, etc.)

---

**Date Implemented**: November 13, 2025  
**Implemented By**: BLACKBOXAI  
**Status**: ✅ Complete and Tested
