@extends('tenant.layouts.dashboard')

@section('title', 'Dashboard')

@section('content')
    <div class="eos-stat-grid">
        <div class="eos-stat s-blue">
            <div class="eos-stat-top">
                <div class="eos-stat-label">Site Type</div>
                <div class="eos-stat-icon blue"><i class="ti ti-world"></i></div>
            </div>
            <div class="eos-stat-num" style="text-transform:capitalize;">{{ $tenant->site_type }}</div>
            <div class="eos-stat-meta">{{ $tenant->template_id ?? 'No template selected' }}</div>
        </div>
        <div class="eos-stat s-purple">
            <div class="eos-stat-top">
                <div class="eos-stat-label">Status</div>
                <div class="eos-stat-icon purple"><i class="ti ti-status"></i></div>
            </div>
            <div class="eos-stat-num" style="text-transform:capitalize;">{{ $tenant->status }}</div>
            <div class="eos-stat-meta">Account status</div>
        </div>
        <div class="eos-stat s-amber">
            <div class="eos-stat-top">
                <div class="eos-stat-label">Gallery</div>
                <div class="eos-stat-icon amber"><i class="ti ti-photo"></i></div>
            </div>
            <div class="eos-stat-num">{{ $galleryCount }}</div>
            <div class="eos-stat-meta">images uploaded</div>
        </div>
        <div class="eos-stat s-teal">
            <div class="eos-stat-top">
                <div class="eos-stat-label">Plan</div>
                <div class="eos-stat-icon teal"><i class="ti ti-credit-card"></i></div>
            </div>
            <div class="eos-stat-num">{{ $tenant->plan ?? '—' }}</div>
            <div class="eos-stat-meta">current plan</div>
        </div>
    </div>

    <div class="eos-row">
        <div class="eos-card" style="flex:1;">
            <div class="eos-card-header">
                <div class="eos-card-title">Quick Links</div>
            </div>
            <a href="{{ route('tenant.settings') }}" class="eos-list-item" style="text-decoration:none;">
                <div class="eos-init"><i class="ti ti-settings"></i></div>
                <div style="flex:1;min-width:0;">
                    <div class="eos-row-name">Settings</div>
                    <div class="eos-row-type">Manage your business info, logo, and banner</div>
                </div>
            </a>
            <a href="{{ route('tenant.content') }}" class="eos-list-item" style="text-decoration:none;">
                <div class="eos-init"><i class="ti ti-file-text"></i></div>
                <div style="flex:1;min-width:0;">
                    <div class="eos-row-name">Content / Pages</div>
                    <div class="eos-row-type">Edit about text, gallery images, contact info</div>
                </div>
            </a>
        </div>
    </div>
@endsection
