@extends('tenant.layouts.dashboard')

@section('title', $tenant->site_type === 'shopping' ? 'Storefront Editor' : 'Customise Theme')
@section('subtitle', $tenant->site_type === 'shopping' ? 'Manage public shop content, checkout copy, and brand styling' : 'Edit your school website content')

@section('content')

@if ($tenant->site_type === 'shopping')
<style>
    .storefront-editor {
        display: grid;
        gap: 18px;
    }
    .storefront-editor-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 16px;
        padding: 18px;
        border: 1px solid var(--border-card);
        border-radius: 12px;
        background: #fff;
        box-shadow: 0 14px 34px rgba(15,23,42,.06);
    }
    .storefront-editor-kicker {
        color: var(--accent-blue);
        font-size: 10px;
        font-weight: 900;
        letter-spacing: 1.2px;
        text-transform: uppercase;
    }
    .storefront-editor-title {
        margin-top: 6px;
        color: var(--text-primary);
        font-family: 'Syne', sans-serif;
        font-size: 24px;
        font-weight: 800;
    }
    .storefront-editor-copy {
        max-width: 760px;
        margin-top: 6px;
        color: var(--text-muted);
        font-size: 12.5px;
        line-height: 1.6;
    }
    .storefront-tabs {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        padding: 8px;
        border: 1px solid var(--border-card);
        border-radius: 12px;
        background: #fff;
        box-shadow: 0 10px 26px rgba(15,23,42,.05);
    }
    .storefront-tab {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        min-height: 36px;
        padding: 8px 12px;
        border: 1px solid transparent;
        border-radius: 9px;
        background: transparent;
        color: var(--text-secondary);
        font-size: 12px;
        font-weight: 800;
        cursor: pointer;
    }
    .storefront-tab:hover {
        background: #eef4ff;
        color: #1d4ed8;
    }
    .storefront-tab.is-active {
        background: #2563eb;
        border-color: #2563eb;
        color: #fff;
        box-shadow: 0 10px 22px rgba(37,99,235,.18);
    }
    .storefront-panel {
        padding: 22px;
    }
    .storefront-panel-title {
        color: var(--text-primary);
        font-family: 'Syne', sans-serif;
        font-size: 18px;
        font-weight: 800;
        margin-bottom: 4px;
    }
    .storefront-panel-sub {
        color: var(--text-muted);
        font-size: 12px;
        margin-bottom: 18px;
    }
    .storefront-grid-2 {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 16px;
    }
    .storefront-grid-3 {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 16px;
    }
    .storefront-help {
        margin-top: 12px;
        padding: 13px 14px;
        border: 1px solid #dbeafe;
        border-radius: 10px;
        background: #eff6ff;
        color: #475569;
        font-size: 12px;
        line-height: 1.6;
    }
    .storefront-save-bar {
        position: sticky;
        bottom: 18px;
        z-index: 12;
        display: flex;
        justify-content: flex-end;
        padding-top: 4px;
    }
    @media (max-width: 760px) {
        .storefront-editor-head { flex-direction: column; }
        .storefront-tabs { overflow-x: auto; flex-wrap: nowrap; padding-bottom: 10px; }
        .storefront-tab { white-space: nowrap; }
        .storefront-grid-2,
        .storefront-grid-3 { grid-template-columns: 1fr; }
        .storefront-panel { padding: 16px; }
        .storefront-save-bar { bottom: 86px; }
    }
</style>

<form method="POST" action="{{ route('tenant.theme.update') }}" x-data="{ activeTab: new URLSearchParams(window.location.search).get('tab') || window.location.hash.replace('#', '') || 'hero' }" enctype="multipart/form-data" class="storefront-editor">
    @csrf

    <section class="storefront-editor-head">
        <div>
            <div class="storefront-editor-kicker">Public Storefront</div>
            <div class="storefront-editor-title">Storefront Editor</div>
            <div class="storefront-editor-copy">These controls update the customer-facing shopping website: hero copy, catalog labels, trust messages, checkout wording, social links, footer, and brand color.</div>
        </div>
        <a href="{{ url('/') }}" class="eos-btn eos-btn-secondary" target="_blank" rel="noopener"><i class="ti ti-external-link"></i> Preview Store</a>
    </section>

    <div class="storefront-tabs">
        @foreach ([
            'hero' => ['ti-home-star', 'Hero'],
            'story' => ['ti-book-2', 'Brand Story'],
            'catalog' => ['ti-layout-grid', 'Catalog'],
            'trust' => ['ti-shield-check', 'Trust'],
            'checkout' => ['ti-brand-whatsapp', 'Checkout'],
            'policies' => ['ti-file-description', 'Policies'],
            'social' => ['ti-share', 'Social & Footer'],
            'style' => ['ti-palette', 'Style'],
            'premium' => ['ti-sparkles', 'Premium FX'],
        ] as $tabKey => [$tabIcon, $tabLabel])
            <button type="button" @click="activeTab = '{{ $tabKey }}'"
                class="storefront-tab"
                :class="{ 'is-active': activeTab === '{{ $tabKey }}' }">
                <i class="ti {{ $tabIcon }}"></i> {{ $tabLabel }}
            </button>
        @endforeach
    </div>

    <div x-show="activeTab === 'hero'" x-cloak class="store-panel-clean storefront-panel">
        <div class="storefront-panel-title">Hero</div>
        <div class="storefront-panel-sub">Main first-screen copy on the public shop.</div>
        <div class="storefront-grid-2">
            <div class="eos-field">
                <label class="eos-label">Hero Eyebrow</label>
                <input type="text" name="store_hero_eyebrow" value="{{ $settings['store_hero_eyebrow'] ?? '' }}" class="eos-input" placeholder="Handmade premium collections">
            </div>
            <div class="eos-field">
                <label class="eos-label">Hero Headline</label>
                <input type="text" name="store_hero_title" value="{{ $settings['store_hero_title'] ?? '' }}" class="eos-input" placeholder="Jem Designs">
            </div>
            <div class="eos-field" style="grid-column:1/-1;">
                <label class="eos-label">Hero Subtitle</label>
                <textarea name="store_hero_subtitle" class="eos-input" rows="3" placeholder="Short premium store intro...">{{ $settings['store_hero_subtitle'] ?? '' }}</textarea>
            </div>
            <div class="eos-field">
                <label class="eos-label">Primary CTA Text</label>
                <input type="text" name="store_primary_cta" value="{{ $settings['store_primary_cta'] ?? 'Shop Now' }}" class="eos-input" placeholder="Shop Now">
            </div>
            <div class="eos-field">
                <label class="eos-label">Secondary CTA Text</label>
                <input type="text" name="store_secondary_cta" value="{{ $settings['store_secondary_cta'] ?? 'View Collections' }}" class="eos-input" placeholder="View Collections">
            </div>
            <div class="eos-field" style="grid-column:1/-1;">
                <label class="eos-label">Hero Image</label>
                <input type="file" name="jem_hero_image_file" accept="image/*" class="eos-input">
                @if (!empty($settings['jem_hero_image']))
                    <div class="eos-row-type" style="margin-top:8px;">Current image is uploaded. Upload a new file to replace it.</div>
                @else
                    <div class="eos-row-type" style="margin-top:8px;">If empty, the approved Jem demo hero image is used.</div>
                @endif
            </div>
        </div>
    </div>

    <div x-show="activeTab === 'story'" x-cloak class="store-panel-clean storefront-panel">
        <div class="storefront-panel-title">Brand Story</div>
        <div class="storefront-panel-sub">Controls the About section and brand highlights.</div>
        <div class="eos-field">
            <label class="eos-label">About / Brand Story Title</label>
            <input type="text" name="about_title" value="{{ $settings['about_title'] ?? '' }}" class="eos-input" placeholder="About Jem Designs">
        </div>
        <div class="eos-field">
            <label class="eos-label">Store About Text</label>
            <textarea name="about_text_raw" class="eos-input" rows="5" placeholder="Tell customers about the brand, craft, products, and promise...">{{ $settings['about_text'] ?? $tenant->about_text ?? '' }}</textarea>
            <input type="hidden" name="about_text_target" value="tenant">
        </div>
        <div class="storefront-grid-3">
            <div class="eos-field"><label class="eos-label">Highlight 1</label><input name="store_highlight_1" value="{{ $settings['store_highlight_1'] ?? '' }}" class="eos-input" placeholder="Handcrafted"></div>
            <div class="eos-field"><label class="eos-label">Highlight 2</label><input name="store_highlight_2" value="{{ $settings['store_highlight_2'] ?? '' }}" class="eos-input" placeholder="Premium quality"></div>
            <div class="eos-field"><label class="eos-label">Highlight 3</label><input name="store_highlight_3" value="{{ $settings['store_highlight_3'] ?? '' }}" class="eos-input" placeholder="Made in India"></div>
        </div>
        <div class="storefront-grid-2" style="margin-top:16px;">
            <div class="eos-field">
                <label class="eos-label">Story Image</label>
                <input type="file" name="jem_story_image_file" accept="image/*" class="eos-input">
            </div>
            <div class="eos-field">
                <label class="eos-label">Founder Main Image</label>
                <input type="file" name="jem_founder_image_file" accept="image/*" class="eos-input">
            </div>
            <div class="eos-field">
                <label class="eos-label">Founder Detail Image</label>
                <input type="file" name="jem_detail_image_file" accept="image/*" class="eos-input">
            </div>
            <div class="eos-field">
                <label class="eos-label">Founder Accent Image</label>
                <input type="file" name="jem_accent_image_file" accept="image/*" class="eos-input">
            </div>
        </div>
    </div>

    <div x-show="activeTab === 'catalog'" x-cloak class="store-panel-clean storefront-panel">
        <div class="storefront-panel-title">Catalog Labels</div>
        <div class="storefront-panel-sub">Names for product sections. Product data is managed under Products.</div>
        <div class="storefront-grid-2">
            <div class="eos-field"><label class="eos-label">Featured Products Title</label><input name="featured_products_title" value="{{ $settings['featured_products_title'] ?? '' }}" class="eos-input" placeholder="Featured Products"></div>
            <div class="eos-field"><label class="eos-label">New Arrivals Title</label><input name="new_arrivals_title" value="{{ $settings['new_arrivals_title'] ?? '' }}" class="eos-input" placeholder="New Arrivals"></div>
            <div class="eos-field"><label class="eos-label">Collections Title</label><input name="collections_title" value="{{ $settings['collections_title'] ?? '' }}" class="eos-input" placeholder="Shop by Collection"></div>
            <div class="eos-field"><label class="eos-label">Top Sellers Title</label><input name="top_sellers_title" value="{{ $settings['top_sellers_title'] ?? '' }}" class="eos-input" placeholder="Best Sellers"></div>
        </div>
        <div class="storefront-help">
            Product cards, collections, variants, inventory, and marketing sections are managed from their own Store menu pages.
        </div>
    </div>

    <div x-show="activeTab === 'trust'" x-cloak class="store-panel-clean storefront-panel">
        <div class="storefront-panel-title">Trust Messages</div>
        <div class="storefront-panel-sub">Shown as a compact trust strip below the hero when filled.</div>
        <div class="storefront-grid-2">
            <div class="eos-field"><label class="eos-label">Shipping Promise</label><input name="shipping_promise" value="{{ $settings['shipping_promise'] ?? '' }}" class="eos-input" placeholder="Ships in 2-4 business days"></div>
            <div class="eos-field"><label class="eos-label">Return Policy</label><input name="return_policy" value="{{ $settings['return_policy'] ?? '' }}" class="eos-input" placeholder="Easy 7-day returns"></div>
            <div class="eos-field"><label class="eos-label">Quality Promise</label><input name="quality_promise" value="{{ $settings['quality_promise'] ?? '' }}" class="eos-input" placeholder="Premium inspected products"></div>
            <div class="eos-field"><label class="eos-label">Support Promise</label><input name="support_promise" value="{{ $settings['support_promise'] ?? '' }}" class="eos-input" placeholder="WhatsApp support available"></div>
        </div>
    </div>

    <div x-show="activeTab === 'checkout'" x-cloak class="store-panel-clean storefront-panel">
        <div class="storefront-panel-title">Checkout Copy</div>
        <div class="storefront-panel-sub">Customer-facing labels used on checkout and WhatsApp order flow.</div>
        <div class="storefront-grid-2">
            <div class="eos-field"><label class="eos-label">WhatsApp Order Button Text</label><input name="whatsapp_order_text" value="{{ $settings['whatsapp_order_text'] ?? 'Order on WhatsApp' }}" class="eos-input" placeholder="Order on WhatsApp"></div>
            <div class="eos-field"><label class="eos-label">Checkout Button Text</label><input name="checkout_button_text" value="{{ $settings['checkout_button_text'] ?? 'Checkout' }}" class="eos-input" placeholder="Checkout"></div>
            <div class="eos-field" style="grid-column:1/-1;"><label class="eos-label">Checkout Note</label><textarea name="checkout_note" class="eos-input" rows="3" placeholder="Any customer-facing checkout note...">{{ $settings['checkout_note'] ?? '' }}</textarea></div>
        </div>
    </div>

    <div x-show="activeTab === 'policies'" x-cloak class="store-panel-clean storefront-panel">
        <div class="storefront-panel-title">Store Policies</div>
        <div class="storefront-panel-sub">Published as public ecommerce policy pages and linked from the storefront footer.</div>
        <div class="eos-field"><label class="eos-label">Privacy Policy</label><textarea name="privacy_policy" class="eos-input" rows="6" placeholder="Explain how customer data is collected, used, and protected...">{{ $settings['privacy_policy'] ?? '' }}</textarea></div>
        <div class="eos-field"><label class="eos-label">Terms & Conditions</label><textarea name="terms_conditions" class="eos-input" rows="6" placeholder="Store terms, order conditions, product information, and customer responsibilities...">{{ $settings['terms_conditions'] ?? '' }}</textarea></div>
        <div class="eos-field"><label class="eos-label">Refund Policy</label><textarea name="refund_policy" class="eos-input" rows="6" placeholder="Return eligibility, refund timelines, exchange process...">{{ $settings['refund_policy'] ?? '' }}</textarea></div>
        <div class="eos-field"><label class="eos-label">Shipping Policy</label><textarea name="shipping_policy" class="eos-input" rows="6" placeholder="Shipping timelines, delivery areas, charges, delays...">{{ $settings['shipping_policy'] ?? '' }}</textarea></div>
    </div>

    <div x-show="activeTab === 'social'" x-cloak class="store-panel-clean storefront-panel">
        <div class="storefront-panel-title">Social & Footer</div>
        <div class="storefront-panel-sub">Shown in the public shop footer.</div>
        <div class="storefront-grid-2">
            <div class="eos-field"><label class="eos-label">Instagram URL</label><input type="url" name="instagram_url" value="{{ $settings['instagram_url'] ?? '' }}" class="eos-input" placeholder="https://instagram.com/..."></div>
            <div class="eos-field"><label class="eos-label">Facebook URL</label><input type="url" name="facebook_url" value="{{ $settings['facebook_url'] ?? '' }}" class="eos-input" placeholder="https://facebook.com/..."></div>
            <div class="eos-field"><label class="eos-label">YouTube URL</label><input type="url" name="youtube_url" value="{{ $settings['youtube_url'] ?? '' }}" class="eos-input" placeholder="https://youtube.com/..."></div>
            <div class="eos-field"><label class="eos-label">Footer Tagline</label><input name="footer_tagline" value="{{ $settings['footer_tagline'] ?? '' }}" class="eos-input" placeholder="Premium designs for everyday moments"></div>
            <div class="eos-field" style="grid-column:1/-1;"><label class="eos-label">Footer About Text</label><textarea name="footer_about" class="eos-input" rows="3" placeholder="Brief store footer intro...">{{ $settings['footer_about'] ?? '' }}</textarea></div>
        </div>
    </div>

    <div x-show="activeTab === 'style'" x-cloak class="store-panel-clean storefront-panel">
        <div class="storefront-panel-title">Colors & Style</div>
        <div class="storefront-panel-sub">Brand accent color used across the public store and checkout.</div>
        <div class="eos-field">
            <label class="eos-label">Accent Color</label>
            <div style="display:flex;gap:8px;flex-wrap:wrap;">
                @foreach ($colors as $hex => $label)
                    <label style="display:flex;flex-direction:column;align-items:center;gap:4px;cursor:pointer;padding:8px 6px;border-radius:8px;border:2px solid {{ ($settings['accent_color'] ?? '#2563eb') === $hex ? 'var(--accent-blue)' : 'transparent' }};min-width:56px;">
                        <input type="radio" name="accent_color" value="{{ $hex }}" {{ ($settings['accent_color'] ?? '#2563eb') === $hex ? 'checked' : '' }} style="display:none;">
                        <span style="display:block;width:32px;height:32px;border-radius:50%;background:{{ $hex }};border:2px solid var(--border);"></span>
                        <span style="font-size:9px;color:var(--text-muted);">{{ $label }}</span>
                    </label>
                @endforeach
            </div>
        </div>
    </div>

    <div x-show="activeTab === 'premium'" x-cloak class="store-panel-clean storefront-panel">
        <div class="storefront-panel-title">Premium Store Effects</div>
        <div class="storefront-panel-sub">Advanced visual effects for premium/custom storefront themes.</div>
        @if ($tenant->hasModule('jem_preloader'))
            <label class="eos-field" style="display:flex;align-items:center;gap:12px;cursor:pointer;">
                <input type="hidden" name="jem_preloader_enabled" value="0">
                <input type="checkbox" name="jem_preloader_enabled" value="1" {{ ($settings['jem_preloader_enabled'] ?? '1') !== '0' ? 'checked' : '' }} style="width:18px;height:18px;accent-color:#2563eb;">
                <span>
                    <span style="display:block;font-size:13px;font-weight:900;color:var(--text-primary);">Enable Jem animated preloader</span>
                    <span style="display:block;font-size:11px;color:var(--text-muted);margin-top:3px;">Shows the luxury animated logo intro before the public store loads.</span>
                </span>
            </label>
        @else
            <div class="storefront-help" style="border-color:#fde68a;background:#fffbeb;color:#92400e;">
                Premium Store Preloader is a paid add-on. It is visible here for tracking, but the client can only enable it after admin activates the <strong>Premium Store Preloader</strong> feature for this tenant.
            </div>
        @endif
    </div>

    <div class="storefront-save-bar">
        <button type="submit" class="eos-btn eos-btn-primary" style="padding:12px 32px;font-size:14px;">
            <i class="ti ti-check"></i> Save Store Theme
        </button>
    </div>
</form>
@else
<form method="POST" action="{{ route('tenant.theme.update') }}" x-data="{ activeTab: 'hero' }" enctype="multipart/form-data">
    @csrf

    {{-- Tab Navigation --}}
    <div style="display:flex;gap:6px;margin-bottom:20px;flex-wrap:wrap;">
        @foreach ([
            'hero' => 'Hero & Banner',
            'about' => 'About School',
            'academics' => 'Academics',
            'admissions' => 'Admissions',
            'faculty' => 'Faculty',
            'student_life' => 'Student Life',
            'gallery' => 'Gallery & Media',
            'news' => 'News & Events',
            'achievements' => 'Achievements',
            'testimonials' => 'Testimonials',
            'downloads' => 'Downloads',
            'certificates' => 'Certificates',
            'contact' => 'Contact & Map',
            'social' => 'Social & Footer',
            'style' => 'Colors & Style',
        ] as $tabKey => $tabLabel)
            <button type="button" @click="activeTab = '{{ $tabKey }}'"
                style="padding:8px 14px;border-radius:8px;font-size:12px;font-weight:600;border:1px solid var(--border-card);cursor:pointer;transition:all .2s;"
                :style="activeTab === '{{ $tabKey }}' ? 'background:var(--accent-teal);color:#fff;border-color:var(--accent-teal);' : 'background:var(--bg-card);color:var(--text-secondary);'">
                {{ $tabLabel }}
            </button>
        @endforeach
    </div>

    {{-- ═══════ HERO & BANNER ═══════ --}}
    <div x-show="activeTab === 'hero'" x-cloak class="eos-card" style="padding:24px;">
        <div style="font-size:16px;font-weight:700;margin-bottom:16px;">Hero Banner & Admission</div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
            <div class="eos-field">
                <label class="eos-label">School Motto</label>
                <input type="text" name="school_motto" value="{{ $settings['school_motto'] ?? '' }}" class="eos-input" placeholder="e.g. Knowledge is Power">
            </div>
            <div class="eos-field">
                <label class="eos-label">Hero Tagline</label>
                <input type="text" name="hero_tagline" value="{{ $settings['hero_tagline'] ?? '' }}" class="eos-input" placeholder="e.g. Nurturing Minds, Shaping Futures">
            </div>
            <div class="eos-field">
                <label class="eos-label">Admission Year</label>
                <input type="text" name="admission_year" value="{{ $settings['admission_year'] ?? '2026-27' }}" class="eos-input" placeholder="2026-27">
            </div>
            <div class="eos-field">
                <label class="eos-label">Apply Now URL</label>
                <input type="url" name="admission_cta_url" value="{{ $settings['admission_cta_url'] ?? '' }}" class="eos-input" placeholder="https://...">
            </div>
            <div class="eos-field" style="grid-column:1/-1;">
                <label class="eos-label">Hero Image</label>
                @if ($settings['hero_image'] ?? null)
                    <div style="margin-bottom:8px;"><img src="{{ str_starts_with($settings['hero_image'], 'http') ? $settings['hero_image'] : Storage::url($settings['hero_image']) }}" alt="Current hero" style="width:180px;height:90px;border-radius:8px;object-fit:cover;border:1px solid var(--border-card);"></div>
                @endif
                <input type="file" name="hero_image_file" accept="image/*" class="eos-input" style="padding:8px;">
                <div style="margin-top:6px;color:var(--text-muted);font-size:11px;">Used by the assigned school theme. Upload a new image to replace the current one.</div>
            </div>
        </div>
    </div>

    {{-- ═══════ ABOUT SCHOOL ═══════ --}}
    <div x-show="activeTab === 'about'" x-cloak class="eos-card" style="padding:24px;">
        <div style="font-size:16px;font-weight:700;margin-bottom:16px;">About School</div>
        <div class="eos-field">
            <label class="eos-label">Section Title</label>
            <input type="text" name="about_title" value="{{ $settings['about_title'] ?? '' }}" class="eos-input" placeholder="About Our School">
        </div>
        <div class="eos-field">
            <label class="eos-label">About Text (shown on homepage)</label>
            <textarea name="about_text_raw" class="eos-input" rows="4" placeholder="School history, overview...">{{ $settings['about_text'] ?? $tenant->about_text ?? '' }}</textarea>
            <input type="hidden" name="about_text_target" value="tenant">
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;margin-top:16px;">
            <div class="eos-field">
                <label class="eos-label">Vision</label>
                <textarea name="vision" class="eos-input" rows="3" placeholder="Our vision is to...">{{ $settings['vision'] ?? '' }}</textarea>
            </div>
            <div class="eos-field">
                <label class="eos-label">Mission</label>
                <textarea name="mission" class="eos-input" rows="3" placeholder="Our mission is to...">{{ $settings['mission'] ?? '' }}</textarea>
            </div>
            <div class="eos-field">
                <label class="eos-label">Core Values</label>
                <textarea name="core_values" class="eos-input" rows="3" placeholder="Excellence, Integrity, Innovation...">{{ $settings['core_values'] ?? '' }}</textarea>
            </div>
        </div>
        <div style="margin-top:16px;">
            <div style="font-size:14px;font-weight:600;margin-bottom:8px;">Principal's Message</div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                <div class="eos-field">
                    <label class="eos-label">Principal Name</label>
                    <input type="text" name="principal_name" value="{{ $settings['principal_name'] ?? '' }}" class="eos-input" placeholder="Dr. Smith">
                </div>
                <div class="eos-field">
                    <label class="eos-label">Title</label>
                    <input type="text" name="principal_title" value="{{ $settings['principal_title'] ?? '' }}" class="eos-input" placeholder="Principal, M.Ed, Ph.D">
                </div>
            </div>
            <div class="eos-field">
                <label class="eos-label">Principal Photo</label>
                @if ($settings['principal_photo'] ?? null)
                    <div style="margin-bottom:8px;">
                        <img src="{{ Storage::url($settings['principal_photo']) }}" style="width:80px;height:80px;border-radius:50%;object-fit:cover;border:2px solid var(--border-card);">
                    </div>
                @endif
                <input type="file" name="principal_photo_file" accept="image/*" class="eos-input" style="padding:8px;">
            </div>
            <div class="eos-field">
                <label class="eos-label">Principal Message</label>
                <textarea name="principal_message" class="eos-input" rows="4" placeholder="Dear parents and students...">{{ $settings['principal_message'] ?? '' }}</textarea>
            </div>
        </div>
        <div class="eos-field" style="margin-top:16px;">
            <label class="eos-label">About Section Image</label>
            @if ($settings['about_image'] ?? null)
                <div style="margin-bottom:8px;"><img src="{{ str_starts_with($settings['about_image'], 'http') ? $settings['about_image'] : Storage::url($settings['about_image']) }}" alt="Current about image" style="width:180px;height:100px;border-radius:8px;object-fit:cover;border:1px solid var(--border-card);"></div>
            @endif
            <input type="file" name="about_image_file" accept="image/*" class="eos-input" style="padding:8px;">
        </div>
    </div>

    {{-- ═══════ ACADEMICS ═══════ --}}
    <div x-show="activeTab === 'academics'" x-cloak class="eos-card" style="padding:24px;">
        <div style="font-size:16px;font-weight:700;margin-bottom:16px;">Academics</div>
        <div class="eos-field">
            <label class="eos-label">Section Title</label>
            <input type="text" name="academics_title" value="{{ $settings['academics_title'] ?? '' }}" class="eos-input" placeholder="Academic Excellence">
        </div>
        <div class="eos-field">
            <label class="eos-label">School Timings</label>
            <input type="text" name="school_timings" value="{{ $settings['school_timings'] ?? '' }}" class="eos-input" placeholder="8:00 AM - 2:30 PM">
        </div>
        @for ($i = 1; $i <= 4; $i++)
            <div style="display:grid;grid-template-columns:1fr 1fr 2fr;gap:12px;margin-top:12px;padding:12px;background:var(--bg-secondary);border-radius:8px;">
                <div class="eos-field" style="margin:0;">
                    <label class="eos-label">Item {{ $i }} Icon</label>
                    <input type="text" name="academics_{{ $i }}_icon" value="{{ $settings["academics_{$i}_icon"] ?? '' }}" class="eos-input" placeholder="ti-book">
                </div>
                <div class="eos-field" style="margin:0;">
                    <label class="eos-label">Item {{ $i }} Title</label>
                    <input type="text" name="academics_{{ $i }}_title" value="{{ $settings["academics_{$i}_title"] ?? '' }}" class="eos-input" placeholder="Curriculum">
                </div>
                <div class="eos-field" style="margin:0;">
                    <label class="eos-label">Item {{ $i }} Description</label>
                    <input type="text" name="academics_{{ $i }}_desc" value="{{ $settings["academics_{$i}_desc"] ?? '' }}" class="eos-input" placeholder="Details about this topic...">
                </div>
            </div>
        @endfor
        <div class="eos-field" style="margin-top:16px;">
            <label class="eos-label">Learning Approach Image</label>
            @if ($settings['learning_image'] ?? null)
                <div style="margin-bottom:8px;"><img src="{{ str_starts_with($settings['learning_image'], 'http') ? $settings['learning_image'] : Storage::url($settings['learning_image']) }}" alt="Current learning image" style="width:180px;height:100px;border-radius:8px;object-fit:cover;border:1px solid var(--border-card);"></div>
            @endif
            <input type="file" name="learning_image_file" accept="image/*" class="eos-input" style="padding:8px;">
        </div>
    </div>

    {{-- ═══════ ADMISSIONS ═══════ --}}
    <div x-show="activeTab === 'admissions'" x-cloak class="eos-card" style="padding:24px;">
        <div style="font-size:16px;font-weight:700;margin-bottom:16px;">Admissions</div>
        <div class="eos-field">
            <label class="eos-label">Section Title</label>
            <input type="text" name="admissions_title" value="{{ $settings['admissions_title'] ?? '' }}" class="eos-input" placeholder="Admissions Open 2026-27">
        </div>
        <div class="eos-field">
            <label class="eos-label">Admission Process</label>
            <textarea name="admission_process" class="eos-input" rows="4" placeholder="Step 1: Fill online form...">{{ $settings['admission_process'] ?? '' }}</textarea>
        </div>
        <div class="eos-field">
            <label class="eos-label">Eligibility</label>
            <textarea name="eligibility" class="eos-input" rows="3" placeholder="Age criteria, class requirements...">{{ $settings['eligibility'] ?? '' }}</textarea>
        </div>
        <div class="eos-field">
            <label class="eos-label">Fee Structure</label>
            <textarea name="fee_structure" class="eos-input" rows="4" placeholder="Class 1: ₹15,000/year...">{{ $settings['fee_structure'] ?? '' }}</textarea>
        </div>
        <div class="eos-field">
            <label class="eos-label">Required Documents</label>
            <textarea name="required_documents" class="eos-input" rows="3" placeholder="Birth certificate, Aadhar card...">{{ $settings['required_documents'] ?? '' }}</textarea>
        </div>
        <div class="eos-field">
            <label class="eos-label">Admission Schedule</label>
            <textarea name="admission_schedule" class="eos-input" rows="2" placeholder="Registration opens: 1st April...">{{ $settings['admission_schedule'] ?? '' }}</textarea>
        </div>
        <div class="eos-field">
            <label class="eos-label">FAQs</label>
            <textarea name="admission_faq" class="eos-input" rows="4" placeholder="Q: What is the admission age?&#10;A: 3+ for Nursery...">{{ $settings['admission_faq'] ?? '' }}</textarea>
        </div>
        <div class="eos-field">
            <label class="eos-label">Enquiry Form URL (external)</label>
            <input type="url" name="admission_enquiry_url" value="{{ $settings['admission_enquiry_url'] ?? '' }}" class="eos-input" placeholder="https://...">
        </div>
    </div>

    {{-- ═══════ FACULTY ═══════ --}}
    <div x-show="activeTab === 'faculty'" x-cloak class="eos-card" style="padding:24px;">
        <div style="font-size:16px;font-weight:700;margin-bottom:16px;">Faculty & Staff</div>
        <div class="eos-field">
            <label class="eos-label">Section Title</label>
            <input type="text" name="faculty_title" value="{{ $settings['faculty_title'] ?? '' }}" class="eos-input" placeholder="Our Team">
        </div>
        @for ($i = 1; $i <= 8; $i++)
            <div style="display:grid;grid-template-columns:60px 1fr 1fr 1fr;gap:12px;margin-top:12px;padding:12px;background:var(--bg-secondary);border-radius:8px;align-items:start;">
                <div class="eos-field" style="margin:0;">
                    <label class="eos-label">Photo</label>
                    @if ($settings["faculty_{$i}_photo"] ?? null)
                        <img src="{{ Storage::url($settings["faculty_{$i}_photo"]) }}" style="width:48px;height:48px;border-radius:50%;object-fit:cover;border:2px solid var(--border-card);">
                    @else
                        <div style="width:48px;height:48px;border-radius:50%;background:var(--bg-card);border:2px dashed var(--border-card);display:flex;align-items:center;justify-content:center;">
                            <i class="ti ti-camera" style="font-size:16px;color:var(--text-dim);"></i>
                        </div>
                    @endif
                    <input type="file" name="faculty_{{ $i }}_photo_file" accept="image/*" class="eos-input" style="padding:4px;font-size:11px;margin-top:4px;">
                </div>
                <div class="eos-field" style="margin:0;">
                    <label class="eos-label">Faculty {{ $i }} Name</label>
                    <input type="text" name="faculty_{{ $i }}_name" value="{{ $settings["faculty_{$i}_name"] ?? '' }}" class="eos-input" placeholder="Full name">
                </div>
                <div class="eos-field" style="margin:0;">
                    <label class="eos-label">Role</label>
                    <input type="text" name="faculty_{{ $i }}_role" value="{{ $settings["faculty_{$i}_role"] ?? '' }}" class="eos-input" placeholder="Principal, Teacher...">
                </div>
                <div class="eos-field" style="margin:0;">
                    <label class="eos-label">Qualification</label>
                    <input type="text" name="faculty_{{ $i }}_qualification" value="{{ $settings["faculty_{$i}_qualification"] ?? '' }}" class="eos-input" placeholder="M.Ed, B.Sc...">
                </div>
            </div>
        @endfor
    </div>

    {{-- ═══════ STUDENT LIFE ═══════ --}}
    <div x-show="activeTab === 'student_life'" x-cloak class="eos-card" style="padding:24px;">
        <div style="font-size:16px;font-weight:700;margin-bottom:16px;">Student Life & Facilities</div>
        <div class="eos-field">
            <label class="eos-label">Section Title</label>
            <input type="text" name="student_life_title" value="{{ $settings['student_life_title'] ?? '' }}" class="eos-input" placeholder="Life Beyond Classrooms">
        </div>
        @for ($i = 1; $i <= 6; $i++)
            <div style="display:grid;grid-template-columns:1fr 1fr 2fr;gap:12px;margin-top:12px;padding:12px;background:var(--bg-secondary);border-radius:8px;">
                <div class="eos-field" style="margin:0;">
                    <label class="eos-label">Activity {{ $i }} Icon</label>
                    <input type="text" name="activity_{{ $i }}_icon" value="{{ $settings["activity_{$i}_icon"] ?? '' }}" class="eos-input" placeholder="ti-star">
                </div>
                <div class="eos-field" style="margin:0;">
                    <label class="eos-label">Activity {{ $i }} Title</label>
                    <input type="text" name="activity_{{ $i }}_title" value="{{ $settings["activity_{$i}_title"] ?? '' }}" class="eos-input" placeholder="Sports, Clubs...">
                </div>
                <div class="eos-field" style="margin:0;">
                    <label class="eos-label">Description</label>
                    <input type="text" name="activity_{{ $i }}_desc" value="{{ $settings["activity_{$i}_desc"] ?? '' }}" class="eos-input" placeholder="Details...">
                </div>
            </div>
        @endfor
        <div style="margin-top:20px;">
            <div style="font-size:14px;font-weight:600;margin-bottom:8px;">Facilities</div>
            <div class="eos-field">
                <label class="eos-label">Facilities Title</label>
                <input type="text" name="facilities_title" value="{{ $settings['facilities_title'] ?? '' }}" class="eos-input" placeholder="Our Facilities">
            </div>
            @for ($i = 1; $i <= 8; $i++)
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-top:8px;">
                    <div class="eos-field" style="margin:0;">
                        <label class="eos-label">Facility {{ $i }} Icon</label>
                        <input type="text" name="facility_{{ $i }}_icon" value="{{ $settings["facility_{$i}_icon"] ?? '' }}" class="eos-input" placeholder="ti-building">
                    </div>
                    <div class="eos-field" style="margin:0;">
                        <label class="eos-label">Facility {{ $i }} Name</label>
                        <input type="text" name="facility_{{ $i }}_name" value="{{ $settings["facility_{$i}_name"] ?? '' }}" class="eos-input" placeholder="Library, Lab...">
                    </div>
                </div>
            @endfor
        </div>
    </div>

    {{-- ═══════ GALLERY & MEDIA ═══════ --}}
    <div x-show="activeTab === 'gallery'" x-cloak class="eos-card" style="padding:24px;">
        <div style="font-size:16px;font-weight:700;margin-bottom:16px;">Gallery & Media</div>
        <div class="eos-field">
            <label class="eos-label">Gallery Section Title</label>
            <input type="text" name="gallery_title" value="{{ $settings['gallery_title'] ?? '' }}" class="eos-input" placeholder="Campus Gallery">
        </div>
        <div style="margin-top:12px;padding:16px;background:var(--bg-secondary);border-radius:8px;">
            <div style="font-size:13px;color:var(--text-secondary);line-height:1.6;">
                <i class="ti ti-info-circle" style="color:var(--accent-teal);"></i>
                Upload gallery images from the <strong>Tenant Dashboard → Gallery</strong> section. Images will appear automatically in the Gallery section of your website.
            </div>
        </div>
    </div>

    {{-- ═══════ NEWS & EVENTS ═══════ --}}
    <div x-show="activeTab === 'news'" x-cloak class="eos-card" style="padding:24px;">
        <div style="font-size:16px;font-weight:700;margin-bottom:16px;">News & Events</div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
            <div class="eos-field">
                <label class="eos-label">News Title</label>
                <input type="text" name="news_title" value="{{ $settings['news_title'] ?? '' }}" class="eos-input" placeholder="News & Notices">
            </div>
            <div class="eos-field">
                <label class="eos-label">Events Title</label>
                <input type="text" name="events_title" value="{{ $settings['events_title'] ?? '' }}" class="eos-input" placeholder="Upcoming Events">
            </div>
        </div>
        @for ($i = 1; $i <= 3; $i++)
            <div style="margin-top:12px;padding:12px;background:var(--bg-secondary);border-radius:8px;">
                <div style="font-size:12px;font-weight:600;color:var(--text-muted);margin-bottom:8px;">NEWS {{ $i }}</div>
                <div style="display:grid;grid-template-columns:1fr 1fr 2fr;gap:12px;">
                    <div class="eos-field" style="margin:0;">
                        <label class="eos-label">Date</label>
                        <input type="text" name="news_{{ $i }}_date" value="{{ $settings["news_{$i}_date"] ?? '' }}" class="eos-input" placeholder="15 Jul 2026">
                    </div>
                    <div class="eos-field" style="margin:0;">
                        <label class="eos-label">Title</label>
                        <input type="text" name="news_{{ $i }}_title" value="{{ $settings["news_{$i}_title"] ?? '' }}" class="eos-input" placeholder="News headline">
                    </div>
                    <div class="eos-field" style="margin:0;">
                        <label class="eos-label">Excerpt</label>
                        <input type="text" name="news_{{ $i }}_excerpt" value="{{ $settings["news_{$i}_excerpt"] ?? '' }}" class="eos-input" placeholder="Short description...">
                    </div>
                </div>
            </div>
        @endfor
        @for ($i = 1; $i <= 3; $i++)
            <div style="margin-top:12px;padding:12px;background:var(--bg-secondary);border-radius:8px;">
                <div style="font-size:12px;font-weight:600;color:var(--text-muted);margin-bottom:8px;">EVENT {{ $i }}</div>
                <div style="display:grid;grid-template-columns:60px 80px 1fr 2fr;gap:12px;">
                    <div class="eos-field" style="margin:0;">
                        <label class="eos-label">Day</label>
                        <input type="text" name="event_{{ $i }}_day" value="{{ $settings["event_{$i}_day"] ?? '' }}" class="eos-input" placeholder="25">
                    </div>
                    <div class="eos-field" style="margin:0;">
                        <label class="eos-label">Month</label>
                        <input type="text" name="event_{{ $i }}_month" value="{{ $settings["event_{$i}_month"] ?? '' }}" class="eos-input" placeholder="Aug">
                    </div>
                    <div class="eos-field" style="margin:0;">
                        <label class="eos-label">Title</label>
                        <input type="text" name="event_{{ $i }}_title" value="{{ $settings["event_{$i}_title"] ?? '' }}" class="eos-input" placeholder="Annual Day">
                    </div>
                    <div class="eos-field" style="margin:0;">
                        <label class="eos-label">Description</label>
                        <input type="text" name="event_{{ $i }}_desc" value="{{ $settings["event_{$i}_desc"] ?? '' }}" class="eos-input" placeholder="Details...">
                    </div>
                </div>
            </div>
        @endfor
    </div>

    {{-- ═══════ ACHIEVEMENTS ═══════ --}}
    <div x-show="activeTab === 'achievements'" x-cloak class="eos-card" style="padding:24px;">
        <div style="font-size:16px;font-weight:700;margin-bottom:16px;">Achievements</div>
        <div class="eos-field">
            <label class="eos-label">Section Title</label>
            <input type="text" name="achievements_title" value="{{ $settings['achievements_title'] ?? '' }}" class="eos-input" placeholder="Our Achievements">
        </div>
        @for ($i = 1; $i <= 6; $i++)
            <div style="display:grid;grid-template-columns:1fr 2fr;gap:12px;margin-top:12px;padding:12px;background:var(--bg-secondary);border-radius:8px;">
                <div class="eos-field" style="margin:0;">
                    <label class="eos-label">Achievement {{ $i }} Title</label>
                    <input type="text" name="achievement_{{ $i }}_title" value="{{ $settings["achievement_{$i}_title"] ?? '' }}" class="eos-input" placeholder="Winner of State Science Fair">
                </div>
                <div class="eos-field" style="margin:0;">
                    <label class="eos-label">Description</label>
                    <input type="text" name="achievement_{{ $i }}_desc" value="{{ $settings["achievement_{$i}_desc"] ?? '' }}" class="eos-input" placeholder="Details...">
                </div>
            </div>
        @endfor
    </div>

    {{-- ═══════ TESTIMONIALS ═══════ --}}
    <div x-show="activeTab === 'testimonials'" x-cloak class="eos-card" style="padding:24px;">
        <div style="font-size:16px;font-weight:700;margin-bottom:16px;">Testimonials</div>
        <div class="eos-field">
            <label class="eos-label">Section Title</label>
            <input type="text" name="testimonials_title" value="{{ $settings['testimonials_title'] ?? '' }}" class="eos-input" placeholder="What Parents Say">
        </div>
        @for ($i = 1; $i <= 3; $i++)
            <div style="margin-top:12px;padding:12px;background:var(--bg-secondary);border-radius:8px;">
                <div style="font-size:12px;font-weight:600;color:var(--text-muted);margin-bottom:8px;">TESTIMONIAL {{ $i }}</div>
                <div style="display:grid;grid-template-columns:1fr 1fr 2fr;gap:12px;">
                    <div class="eos-field" style="margin:0;">
                        <label class="eos-label">Name</label>
                        <input type="text" name="testimonial_{{ $i }}_name" value="{{ $settings["testimonial_{$i}_name"] ?? '' }}" class="eos-input" placeholder="Parent name">
                    </div>
                    <div class="eos-field" style="margin:0;">
                        <label class="eos-label">Role</label>
                        <input type="text" name="testimonial_{{ $i }}_role" value="{{ $settings["testimonial_{$i}_role"] ?? '' }}" class="eos-input" placeholder="Parent of Class 5">
                    </div>
                    <div class="eos-field" style="margin:0;">
                        <label class="eos-label">Quote</label>
                        <input type="text" name="testimonial_{{ $i }}_quote" value="{{ $settings["testimonial_{$i}_quote"] ?? '' }}" class="eos-input" placeholder="This school has been amazing...">
                    </div>
                </div>
            </div>
        @endfor
    </div>

    {{-- ═══════ DOWNLOADS ═══════ --}}
    <div x-show="activeTab === 'downloads'" x-cloak class="eos-card" style="padding:24px;">
        <div style="font-size:16px;font-weight:700;margin-bottom:16px;">Downloads</div>
        <div class="eos-field">
            <label class="eos-label">Section Title</label>
            <input type="text" name="downloads_title" value="{{ $settings['downloads_title'] ?? '' }}" class="eos-input" placeholder="Important Downloads">
        </div>
        @for ($i = 1; $i <= 5; $i++)
            <div style="display:grid;grid-template-columns:1fr 2fr auto;gap:12px;margin-top:12px;padding:12px;background:var(--bg-secondary);border-radius:8px;align-items:end;">
                <div class="eos-field" style="margin:0;">
                    <label class="eos-label">Download {{ $i }} Name</label>
                    <input type="text" name="download_{{ $i }}_name" value="{{ $settings["download_{$i}_name"] ?? '' }}" class="eos-input" placeholder="Admission Form">
                </div>
                <div class="eos-field" style="margin:0;">
                    <label class="eos-label">File Upload or URL</label>
                    @if ($settings["download_{$i}_file"] ?? null)
                        <div style="font-size:12px;color:var(--accent-teal);margin-bottom:4px;">
                            <i class="ti ti-file-check"></i> {{ basename($settings["download_{$i}_file"]) }}
                        </div>
                    @endif
                    <input type="file" name="download_{{ $i }}_file" class="eos-input" style="padding:6px;font-size:12px;">
                    <input type="text" name="download_{{ $i }}_url" value="{{ $settings["download_{$i}_url"] ?? '' }}" class="eos-input" placeholder="Or paste URL: https://..." style="margin-top:6px;">
                </div>
                <div style="font-size:11px;color:var(--text-muted);padding-bottom:8px;">Upload file OR paste URL</div>
            </div>
        @endfor
    </div>

    {{-- ═══════ CERTIFICATES ═══════ --}}
    <div x-show="activeTab === 'certificates'" x-cloak class="eos-card" style="padding:24px;">
        <div style="font-size:16px;font-weight:700;margin-bottom:16px;">Certificates & Recognition</div>
        <div class="eos-field">
            <label class="eos-label">Section Title</label>
            <input type="text" name="certificates_title" value="{{ $settings['certificates_title'] ?? '' }}" class="eos-input" placeholder="Certificates & Recognition">
        </div>
        @for ($i = 1; $i <= 5; $i++)
            <div class="eos-field" style="margin-top:12px;">
                <label class="eos-label">Certificate {{ $i }}</label>
                <input type="text" name="cert_{{ $i }}_name" value="{{ $settings["cert_{$i}_name"] ?? '' }}" class="eos-input" placeholder="e.g. CBSE Affiliation Certificate">
            </div>
        @endfor
    </div>

    {{-- ═══════ CONTACT & MAP ═══════ --}}
    <div x-show="activeTab === 'contact'" x-cloak class="eos-card" style="padding:24px;">
        <div style="font-size:16px;font-weight:700;margin-bottom:16px;">Contact & Google Map</div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
            <div class="eos-field">
                <label class="eos-label">Contact Section Title</label>
                <input type="text" name="contact_title" value="{{ $settings['contact_title'] ?? '' }}" class="eos-input" placeholder="Get in Touch">
            </div>
            <div class="eos-field">
                <label class="eos-label">Office Hours</label>
                <input type="text" name="office_hours" value="{{ $settings['office_hours'] ?? '' }}" class="eos-input" placeholder="Mon-Sat: 8AM - 4PM">
            </div>
            <div class="eos-field">
                <label class="eos-label">School Code</label>
                <input type="text" name="school_code" value="{{ $settings['school_code'] ?? '' }}" class="eos-input" placeholder="SCH-12345">
            </div>
            <div class="eos-field">
                <label class="eos-label">Affiliation Number</label>
                <input type="text" name="affiliation_number" value="{{ $settings['affiliation_number'] ?? '' }}" class="eos-input" placeholder="CBSE-123456">
            </div>
        </div>
        <div class="eos-field" style="margin-top:16px;">
            <label class="eos-label">Google Map Embed Code</label>
            <textarea name="google_map_embed" class="eos-input" rows="3" placeholder='<iframe src="https://www.google.com/maps/embed?...'></textarea>
            <div style="font-size:11px;color:var(--text-muted);margin-top:4px;">Go to Google Maps → Share → Embed a map → Copy the iframe code</div>
        </div>
        <div class="eos-field">
            <label class="eos-label">WhatsApp Number (with country code)</label>
            <input type="text" name="whatsapp_number" value="{{ $settings['whatsapp_number'] ?? '' }}" class="eos-input" placeholder="919876543210">
        </div>
        <div style="margin-top:16px;padding:12px;background:var(--bg-secondary);border-radius:8px;">
            <div style="font-size:13px;color:var(--text-secondary);line-height:1.6;">
                <i class="ti ti-info-circle" style="color:var(--accent-teal);"></i>
                Address, phone, and email are taken from the <strong>Tenant Settings → Contact Info</strong> section.
            </div>
        </div>
    </div>

    {{-- ═══════ SOCIAL & FOOTER ═══════ --}}
    <div x-show="activeTab === 'social'" x-cloak class="eos-card" style="padding:24px;">
        <div style="font-size:16px;font-weight:700;margin-bottom:16px;">Social Media & Footer</div>
        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;">
            <div class="eos-field">
                <label class="eos-label">Facebook URL</label>
                <input type="url" name="facebook_url" value="{{ $settings['facebook_url'] ?? '' }}" class="eos-input" placeholder="https://facebook.com/...">
            </div>
            <div class="eos-field">
                <label class="eos-label">Instagram URL</label>
                <input type="url" name="instagram_url" value="{{ $settings['instagram_url'] ?? '' }}" class="eos-input" placeholder="https://instagram.com/...">
            </div>
            <div class="eos-field">
                <label class="eos-label">YouTube URL</label>
                <input type="url" name="youtube_url" value="{{ $settings['youtube_url'] ?? '' }}" class="eos-input" placeholder="https://youtube.com/...">
            </div>
        </div>
        <div class="eos-field" style="margin-top:16px;">
            <label class="eos-label">Footer About Text</label>
            <textarea name="footer_about" class="eos-input" rows="3" placeholder="Brief about the school for footer...">{{ $settings['footer_about'] ?? '' }}</textarea>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-top:16px;">
            <div class="eos-field">
                <label class="eos-label">Important Link 1 Name</label>
                <input type="text" name="important_link_1_name" value="{{ $settings['important_link_1_name'] ?? '' }}" class="eos-input" placeholder="School Blog">
            </div>
            <div class="eos-field">
                <label class="eos-label">Important Link 1 URL</label>
                <input type="url" name="important_link_1_url" value="{{ $settings['important_link_1_url'] ?? '' }}" class="eos-input" placeholder="https://...">
            </div>
            <div class="eos-field">
                <label class="eos-label">Important Link 2 Name</label>
                <input type="text" name="important_link_2_name" value="{{ $settings['important_link_2_name'] ?? '' }}" class="eos-input" placeholder="Alumni Portal">
            </div>
            <div class="eos-field">
                <label class="eos-label">Important Link 2 URL</label>
                <input type="url" name="important_link_2_url" value="{{ $settings['important_link_2_url'] ?? '' }}" class="eos-input" placeholder="https://...">
            </div>
            <div class="eos-field">
                <label class="eos-label">Important Link 3 Name</label>
                <input type="text" name="important_link_3_name" value="{{ $settings['important_link_3_name'] ?? '' }}" class="eos-input" placeholder="Careers">
            </div>
            <div class="eos-field">
                <label class="eos-label">Important Link 3 URL</label>
                <input type="url" name="important_link_3_url" value="{{ $settings['important_link_3_url'] ?? '' }}" class="eos-input" placeholder="https://...">
            </div>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-top:16px;">
            <div class="eos-field">
                <label class="eos-label">Privacy Policy URL</label>
                <input type="url" name="privacy_policy_url" value="{{ $settings['privacy_policy_url'] ?? '' }}" class="eos-input" placeholder="https://...">
            </div>
            <div class="eos-field">
                <label class="eos-label">Terms & Conditions URL</label>
                <input type="url" name="terms_url" value="{{ $settings['terms_url'] ?? '' }}" class="eos-input" placeholder="https://...">
            </div>
        </div>
    </div>

    {{-- ═══════ COLORS & STYLE ═══════ --}}
    <div x-show="activeTab === 'style'" x-cloak class="eos-card" style="padding:24px;">
        <div style="font-size:16px;font-weight:700;margin-bottom:16px;">Colors & Style</div>
        <div class="eos-field">
            <label class="eos-label">Accent Color</label>
            <div style="display:flex;gap:8px;flex-wrap:wrap;">
                @foreach ($colors as $hex => $label)
                    <label style="display:flex;flex-direction:column;align-items:center;gap:4px;cursor:pointer;padding:8px 6px;border-radius:8px;border:2px solid {{ ($settings['accent_color'] ?? '#1e40af') === $hex ? 'var(--accent-teal)' : 'transparent' }};min-width:56px;">
                        <input type="radio" name="accent_color" value="{{ $hex }}"
                               {{ ($settings['accent_color'] ?? '#1e40af') === $hex ? 'checked' : '' }}
                               style="display:none;">
                        <span style="display:block;width:32px;height:32px;border-radius:50%;background:{{ $hex }};border:2px solid var(--border);"></span>
                        <span style="font-size:9px;color:var(--text-muted);">{{ $label }}</span>
                    </label>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Save Button --}}
    <div style="margin-top:20px;text-align:right;">
        <button type="submit" class="eos-btn eos-btn-primary" style="padding:12px 32px;font-size:14px;">
            <i class="ti ti-check"></i> Save All Settings
        </button>
    </div>
</form>

@endif
@endsection
