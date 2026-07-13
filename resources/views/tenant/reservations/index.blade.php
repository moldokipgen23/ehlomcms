@extends('tenant.layouts.dashboard')

@section('title', 'Reservations')

@section('content')
<div class="eos-row">
    <div class="eos-card" style="flex:1;">
        <div class="eos-card-header">
            <div class="eos-card-title">Reservations</div>
            <span class="eos-card-link">{{ $reservations->count() }} total</span>
        </div>

        @forelse ($reservations as $reservation)
            <div class="eos-list-item">
                <div class="eos-init" style="background:var(--bg-hover);">
                    <i class="ti {{ $reservation->status === 'confirmed' ? 'ti-circle-check' : ($reservation->status === 'cancelled' ? 'ti-circle-x' : 'ti-clock') }}" style="color:{{ $reservation->status === 'confirmed' ? 'var(--accent-green)' : ($reservation->status === 'cancelled' ? 'var(--accent-red)' : 'var(--accent-amber)') }};"></i>
                </div>
                <div style="flex:1;min-width:0;">
                    <div class="eos-row-name">{{ $reservation->customer_name }}</div>
                    <div class="eos-row-type">
                        {{ $reservation->party_size }} {{ Str::plural('guest', $reservation->party_size) }}
                        &middot; {{ $reservation->date->format('M j, Y') }} at {{ \Illuminate\Support\Carbon::parse($reservation->time)->format('g:i A') }}
                        &middot; {{ $reservation->phone }}
                        @if ($reservation->notes)
                            &middot; {{ $reservation->notes }}
                        @endif
                    </div>
                </div>
                <div style="text-align:right;">
                    <form action="{{ route('tenant.reservations.update-status', $reservation) }}" method="POST" style="display:inline;">
                        @csrf
                        <select name="status" onchange="this.form.submit()" class="eos-select" style="font-size:11px;padding:3px 6px;border-radius:5px;border:1px solid var(--border);background:var(--bg-card);color:var(--text-primary);cursor:pointer;">
                            @foreach ($statuses as $s)
                                <option value="{{ $s }}" {{ $reservation->status === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                            @endforeach
                        </select>
                    </form>
                </div>
            </div>
        @empty
            <div class="eos-empty" style="padding:32px 16px;">No reservations yet. Booking requests appear here when customers submit the reservation form on your site.</div>
        @endforelse
    </div>
</div>
@endsection
