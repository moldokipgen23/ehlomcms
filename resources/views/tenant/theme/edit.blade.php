@extends('tenant.layouts.dashboard')

@section('title', 'Customise Theme')
@section('subtitle', 'Edit your school website content')

@section('content')

<form method="POST" action="{{ route('tenant.theme.update') }}" x-data="{ activeTab: 'hero' }">
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
                <label class="eos-label">Principal Message</label>
                <textarea name="principal_message" class="eos-input" rows="4" placeholder="Dear parents and students...">{{ $settings['principal_message'] ?? '' }}</textarea>
            </div>
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
            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;margin-top:12px;padding:12px;background:var(--bg-secondary);border-radius:8px;">
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
            <div style="display:grid;grid-template-columns:1fr 2fr;gap:12px;margin-top:12px;padding:12px;background:var(--bg-secondary);border-radius:8px;">
                <div class="eos-field" style="margin:0;">
                    <label class="eos-label">Download {{ $i }} Name</label>
                    <input type="text" name="download_{{ $i }}_name" value="{{ $settings["download_{$i}_name"] ?? '' }}" class="eos-input" placeholder="Admission Form">
                </div>
                <div class="eos-field" style="margin:0;">
                    <label class="eos-label">Download URL</label>
                    <input type="url" name="download_{{ $i }}_url" value="{{ $settings["download_{$i}_url"] ?? '' }}" class="eos-input" placeholder="https://... file link">
                </div>
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

@endsection
