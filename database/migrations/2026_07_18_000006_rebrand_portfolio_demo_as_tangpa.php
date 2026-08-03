<?php

use App\Models\Client;
use App\Models\Tenant;
use App\Models\TenantBlogPost;
use App\Models\TenantBusinessEnquiry;
use App\Models\TenantBusinessItem;
use App\Models\TenantService;
use App\Models\TenantTestimonial;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        $tenant = Tenant::where('subdomain', 'portfoliodemo')->first();
        if (!$tenant) {
            return;
        }

        $tenant->update([
            'name' => 'Tangpa',
            'contact_email' => 'hello@tangpa.example',
            'contact_phone' => '+91 87300 11001',
            'contact_address' => 'Lamka, Churachandpur, Manipur',
            'contact_hours' => 'Monday-Saturday, 9:30 AM-6:00 PM',
            'about_text' => 'Tangpa is a local creative and digital company in Lamka, Churachandpur. We help shops, organisations, and growing businesses build clear brands, professional websites, and practical digital experiences that connect with their communities.',
            'theme_settings' => array_merge($tenant->theme_settings ?? [], [
                'accent_color' => '#0f766e',
                'hero_eyebrow' => 'Local insight · Professional delivery',
                'hero_title' => 'Helping local businesses look ready for what comes next.',
                'hero_subtitle' => 'Tangpa brings brand, website, and digital support together for businesses and organisations across Lamka, Churachandpur and beyond.',
                'seo_title' => 'Tangpa - Creative and Digital Company in Lamka, Churachandpur',
                'seo_description' => 'Tangpa helps local companies with branding, websites, digital products, and practical business communication in Lamka, Churachandpur.',
            ]),
        ]);

        Client::whereKey($tenant->client_id)->update([
            'name' => 'Tangpa Demo Owner',
            'business_name' => 'Tangpa',
            'phone' => '+91 87300 11001',
            'whatsapp' => '+91 87300 11001',
            'address' => 'Lamka, Churachandpur, Manipur',
            'notes' => 'Internal Portfolio / Business lead demonstration for a professional local company.',
        ]);

        TenantService::where('tenant_id', $tenant->id)->delete();
        foreach ([
            ['Brand & Identity', 'Professional logos, visual systems, messaging, and business materials designed to build recognition and trust.', 25000],
            ['Business Websites', 'Responsive websites for local shops, professionals, institutions, and organisations, with clear content and enquiry capture.', 45000],
            ['Digital Support', 'Ongoing content, campaign, website, and digital communication support for teams that need a dependable local partner.', 12000],
        ] as [$name, $description, $price]) {
            TenantService::create(compact('name', 'description', 'price') + ['tenant_id' => $tenant->id]);
        }

        TenantBusinessItem::where('tenant_id', $tenant->id)->delete();
        foreach ([
            ['case_study', 'Lamka Market Collective', 'Retail · Brand & Website', 'Created a shared visual identity and mobile-first directory that helps shoppers discover participating local stores.', ['result' => '28 local businesses featured'], 1],
            ['case_study', 'Hillside Learning Centre', 'Education · Digital Presence', 'Reorganised programme information and admissions enquiries into a clear website built for parents using mobile phones.', ['result' => '2x more online enquiries'], 2],
            ['case_study', 'North East Harvest', 'Food · Packaging & Commerce', 'Developed a grounded product identity, packaging system, and catalogue experience for a growing regional food business.', ['result' => '12 products launched'], 3],
            ['team_member', 'Thangboi Haokip', 'Founder & Creative Lead', 'Thangboi leads brand direction and works closely with local clients to turn business goals into clear communication.', [], 1],
            ['team_member', 'Kimneilam Neihsial', 'Web & Content Lead', 'Kimneilam plans responsive websites and content systems that stay simple for clients and useful for customers.', [], 2],
            ['team_member', 'Lalminthang Guite', 'Client Support', 'Lalminthang coordinates projects, content collection, training, and ongoing support for every Tangpa client.', [], 3],
            ['career', 'Junior Web Designer', 'Lamka · On-site / Hybrid', 'Support website design, responsive layouts, content preparation, and client updates across local business projects.', ['deadline' => now()->addMonths(2)->toDateString()], 1],
            ['career', 'Content & Social Coordinator', 'Lamka · Full-time', 'Help local brands organise stories, photographs, campaigns, and practical social media content.', ['deadline' => now()->addMonths(2)->toDateString()], 2],
        ] as [$type, $title, $subtitle, $body, $meta, $sortOrder]) {
            TenantBusinessItem::create([
                'tenant_id' => $tenant->id, 'type' => $type, 'title' => $title,
                'slug' => Str::slug($title), 'subtitle' => $subtitle, 'body' => $body,
                'meta' => $meta, 'is_active' => true, 'sort_order' => $sortOrder,
            ]);
        }

        TenantTestimonial::where('tenant_id', $tenant->id)->delete();
        foreach ([
            ['Lhingneilam Gangte', 'Owner, Local Retail Business', 'Tangpa listened carefully and made the whole process understandable. Our new identity and website finally feel professional and true to us.'],
            ['Khaikholen Thadou', 'Director, Learning Centre', 'Parents can now find information quickly on their phones, and our team can manage enquiries without confusion.'],
            ['Niangboi Vungzagin', 'Founder, Food Brand', 'The team gave our products a clear, consistent look and helped us present them confidently to new buyers.'],
        ] as [$authorName, $authorRole, $content]) {
            TenantTestimonial::create(['tenant_id' => $tenant->id, 'author_name' => $authorName, 'author_role' => $authorRole, 'content' => $content, 'rating' => 5]);
        }

        TenantBlogPost::where('tenant_id', $tenant->id)->delete();
        foreach ([
            ['What every local business website should make clear', 'A useful website answers a few important questions immediately, especially for customers browsing on a phone.', 'Customers should quickly understand what you offer, where you are located, how to contact you, and why they can trust your business. Clear information matters more than unnecessary complexity.'],
            ['Building a brand that still feels local', 'Professional presentation does not require losing the character and values that make a local business distinct.', 'A strong local brand combines familiar language, relevant stories, and consistent visual choices with the clarity customers expect from any professional company.'],
        ] as [$title, $excerpt, $content]) {
            TenantBlogPost::create(['tenant_id' => $tenant->id, 'title' => $title, 'slug' => Str::slug($title), 'excerpt' => $excerpt, 'content' => $content, 'status' => 'published', 'published_at' => now()->subDays(10)]);
        }

        TenantBusinessEnquiry::where('tenant_id', $tenant->id)->where('email', 'demo.lead@example.com')->update([
            'name' => 'Local Business Demo Lead', 'subject' => 'Website and brand enquiry',
            'message' => 'We run a small business in Lamka and want a clearer brand, mobile-friendly website, and WhatsApp enquiry flow.',
        ]);
    }

    public function down(): void
    {
        // Demo rebranding is intentionally retained.
    }
};
