<?php

use App\Models\Client;
use App\Models\Tenant;
use App\Models\Theme;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        $html = <<<'HTML'
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>{{tenant.name}} | Business Advisory</title>
<style>
*{box-sizing:border-box}html{scroll-behavior:smooth}body{margin:0;color:#17221f;background:#f7f8f5;font-family:Arial,sans-serif}a{color:inherit}.wrap{width:min(1120px,calc(100% - 40px));margin:auto}.nav{height:74px;display:flex;align-items:center;justify-content:space-between;gap:24px}.brand{font-size:19px;font-weight:800}.navlinks{display:flex;gap:24px;font-size:13px;font-weight:700}.navlinks a{text-decoration:none}.hero{min-height:620px;display:grid;align-items:end;background:linear-gradient(90deg,rgba(9,30,25,.94),rgba(9,30,25,.45)),url('https://images.unsplash.com/photo-1497366811353-6870744d04b2?auto=format&fit=crop&w=1800&q=85') center/cover;color:#fff}.heroContent{max-width:760px;padding:100px 0 88px}.eyebrow{color:#d6b86d;font-size:12px;font-weight:800;letter-spacing:2px;text-transform:uppercase}.hero h1{margin:18px 0;font-family:Georgia,serif;font-size:clamp(44px,7vw,82px);font-weight:400;line-height:1.02}.hero p{max-width:620px;color:#d9e2df;font-size:18px;line-height:1.7}.button{display:inline-flex;margin-top:20px;padding:14px 19px;background:#d6b86d;color:#13231f;text-decoration:none;font-size:13px;font-weight:800}.strip{background:#d6b86d}.stripGrid{display:grid;grid-template-columns:repeat(3,1fr);gap:1px}.stripItem{padding:24px;border-right:1px solid rgba(19,35,31,.18);font-size:13px;font-weight:700}.section{padding:92px 0}.sectionHead{display:grid;grid-template-columns:.75fr 1.25fr;gap:60px;margin-bottom:46px}.section h2{margin:0;font-family:Georgia,serif;font-size:clamp(34px,5vw,58px);font-weight:400}.sectionLead{color:#5f6d68;font-size:17px;line-height:1.75}.services{display:grid;grid-template-columns:repeat(3,1fr);gap:16px}.card{padding:28px;border:1px solid #dfe4df;background:#fff}.number{color:#997c36;font-size:12px;font-weight:800}.card h3{margin:32px 0 12px;font-family:Georgia,serif;font-size:25px}.card p{margin:0;color:#697671;line-height:1.65}.dark{background:#10231e;color:#fff}.dark .sectionLead{color:#afbfba}.work{display:grid;grid-template-columns:1fr 1fr;gap:16px}.workItem{min-height:330px;padding:32px;display:flex;flex-direction:column;justify-content:end;background:#1a332c}.workItem:first-child{background:linear-gradient(0deg,rgba(12,34,28,.88),rgba(12,34,28,.16)),url('https://images.unsplash.com/photo-1524758631624-e2822e304c36?auto=format&fit=crop&w=1100&q=85') center/cover}.workItem:last-child{background:linear-gradient(0deg,rgba(12,34,28,.9),rgba(12,34,28,.2)),url('https://images.unsplash.com/photo-1521737711867-e3b97375f902?auto=format&fit=crop&w=1100&q=85') center/cover}.workItem h3{margin:8px 0 0;font-family:Georgia,serif;font-size:30px}.contact{display:grid;grid-template-columns:1fr 1fr;gap:80px}.contactList{display:grid;gap:16px}.contactRow{padding:16px 0;border-bottom:1px solid #dbe1dc;color:#53625d}.footer{padding:28px 0;border-top:1px solid #dbe1dc;color:#72807b;font-size:12px}.footer .wrap{display:flex;justify-content:space-between;gap:20px}@media(max-width:760px){.navlinks{display:none}.hero{min-height:560px}.heroContent{padding:72px 0}.stripGrid,.services,.work,.sectionHead,.contact{grid-template-columns:1fr}.stripItem{border-right:0;border-bottom:1px solid rgba(19,35,31,.18)}.section{padding:66px 0}.sectionHead,.contact{gap:28px}.workItem{min-height:280px}.footer .wrap{flex-direction:column}}
</style>
</head>
<body>
<header class="wrap nav"><div class="brand">{{tenant.name}}</div><nav class="navlinks"><a href="#services">Services</a><a href="#work">Work</a><a href="#about">About</a><a href="#contact">Contact</a></nav></header>
<main>
<section class="hero"><div class="wrap heroContent"><div class="eyebrow">Independent business advisory</div><h1>Clear direction for ambitious local businesses.</h1><p>We help founders organise their strategy, strengthen operations, and turn good ideas into confident, sustainable companies.</p><a class="button" href="#contact">Start a conversation</a></div></section>
<div class="strip"><div class="wrap stripGrid"><div class="stripItem">Strategy grounded in local reality</div><div class="stripItem">Practical systems your team can use</div><div class="stripItem">Direct access to senior advisors</div></div></div>
<section class="section" id="services"><div class="wrap"><div class="sectionHead"><div><div class="eyebrow">What we do</div><h2>Focused help where it matters.</h2></div><p class="sectionLead">From a first operating plan to the next stage of growth, our work is designed to create clarity, build useful systems, and help teams make better decisions.</p></div><div class="services"><article class="card"><div class="number">01</div><h3>Business Strategy</h3><p>Positioning, growth priorities, practical plans, and decision frameworks built around your real market.</p></article><article class="card"><div class="number">02</div><h3>Operations</h3><p>Clear processes, roles, and service systems that make daily work easier and customer experiences stronger.</p></article><article class="card"><div class="number">03</div><h3>Digital Direction</h3><p>A sensible roadmap for websites, customer tools, communication, and technology investment.</p></article></div></div></section>
<section class="section dark" id="work"><div class="wrap"><div class="sectionHead"><div><div class="eyebrow">Selected work</div><h2>Progress you can see.</h2></div><p class="sectionLead">We work closely with small companies, family businesses, and growing organisations to solve practical challenges without unnecessary complexity.</p></div><div class="work"><article class="workItem"><div class="eyebrow">Retail growth</div><h3>A clearer model for three new locations</h3></article><article class="workItem"><div class="eyebrow">Team operations</div><h3>One shared system for a growing service company</h3></article></div></div></section>
<section class="section" id="about"><div class="wrap sectionHead"><div><div class="eyebrow">About us</div><h2>Experienced, accessible, and practical.</h2></div><p class="sectionLead">{{tenant.about}} We believe strong advice should be understandable, honest, and useful on Monday morning, not only impressive in a presentation.</p></div></section>
<section class="section" id="contact"><div class="wrap contact"><div><div class="eyebrow">Contact</div><h2>Let’s discuss what comes next.</h2><p class="sectionLead">Tell us where the business is today and what you want to improve. We will help identify a sensible first step.</p></div><div class="contactList"><div class="contactRow"><strong>Email</strong><br>{{tenant.contact_email}}</div><div class="contactRow"><strong>Phone</strong><br>{{tenant.contact_phone}}</div><div class="contactRow"><strong>Office</strong><br>{{tenant.contact_address}}</div></div></div></section>
</main>
<footer class="footer"><div class="wrap"><span>© 2026 {{tenant.name}}</span><span>Independent business advisory</span></div></footer>
</body></html>
HTML;

        $theme = Theme::updateOrCreate(['key' => 'portfolio-static-approved'], [
            'name' => 'Portfolio Static Approved',
            'description' => 'A responsive one-page HTML portfolio demonstrating static approved delivery.',
            'base_template' => 'business',
            'custom_html' => $html,
            'default_settings' => [],
            'industries' => ['business'],
            'public' => true,
        ]);

        $client = Client::updateOrCreate(['email' => 'static-portfolio-demo@ehlom.com'], [
            'name' => 'Summit Advisory Demo Owner',
            'business_name' => 'Summit Advisory',
            'phone' => '+91 90000 12001',
            'whatsapp' => '+91 90000 12001',
            'address' => 'Lamka, Churachandpur, Manipur',
            'status' => 'active',
            'notes' => 'Internal static approved HTML Portfolio / Business demonstration.',
        ]);

        $tenant = Tenant::updateOrCreate(['subdomain' => 'staticportfoliodemo'], [
            'client_id' => $client->id,
            'name' => 'Summit Advisory',
            'site_type' => 'business',
            'site_mode' => 'static',
            'template_id' => $theme->key,
            'status' => 'active',
            'plan' => 'Static Demo',
            'contact_email' => 'hello@summitadvisory.example',
            'contact_phone' => '+91 90000 12001',
            'contact_address' => 'Lamka, Churachandpur, Manipur',
            'about_text' => 'Summit Advisory supports local founders and growing teams with business planning, operations, and practical digital direction.',
            'modules' => [],
        ]);

        User::updateOrCreate(['email' => 'owner@staticportfoliodemo.ehlom.com'], [
            'name' => 'Static Portfolio Demo Owner',
            'password' => Hash::make(Str::random(64)),
            'tenant_id' => $tenant->id,
        ]);
    }

    public function down(): void
    {
        Tenant::where('subdomain', 'staticportfoliodemo')->delete();
        User::where('email', 'owner@staticportfoliodemo.ehlom.com')->delete();
        Client::where('email', 'static-portfolio-demo@ehlom.com')->delete();
        Theme::where('key', 'portfolio-static-approved')->delete();
    }
};
