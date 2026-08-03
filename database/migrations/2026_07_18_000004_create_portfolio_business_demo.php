<?php

use App\Models\Client;
use App\Models\Tenant;
use App\Models\TenantBlogPost;
use App\Models\TenantBusinessEnquiry;
use App\Models\TenantBusinessItem;
use App\Models\TenantNewsletterSubscriber;
use App\Models\TenantService;
use App\Models\TenantTestimonial;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        $client = Client::updateOrCreate(['email' => 'portfolio-demo@ehlom.com'], [
            'name' => 'Aster Studio Demo Owner', 'business_name' => 'Aster Studio',
            'phone' => '+91 90000 11001', 'whatsapp' => '+91 90000 11001',
            'address' => 'Indiranagar, Bengaluru, India', 'status' => 'active',
            'notes' => 'Internal Portfolio / Business lead demonstration tenant.',
        ]);

        $tenant = Tenant::updateOrCreate(['subdomain' => 'portfoliodemo'], [
            'client_id' => $client->id, 'name' => 'Aster Studio', 'site_type' => 'business',
            'site_mode' => 'managed', 'template_id' => 'business', 'status' => 'active', 'plan' => 'Demo',
            'contact_email' => 'hello@asterstudio.example', 'contact_phone' => '+91 90000 11001',
            'contact_address' => 'Indiranagar, Bengaluru, India', 'contact_hours' => 'Monday-Friday, 9:30 AM-6:00 PM',
            'about_text' => 'Aster Studio is a strategy and design consultancy helping growing companies clarify their story, improve customer experience, and launch digital products that move the business forward.',
            'modules' => ['content', 'services', 'testimonials', 'blog', 'case_studies', 'team', 'careers', 'newsletter', 'enquiries'],
            'theme_settings' => [
                'accent_color' => '#0f766e', 'hero_eyebrow' => 'Independent strategy & design studio',
                'hero_title' => 'Clear ideas. Distinct brands. Better business.',
                'hero_subtitle' => 'We partner with ambitious teams to turn complex business challenges into focused brands, useful products, and experiences people choose.',
                'seo_title' => 'Aster Studio - Strategy, Brand and Digital Product Design',
                'seo_description' => 'A lead-ready Portfolio and Business demo with services, case studies, team, insights, careers, enquiries, and newsletter capture.',
            ],
        ]);

        User::updateOrCreate(['email' => 'owner@portfoliodemo.ehlom.com'], [
            'name' => 'Aster Demo Owner', 'password' => Hash::make(Str::random(64)), 'tenant_id' => $tenant->id,
        ]);

        foreach ([
            ['Brand Strategy', 'Positioning, messaging, and brand systems that give growing organisations a clear place in the market.', 85000],
            ['Digital Product Design', 'Research-led product and service experiences, from early concept through production-ready interface design.', 120000],
            ['Web Experience', 'High-performing websites that connect a distinctive story with simple, measurable customer journeys.', 95000],
        ] as [$name, $description, $price]) {
            TenantService::updateOrCreate(['tenant_id' => $tenant->id, 'name' => $name], compact('description', 'price'));
        }

        foreach ([
            ['case_study', 'Northline Financial Platform', 'Fintech · Product Design', 'Simplified an expert-only onboarding journey into a clear guided experience for first-time business customers.', ['result' => '42% faster onboarding'], 1],
            ['case_study', 'Fieldnote Hospitality Brand', 'Hospitality · Brand System', 'Built a flexible identity and digital launch system for a new collection of design-led neighbourhood stays.', ['result' => '3 properties launched'], 2],
            ['case_study', 'Nila Health Member Experience', 'Healthcare · Service Design', 'Connected fragmented booking, care, and follow-up touchpoints into one confident member journey.', ['result' => '31% fewer support requests'], 3],
            ['team_member', 'Maya Iyer', 'Strategy Director', 'Maya leads positioning, research, and organisational alignment for complex transformation programmes.', [], 1],
            ['team_member', 'Arjun Menon', 'Design Director', 'Arjun turns strategy into identity systems and digital experiences with clarity and character.', [], 2],
            ['team_member', 'Sara Thomas', 'Client Partner', 'Sara keeps teams aligned, decisions moving, and delivery grounded in measurable business outcomes.', [], 3],
            ['career', 'Senior Product Designer', 'Bengaluru · Hybrid', 'Lead research, interaction design, and product direction across a mix of growth-stage client engagements.', ['deadline' => now()->addMonths(2)->toDateString()], 1],
            ['career', 'Brand Strategist', 'Bengaluru / Remote', 'Shape positioning, customer insight, and verbal strategy for ambitious brands in transition.', ['deadline' => now()->addMonths(2)->toDateString()], 2],
        ] as [$type, $title, $subtitle, $body, $meta, $sortOrder]) {
            TenantBusinessItem::updateOrCreate(
                ['tenant_id' => $tenant->id, 'type' => $type, 'slug' => Str::slug($title)],
                ['title' => $title, 'subtitle' => $subtitle, 'body' => $body, 'meta' => $meta, 'is_active' => true, 'sort_order' => $sortOrder]
            );
        }

        foreach ([
            ['Rhea Kapoor', 'Founder, Fieldnote', 'Aster brought senior thinking into every conversation. The work gave our team a sharper story and a system we can actually use.'],
            ['Daniel Wu', 'COO, Northline', 'They reduced a complicated platform problem to a handful of clear decisions, then made the experience feel effortless.'],
            ['Priya Nair', 'Growth Lead, Nila Health', 'The final experience is both more human for members and easier for our operating teams to manage.'],
        ] as [$authorName, $authorRole, $content]) {
            TenantTestimonial::updateOrCreate(['tenant_id' => $tenant->id, 'author_name' => $authorName], ['author_role' => $authorRole, 'content' => $content, 'rating' => 5]);
        }

        foreach ([
            ['Why clarity is a growth strategy', 'Clear positioning is not a cosmetic exercise. It changes how teams decide, sell, and build.', 'The strongest brands make hard choices visible. In this field note, we outline a practical approach to finding the few ideas a growing organisation can consistently own.'],
            ['Designing services people can trust', 'Trust is built through the small operational moments customers experience every day.', 'Useful service design connects customer expectations, employee tools, and business constraints instead of treating the interface as an isolated layer.'],
        ] as [$title, $excerpt, $content]) {
            TenantBlogPost::updateOrCreate(['tenant_id' => $tenant->id, 'slug' => Str::slug($title)], ['title' => $title, 'excerpt' => $excerpt, 'content' => $content, 'status' => 'published', 'published_at' => now()->subDays(rand(4, 30))]);
        }

        TenantBusinessEnquiry::updateOrCreate(['tenant_id' => $tenant->id, 'email' => 'demo.lead@example.com'], [
            'type' => 'enquiry', 'name' => 'Demo Lead', 'subject' => 'Brand and website engagement',
            'message' => 'We are preparing for a new market launch and would like to discuss positioning, identity, and a new website.', 'status' => 'new',
        ]);
        TenantNewsletterSubscriber::updateOrCreate(['tenant_id' => $tenant->id, 'email' => 'subscriber@example.com'], ['name' => 'Demo Subscriber', 'status' => 'active']);
    }

    public function down(): void
    {
        Tenant::where('subdomain', 'portfoliodemo')->delete();
        User::where('email', 'owner@portfoliodemo.ehlom.com')->delete();
        Client::where('email', 'portfolio-demo@ehlom.com')->delete();
    }
};
