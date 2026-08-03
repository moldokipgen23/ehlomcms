<?php

use App\Models\Client;
use App\Models\Tenant;
use App\Models\TenantBusinessItem;
use App\Models\TenantCustomPage;
use App\Models\TenantGalleryImage;
use App\Models\TenantTestimonial;
use App\Models\Theme;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        $theme = Theme::updateOrCreate(['key' => 'school/eiho'], [
            'name' => 'Eiho School Academy',
            'description' => 'A welcoming, mobile-first school website demo for public information and admissions enquiries.',
            'base_template' => 'eiho-school',
            'default_settings' => ['accent_color' => '#2563A6'],
            'industries' => ['school'],
            'public' => true,
        ]);

        $client = Client::updateOrCreate(['email' => 'eiho-school-demo@ehlom.com'], [
            'name' => 'Eiho School Academy Demo Owner',
            'business_name' => 'Eiho School Academy',
            'phone' => '+91 84028 31826',
            'whatsapp' => '+91 84028 31826',
            'address' => 'Lamka, Churachandpur, Manipur - 795128',
            'status' => 'active',
            'notes' => 'Internal School Website module demonstration. Content is editable demo data unless marked verified.',
        ]);

        $settings = [
            'seo_title' => 'Eiho School Academy | Lamka, Churachandpur',
            'seo_description' => 'Eiho School Academy provides a caring, disciplined and academically focused learning environment from Nursery to Class X.',
            'hero_title' => 'Inspiring Young Minds for a Brighter Tomorrow',
            'hero_subtitle' => 'Eiho School Academy provides a caring, disciplined and academically focused learning environment for students from Nursery to Class X.',
            'about_text' => 'Eiho School Academy is a modern educational institution located in Lamka, Churachandpur. The school is committed to providing strong academic foundations, English communication skills, discipline, creativity and all-round development in a welcoming environment.',
            'principal_name' => 'Dr. Grace Haokip',
            'principal_title' => 'Principal - editable demonstration profile',
            'office_hours' => 'Open daily until 12:00 AM',
            'whatsapp_number' => '918402831826',
            'hero_image' => 'https://images.unsplash.com/photo-1580582932707-520aed937b7b?auto=format&fit=crop&w=2000&q=85',
            'about_image' => 'https://images.unsplash.com/photo-1503676260728-1c00da094a0b?auto=format&fit=crop&w=1200&q=85',
            'learning_image' => 'https://images.unsplash.com/photo-1577896851231-70ef18881754?auto=format&fit=crop&w=1000&q=85',
            'privacy_policy' => 'This demonstration privacy policy should be replaced with the school\'s approved policy before launch.',
            'terms_conditions' => 'This demonstration terms page should be replaced with the school\'s approved terms before launch.',
            'refund_policy' => 'No online payments are collected by this school website demo. Replace this page if the school adds a paid service.',
        ];

        $modules = ['content', 'hero', 'stats', 'about', 'academics', 'admissions', 'faculty', 'student_life', 'facilities', 'gallery', 'news', 'achievements', 'testimonials', 'downloads', 'certificates', 'contact', 'why_choose', 'map', 'enquiry_form'];
        $tenant = Tenant::updateOrCreate(['subdomain' => 'eihoschooldemo'], [
            'client_id' => $client->id,
            'name' => 'Eiho School Academy',
            'site_type' => 'school',
            'site_mode' => 'managed',
            'template_id' => $theme->key,
            'status' => 'active',
            'plan' => 'School Demo',
            'contact_email' => 'info@eihoschoolacademy.in',
            'contact_phone' => '+91 84028 31826',
            'whatsapp_number' => '+91 84028 31826',
            'contact_address' => 'Lamka, Churachandpur, Manipur - 795128',
            'contact_hours' => 'Open daily until 12:00 AM',
            'about_text' => $settings['about_text'],
            'theme_settings' => $settings,
            'modules' => $modules,
        ]);

        User::updateOrCreate(['email' => 'owner@eihoschooldemo.ehlom.com'], [
            'name' => 'Eiho Demo Administrator',
            'password' => Hash::make(Str::random(64)),
            'tenant_id' => $tenant->id,
        ]);

        $items = [
            ['academic_program', 'Foundation Stage', 'Nursery and Kindergarten', 'A gentle start that builds language, routines, curiosity and joyful learning habits.', 1],
            ['academic_program', 'Primary Stage', 'Classes I to V', 'Core literacy, numeracy, science, social learning and confidence through exploration.', 2],
            ['academic_program', 'Middle Stage', 'Classes VI to VIII', 'Deeper subject learning, communication skills and individual guidance for growing learners.', 3],
            ['academic_program', 'Secondary Stage', 'Classes IX and X', 'Focused preparation, practical learning and the confidence to take the next step.', 4],
            ['faculty_member', 'Dr. Grace Haokip', 'Principal / School Leadership', 'A fictional editable demonstration profile for the school website.', 1],
            ['faculty_member', 'N. Sarah Mate', 'Primary Teacher / English', 'Supports confident reading, writing and communication across the primary years.', 2],
            ['faculty_member', 'David Lalremruata', 'Science and Computer Learning', 'Makes practical learning and digital awareness accessible to young learners.', 3],
            ['faculty_member', 'Martha Kipgen', 'Early Years Educator', 'Creates warm, structured learning experiences for the foundation stage.', 4],
            ['faculty_member', 'Rosemary Gangte', 'Arts and Activities', 'Guides creative expression through art, music and school celebrations.', 5],
            ['facility', 'Modern Classrooms', 'Campus space', 'Editable demonstration facility information for the school website.', 1],
            ['facility', 'Computer Learning', 'Learning support', 'Editable demonstration facility information for the school website.', 2],
            ['facility', 'School Library', 'Reading and discovery', 'Editable demonstration facility information for the school website.', 3],
            ['facility', 'Science Activities', 'Practical learning', 'Editable demonstration facility information for the school website.', 4],
            ['facility', 'Sports and Playground', 'Student life', 'Editable demonstration facility information for the school website.', 5],
            ['facility', 'Safe Drinking Water', 'Student wellbeing', 'Editable demonstration facility information for the school website.', 6],
            ['facility', 'CCTV and Campus Safety', 'Safety support', 'Editable demonstration facility information for the school website.', 7],
            ['facility', 'First Aid Support', 'Student wellbeing', 'Editable demonstration facility information for the school website.', 8],
            ['student_activity', 'Cultural Programs', 'Student life', 'Editable demonstration content for celebrations, performance and community participation.', 1],
            ['student_activity', 'Sports Day', 'Student life', 'Editable demonstration content for teamwork, movement and healthy competition.', 2],
            ['student_activity', 'Art and Creativity', 'Student life', 'Editable demonstration content for imagination, making and self-expression.', 3],
            ['student_activity', 'Community Participation', 'Student life', 'Editable demonstration content for service, care and local connection.', 4],
            ['achievement', 'Academic Achievement', 'Demo milestone', 'Fictional demonstration achievement ready to be replaced with the official school record.', 1],
            ['achievement', 'Sports Recognition', 'Demo milestone', 'Fictional demonstration achievement ready to be replaced with the official school record.', 2],
            ['achievement', 'Debate and Quiz Participation', 'Demo milestone', 'Fictional demonstration achievement ready to be replaced with the official school record.', 3],
            ['achievement', 'Art and Cultural Recognition', 'Demo milestone', 'Fictional demonstration achievement ready to be replaced with the official school record.', 4],
            ['achievement', 'Community Service', 'Demo milestone', 'Fictional demonstration achievement ready to be replaced with the official school record.', 5],
            ['school_notice', 'Admissions Open for the 2026-2027 Academic Session', 'Announcement', 'Prospective families can submit an enquiry to learn about classes, visits and the admission process.', 1],
            ['school_notice', 'Parent Orientation Programme', 'Event', 'Editable demonstration notice for a parent orientation programme.', 2],
            ['school_notice', 'Annual Sports and Cultural Week', 'News', 'Editable demonstration notice for the school calendar.', 3],
            ['school_notice', 'New Academic Calendar Released', 'Notice', 'Editable demonstration notice for the school calendar.', 4],
        ];

        foreach ($items as [$type, $title, $subtitle, $body, $sort]) {
            TenantBusinessItem::updateOrCreate(
                ['tenant_id' => $tenant->id, 'type' => $type, 'slug' => Str::slug($title)],
                ['title' => $title, 'subtitle' => $subtitle, 'body' => $body, 'meta' => $type === 'school_notice' ? ['day' => '06', 'month' => 'JUL'] : [], 'is_active' => true, 'sort_order' => $sort]
            );
        }

        foreach ([
            ['A parent', 'Parent', 'The school feels welcoming, organised and focused on helping children grow with confidence.'],
            ['A student', 'Student', 'I enjoy learning with my classmates and taking part in activities beyond our lessons.'],
            ['An alumna', 'Alumna', 'Eiho gave me a strong foundation and encouraged me to keep learning with purpose.'],
        ] as [$name, $role, $content]) {
            TenantTestimonial::updateOrCreate(['tenant_id' => $tenant->id, 'author_name' => $name], ['author_role' => $role, 'content' => $content, 'rating' => 5]);
        }

        foreach ([
            ['about-school', 'About the School', 'Eiho School Academy is a modern educational institution in Lamka, Churachandpur, committed to strong academic foundations, communication, discipline and all-round development.'],
            ['academics', 'Academics', 'Explore the editable demonstration learning stages from Nursery to Class X and the school learning approach.'],
            ['admissions', 'Admissions', 'Admissions are open for the 2026-2027 demonstration session. Contact the school for the latest verified information.'],
            ['contact', 'Contact', 'Eiho School Academy, Lamka, Churachandpur, Manipur - 795128. Phone +91 84028 31826. Email info@eihoschoolacademy.in.'],
            ['privacy-policy', 'Privacy Policy', $settings['privacy_policy']],
        ] as [$slug, $title, $content]) {
            TenantCustomPage::updateOrCreate(['tenant_id' => $tenant->id, 'slug' => $slug], ['title' => $title, 'content' => $content, 'is_published' => true, 'sort_order' => 1]);
        }

        $gallery = [
            ['Campus', '#2563A6', '#EAF4FF'], ['Classrooms', '#123B6D', '#D8A63C'], ['Sports Day', '#15803D', '#EAF4FF'], ['Cultural Programme', '#8B5E34', '#FFF4D6'],
            ['Student Activities', '#2563A6', '#DDEEFF'], ['Celebrations', '#123B6D', '#F4E5B7'],
        ];
        foreach ($gallery as $index => [$label, $primary, $secondary]) {
            $path = "tenants/{$tenant->id}/school-demo/" . Str::slug($label) . '.svg';
            $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 900 700"><defs><linearGradient id="g" x1="0" x2="1" y1="0" y2="1"><stop stop-color="' . $primary . '"/><stop offset="1" stop-color="' . $secondary . '"/></linearGradient></defs><rect width="900" height="700" fill="url(#g)"/><circle cx="750" cy="145" r="110" fill="rgba(255,255,255,.18)"/><path d="M0 570L210 390 360 500 550 290 900 570V700H0Z" fill="rgba(255,255,255,.18)"/><text x="55" y="105" fill="white" font-family="Arial,sans-serif" font-size="26" font-weight="700">EIHO SCHOOL ACADEMY</text><text x="55" y="635" fill="white" font-family="Arial,sans-serif" font-size="42" font-weight="700">' . e($label) . '</text><text x="58" y="675" fill="rgba(255,255,255,.8)" font-family="Arial,sans-serif" font-size="18">Editable demonstration image</text></svg>';
            Storage::disk('public')->put($path, $svg);
            TenantGalleryImage::updateOrCreate(['tenant_id' => $tenant->id, 'image_path' => $path], ['caption' => $label, 'sort_order' => $index + 1]);
        }
    }

    public function down(): void
    {
        $tenant = Tenant::where('subdomain', 'eihoschooldemo')->first();
        if ($tenant) {
            TenantBusinessItem::where('tenant_id', $tenant->id)->delete();
            TenantCustomPage::where('tenant_id', $tenant->id)->delete();
            TenantGalleryImage::where('tenant_id', $tenant->id)->delete();
            TenantTestimonial::where('tenant_id', $tenant->id)->delete();
            User::where('tenant_id', $tenant->id)->delete();
            $tenant->delete();
        }
        Client::where('email', 'eiho-school-demo@ehlom.com')->delete();
        Theme::where('key', 'school/eiho')->delete();
    }
};
