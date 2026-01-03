# GitHub Commit Message

Use this for your GitHub Desktop commit:

## Summary (required):
```
Add comprehensive Tender Module with bid management and awarding system
```

## Description:
```
This commit implements a complete Tender Management System with the following features:

### Core Features
- Tender CRUD operations (Create, Read, Update, Delete)
- Tender bid submission and management
- Manual tender awarding workflow
- Tender status lifecycle (Draft → Published → Closed → Awarded)
- Automatic bid rejection when tender is awarded

### Backend Changes
- Added Tender and TenderBid models with relationships
- Implemented TenderController with award() method for manual bid selection
- Implemented TenderBidController for bid management
- Created database migrations for tenders and tender_bids tables
- Added foreign key relationship for awarded_bid_id
- Created TenderSeeder for sample data

### Frontend Changes
- Built Vue.js pages for tender management (Index, Create, Edit, Show)
- Built Vue.js page for tender bids (Index)
- Added tender navigation to AppSidebar
- Implemented tender filtering and search functionality
- Added bid submission and review interface

### Routes
- Added tender resource routes
- Added custom POST route for tender awarding: /tenders/{tender}/award
- Protected all routes with auth middleware

### Documentation
- TENDER_MODULE_COMPLETE.md - Implementation summary
- TENDER_MODULE_FIX_GUIDE.md - Troubleshooting guide
- TENDER_AWARDING_LOGIC_EXPLAINED.md - Detailed explanation of awarding logic
- NODEJS_INSTALLATION_FIX.md - Node.js setup guide for development

### Key Business Logic
- Tenders can only be awarded manually by authorized users
- When a tender is awarded, the winning bid is marked as "Accepted"
- All other bids are automatically marked as "Rejected"
- Awarded tenders cannot be edited or deleted (audit trail protection)
- Tender number auto-generation: TND-YYYYMM-####

### Database Schema
- tenders table with status tracking and awarded_bid_id
- tender_bids table with vendor relationships
- Soft deletes enabled for audit trail
- Foreign key constraints for data integrity

This implementation provides a complete procurement tender management system with manual evaluation and awarding capabilities.
```

---

## How to Use in GitHub Desktop:

1. Copy the text from "Summary (required)" section (without the backticks)
2. Paste it in the "Summary (required)" field in GitHub Desktop
3. Copy the text from "Description" section (without the backticks)
4. Paste it in the "Description" field in GitHub Desktop
5. Click "Commit to Norman" (or your branch name)
6. Push to GitHub

## Files Changed (20 files):
- ✅ Backend: Controllers, Models, Migrations, Seeders
- ✅ Frontend: Vue.js pages and components
- ✅ Routes: web.php with tender routes
- ✅ Documentation: 4 new markdown files

This commit message follows best practices:
- Clear, concise summary
- Detailed description with categories
- Lists all major changes
- Explains business logic
- Professional formatting
