<?php

namespace Database\Seeders;

use App\Models\Tender;
use App\Models\TenderBid;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Database\Seeder;

class TenderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::first();
        
        if (!$user) {
            $this->command->warn('No users found. Please create a user first.');
            return;
        }

        $vendors = Vendor::limit(5)->get();
        
        if ($vendors->isEmpty()) {
            $this->command->warn('No vendors found. Please create vendors first.');
            return;
        }

        // Create Published Tender (Open for Bidding)
        $tender1 = Tender::create([
            'tender_number' => Tender::generateTenderNumber(),
            'title' => 'Office Furniture Supply',
            'description' => 'Supply of office furniture including desks, chairs, and filing cabinets for new office building.',
            'estimated_budget' => 50000.00,
            'opening_date' => now()->subDays(5),
            'closing_date' => now()->addDays(10),
            'status' => 'Published',
            'requirements' => "- Minimum 5 years experience in office furniture supply\n- ISO 9001 certified\n- Provide warranty for at least 2 years\n- Delivery within 30 days",
            'terms_conditions' => "- Payment terms: 30 days after delivery\n- Penalties apply for late delivery\n- All items must meet specified quality standards",
            'created_by' => $user->id,
        ]);

        // Add bids for tender 1
        foreach ($vendors->take(3) as $index => $vendor) {
            TenderBid::create([
                'tender_id' => $tender1->id,
                'vendor_id' => $vendor->id,
                'bid_amount' => 45000 + ($index * 2000),
                'proposal' => "We propose to supply high-quality office furniture that meets all your requirements. Our products are sourced from reputable manufacturers and come with comprehensive warranties.",
                'technical_specifications' => "- Ergonomic office chairs with adjustable height\n- Solid wood desks with cable management\n- Steel filing cabinets with security locks",
                'delivery_timeline_days' => 25 + ($index * 5),
                'status' => 'Submitted',
                'submitted_at' => now()->subDays(3 - $index),
            ]);
        }

        // Create Closed Tender
        $tender2 = Tender::create([
            'tender_number' => Tender::generateTenderNumber(),
            'title' => 'IT Equipment Procurement',
            'description' => 'Procurement of laptops, monitors, and networking equipment for company expansion.',
            'estimated_budget' => 100000.00,
            'opening_date' => now()->subDays(30),
            'closing_date' => now()->subDays(5),
            'status' => 'Closed',
            'requirements' => "- Latest generation processors\n- Minimum 16GB RAM\n- 512GB SSD storage\n- 3-year warranty",
            'terms_conditions' => "- Payment in 2 installments\n- Installation and setup included\n- Technical support for 1 year",
            'created_by' => $user->id,
        ]);

        // Add bids for tender 2
        foreach ($vendors->take(4) as $index => $vendor) {
            TenderBid::create([
                'tender_id' => $tender2->id,
                'vendor_id' => $vendor->id,
                'bid_amount' => 95000 + ($index * 3000),
                'proposal' => "Complete IT equipment solution with installation and support services included.",
                'technical_specifications' => "- Intel Core i7 processors\n- 16GB DDR4 RAM\n- 512GB NVMe SSD\n- 24-inch Full HD monitors",
                'delivery_timeline_days' => 20 + ($index * 3),
                'status' => 'Under Review',
                'submitted_at' => now()->subDays(10 - $index),
            ]);
        }

        // Create Awarded Tender
        $tender3 = Tender::create([
            'tender_number' => Tender::generateTenderNumber(),
            'title' => 'Cleaning Services Contract',
            'description' => 'Annual cleaning services contract for office premises.',
            'estimated_budget' => 30000.00,
            'opening_date' => now()->subDays(60),
            'closing_date' => now()->subDays(30),
            'status' => 'Awarded',
            'requirements' => "- Licensed cleaning service provider\n- Eco-friendly cleaning products\n- Daily cleaning schedule\n- Emergency cleaning available",
            'terms_conditions' => "- Monthly payment\n- 12-month contract\n- Performance review every quarter",
            'created_by' => $user->id,
        ]);

        // Add winning bid
        $winningBid = TenderBid::create([
            'tender_id' => $tender3->id,
            'vendor_id' => $vendors->first()->id,
            'bid_amount' => 28000.00,
            'proposal' => "Professional cleaning services with experienced staff and eco-friendly products.",
            'technical_specifications' => "- Daily cleaning: 5 days/week\n- Deep cleaning: monthly\n- All eco-certified products\n- 24/7 emergency response",
            'delivery_timeline_days' => 7,
            'status' => 'Accepted',
            'submitted_at' => now()->subDays(35),
        ]);

        // Update tender with awarded bid
        $tender3->update([
            'awarded_bid_id' => $winningBid->id,
            'awarded_at' => now()->subDays(25),
        ]);

        // Add rejected bids
        foreach ($vendors->skip(1)->take(2) as $index => $vendor) {
            TenderBid::create([
                'tender_id' => $tender3->id,
                'vendor_id' => $vendor->id,
                'bid_amount' => 30000 + ($index * 2000),
                'proposal' => "Quality cleaning services for your office.",
                'technical_specifications' => "Standard cleaning services with professional equipment.",
                'delivery_timeline_days' => 10 + ($index * 5),
                'status' => 'Rejected',
                'notes' => 'Another bid was selected',
                'submitted_at' => now()->subDays(35 - $index),
            ]);
        }

        // Create Draft Tender
        Tender::create([
            'tender_number' => Tender::generateTenderNumber(),
            'title' => 'Security System Installation',
            'description' => 'Installation of CCTV cameras and access control systems.',
            'estimated_budget' => 75000.00,
            'opening_date' => now()->addDays(7),
            'closing_date' => now()->addDays(30),
            'status' => 'Draft',
            'requirements' => "- IP-based CCTV system\n- Minimum 4K resolution\n- Cloud storage integration\n- Mobile app access",
            'terms_conditions' => "- Payment upon completion\n- 5-year warranty\n- Annual maintenance included",
            'created_by' => $user->id,
        ]);

        $this->command->info('Tender seeder completed successfully!');
        $this->command->info('Created 4 tenders with various statuses and bids.');
    }
}
