@extends('tenant.layouts.dashboard')

@section('title', 'Add-on Shop')
@section('subtitle', 'Upgrade this store with ecommerce tools')

@section('content')
    @php
        $activeCount = $addons->filter(function ($addon, $key) use ($records, $tenant) {
            return ($records->get($key)->status ?? null) === 'active';
        })->count();
        $formatPrice = fn ($price) => '₹' . number_format((float) $price, 0);
        $billingSuffix = fn ($addon) => ($addon->billing_cycle ?? 'monthly') === 'one_time' ? 'once' : '/' . $addon->billingLabel();
    @endphp

    <style>
        .addon-shop { display: grid; gap: 18px; }
        .addon-shop-hero {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
            border: 1px solid var(--border-card);
            border-radius: 12px;
            padding: 20px;
            background: linear-gradient(140deg, rgba(29,34,54,.98), rgba(16,20,32,.98));
        }
        .addon-shop-title { color: var(--text-primary); font-family: 'Syne', sans-serif; font-size: 26px; font-weight: 700; }
        .addon-shop-copy { color: var(--text-muted); font-size: 13px; line-height: 1.6; margin-top: 6px; max-width: 720px; }
        .addon-shop-metrics { display: flex; gap: 10px; flex-wrap: wrap; }
        .addon-shop-metric {
            min-width: 118px;
            padding: 12px;
            border: 1px solid var(--border);
            border-radius: 10px;
            background: rgba(255,255,255,.03);
        }
        .addon-shop-metric strong { display: block; color: var(--text-primary); font-size: 20px; line-height: 1; }
        .addon-shop-metric span { display: block; color: var(--text-muted); font-size: 10.5px; margin-top: 6px; }
        .addon-shop-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 14px; }
        .addon-card {
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            min-height: 250px;
            border: 1px solid var(--border-card);
            border-radius: 12px;
            background: linear-gradient(160deg, rgba(28,33,53,.98), rgba(19,23,37,.98));
        }
        .addon-card.active { border-color: rgba(29,184,132,.78); }
        .addon-card.pending { border-color: rgba(232,169,48,.74); }
        .addon-card::before {
            content: '';
            position: absolute;
            inset: 0 0 auto;
            height: 3px;
            background: linear-gradient(90deg, var(--accent-amber), var(--accent-blue));
        }
        .addon-card.active::before { background: var(--accent-teal); }
        .addon-card.pending::before { background: var(--accent-amber); }
        .addon-card-body { display: flex; flex-direction: column; flex: 1; padding: 16px; }
        .addon-card-top { display: flex; align-items: flex-start; gap: 12px; }
        .addon-card-icon {
            width: 42px;
            height: 42px;
            border-radius: 11px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 21px;
            background: linear-gradient(135deg, var(--accent-blue), var(--accent-purple));
            flex-shrink: 0;
        }
        .addon-card.active .addon-card-icon { background: var(--accent-teal); }
        .addon-card.pending .addon-card-icon { background: var(--accent-amber); }
        .addon-card-name { color: var(--text-primary); font-size: 15px; font-weight: 800; line-height: 1.25; }
        .addon-card-desc { color: var(--text-muted); font-size: 12px; line-height: 1.55; margin-top: 12px; flex: 1; }
        .addon-card-foot { display: grid; gap: 12px; margin-top: 16px; }
        .addon-card-price { display: flex; align-items: center; justify-content: space-between; gap: 10px; }
        .addon-price-main { color: var(--text-primary); font-size: 18px; font-weight: 900; }
        .addon-price-sub { color: var(--text-dim); font-size: 10px; margin-top: 2px; }
        .addon-status {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 5px 8px;
            border-radius: 999px;
            font-size: 10px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: .4px;
            white-space: nowrap;
        }
        .addon-status.active { color: var(--accent-teal); background: rgba(29,184,132,.12); }
        .addon-status.pending { color: var(--accent-amber); background: rgba(232,169,48,.13); }
        .addon-status.inactive { color: var(--text-muted); background: rgba(139,148,184,.1); }
        .addon-cta {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
            min-height: 39px;
            border: 0;
            border-radius: 9px;
            color: #fff;
            background: var(--accent-teal);
            font-size: 12.5px;
            font-weight: 900;
            text-decoration: none;
            cursor: pointer;
        }
        .addon-cta.secondary { color: var(--text-secondary); background: rgba(255,255,255,.06); border: 1px solid var(--border); }
        .addon-cta.danger { background: #b93b3b; }
        @media (max-width: 1180px) {
            .addon-shop-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .addon-shop-hero { align-items: flex-start; flex-direction: column; }
        }
        @media (max-width: 680px) {
            .addon-shop-grid { grid-template-columns: 1fr; }
            .addon-shop-title { font-size: 22px; }
            .addon-shop-metrics { width: 100%; }
            .addon-shop-metric { flex: 1; min-width: 0; }
        }
    </style>

    <div class="addon-shop">
        <section class="addon-shop-hero">
            <div>
                <div class="addon-shop-title">Store add-on shop</div>
                <div class="addon-shop-copy">
                    Request standalone Ehlom services such as WhatsApp API, AI Agent, automation, and other platform add-ons. Business-module features are managed separately by Ehlom admin.
                </div>
            </div>
            <div class="addon-shop-metrics">
                <div class="addon-shop-metric">
                    <strong>{{ $activeCount }}</strong>
                    <span>Active upgrades</span>
                </div>
                <div class="addon-shop-metric">
                    <strong>{{ count($addons) }}</strong>
                    <span>Available add-ons</span>
                </div>
            </div>
        </section>

        <section class="addon-shop-grid">
            @foreach ($addons as $key => $addon)
                @php
                    $record = $records->get($key);
                    $status = $record->status ?? 'inactive';
                    $priceWithTax = $addon->price * 1.18;
                    $suffix = $billingSuffix($addon);
                @endphp
                <article class="addon-card {{ $status }}">
                    <div class="addon-card-body">
                        <div class="addon-card-top">
                            <div class="addon-card-icon"><i class="ti {{ $addon->icon }}"></i></div>
                            <div style="flex:1;min-width:0;">
                                <div class="addon-card-name">{{ $addon->name }}</div>
                                <div class="addon-card-price" style="margin-top:10px;">
                                    <div>
                                        <div class="addon-price-main">{{ $formatPrice($addon->price) }} {{ $suffix }}</div>
                                        <div class="addon-price-sub">{{ $formatPrice($priceWithTax) }} {{ $suffix }} incl. GST</div>
                                    </div>
                                    <span class="addon-status {{ $status }}">
                                        <i class="ti {{ $status === 'active' ? 'ti-check' : ($status === 'pending' ? 'ti-clock' : 'ti-plus') }}"></i>
                                        {{ $status === 'active' ? 'Active' : ($status === 'pending' ? 'Pending' : 'Upgrade') }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="addon-card-desc">{{ $addon->description }}</div>

                        <div class="addon-card-foot">
                            @if ($status === 'active')
                                <form action="{{ route('tenant.addons.toggle', $key) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="addon-cta danger"><i class="ti ti-toggle-left"></i> Disable Add-on</button>
                                </form>
                            @elseif ($status === 'pending')
                                <form action="{{ route('tenant.addons.toggle', $key) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="addon-cta secondary"><i class="ti ti-clock-cancel"></i> Cancel Request</button>
                                </form>
                            @else
                                <a href="{{ route('tenant.addons.checkout', $key) }}" class="addon-cta"><i class="ti ti-credit-card"></i> Subscribe</a>
                            @endif
                        </div>
                    </div>
                </article>
            @endforeach
        </section>
    </div>
@endsection
