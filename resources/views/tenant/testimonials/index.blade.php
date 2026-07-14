@extends('tenant.layouts.dashboard')

@section('title', 'Testimonials')

@section('topbar-action')
    <a href="{{ route('tenant.testimonials.create') }}" class="eos-btn eos-btn-primary" style="text-decoration:none;">
        <i class="ti ti-plus"></i> Add Testimonial
    </a>
@endsection

@section('content')
<div class="eos-row">
    <div class="eos-card" style="flex:1;">
        <div class="eos-card-header">
            <div class="eos-card-title">Your Testimonials</div>
            <span class="eos-card-link">{{ $testimonials->count() }} items</span>
        </div>

        @forelse ($testimonials as $t)
            <div class="eos-list-item">
                <div style="width:48px;height:48px;border-radius:50%;overflow:hidden;flex-shrink:0;background:var(--bg-hover);">
                    @if ($t->photo)
                        <img src="{{ Storage::url($t->photo) }}" alt="{{ $t->author_name }}" style="width:100%;height:100%;object-fit:cover;">
                    @else
                        <div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;color:var(--text-dim);"><i class="ti ti-user"></i></div>
                    @endif
                </div>
                <div style="flex:1;min-width:0;padding:0 12px;">
                    <div class="eos-row-name">
                        {{ $t->author_name }}
                        @if ($t->rating)
                            <span style="color:var(--accent-amber);font-size:11px;">{{ str_repeat('★', $t->rating) }}{{ str_repeat('☆', 5 - $t->rating) }}</span>
                        @endif
                    </div>
                    <div class="eos-row-type">
                        {{ $t->author_role }}
                        {{ $t->author_role ? '— ' : '' }}{{ Str::limit($t->content, 70) }}
                    </div>
                </div>
                <div style="text-align:right;">
                    <div style="display:flex;gap:6px;justify-content:flex-end;">
                        <a href="{{ route('tenant.testimonials.edit', $t->id) }}" class="eos-logout" title="Edit"><i class="ti ti-pencil"></i></a>
                        <form method="POST" action="{{ route('tenant.testimonials.destroy', $t->id) }}" onsubmit="return confirm('Delete this testimonial?');">
                            @csrf @method('DELETE')
                            <button type="submit" class="eos-logout" title="Delete"><i class="ti ti-trash"></i></button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="eos-empty" style="padding:32px 16px;">
                No testimonials yet. <a href="{{ route('tenant.testimonials.create') }}" style="color:var(--accent-blue);">Add your first testimonial</a>.
            </div>
        @endforelse
    </div>
</div>
@endsection
