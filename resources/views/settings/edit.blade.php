@extends('layouts.app')

@section('title', 'Settings')
@section('subtitle', 'Branding, email & system configuration')

@section('content')
    @php
        $smtpKeys = ['brevo_api_key', 'smtp_host', 'smtp_port', 'smtp_username', 'smtp_password', 'smtp_encryption', 'smtp_from_address', 'smtp_from_name'];
        $tplKeys = ['email_invoice_subject', 'email_invoice_body', 'email_renewal_subject', 'email_renewal_body', 'email_payment_subject', 'email_payment_body', 'email_completion_subject', 'email_completion_body'];
        $billingKeys = ['billing_razorpay_key', 'billing_razorpay_secret', 'billing_razorpay_webhook_secret', 'billing_bank_label', 'billing_bank_instructions', 'billing_cash_instructions'];
        $activeTab = $errors->hasAny($billingKeys) ? 'billing' : ($errors->hasAny($smtpKeys) ? 'smtp' : ($errors->hasAny($tplKeys) ? 'templates' : 'branding'));
    @endphp

    <div x-data="{ tab: '{{ $activeTab }}' }">
        <div class="eos-tabs">
            <button type="button" class="eos-tab" :class="{ active: tab === 'branding' }" @click="tab = 'branding'">
                <i class="ti ti-photo"></i> Branding
            </button>
            <button type="button" class="eos-tab" :class="{ active: tab === 'smtp' }" @click="tab = 'smtp'">
                <i class="ti ti-mail-cog"></i> SMTP / Email
            </button>
            <button type="button" class="eos-tab" :class="{ active: tab === 'templates' }" @click="tab = 'templates'">
                <i class="ti ti-template"></i> Email Templates
            </button>
            <button type="button" class="eos-tab" :class="{ active: tab === 'billing' }" @click="tab = 'billing'">
                <i class="ti ti-credit-card-pay"></i> Billing & Payments
            </button>
        </div>

        <form method="POST" action="{{ route('settings.update') }}" enctype="multipart/form-data">
            @csrf

            {{-- ── BRANDING ── --}}
            <div x-show="tab === 'branding'" x-cloak>
                <div class="eos-card" style="margin-bottom:14px;">
                    <div class="eos-card-header"><div class="eos-card-title">Company Logo</div></div>
                    <p style="font-size:11.5px;color:var(--text-dim);margin-bottom:12px;">
                        Shown in the header of every invoice and agreement PDF. PNG/JPG/WEBP, max 2&nbsp;MB.
                    </p>
                    @if ($logo)
                        <div style="background:#fff;display:inline-block;padding:10px;border-radius:8px;margin-bottom:10px;">
                            <img src="{{ Storage::url($logo) }}" alt="Logo" style="max-height:64px;display:block;">
                        </div>
                        <label style="display:flex;align-items:center;gap:6px;font-size:11.5px;color:var(--text-secondary);margin-bottom:10px;">
                            <input type="checkbox" name="remove_logo" value="1"> Remove current logo
                        </label>
                    @endif
                    <div class="eos-field">
                        <label class="eos-label">Upload Logo</label>
                        <input type="file" name="logo" accept="image/*" class="eos-input">
                    </div>
                </div>

                <div class="eos-card" style="margin-bottom:14px;">
                    <div class="eos-card-header"><div class="eos-card-title">Signature</div></div>
                    <p style="font-size:11.5px;color:var(--text-dim);margin-bottom:12px;">
                        Appears in the signature block of invoice and agreement PDFs. Use a transparent PNG for best results.
                    </p>
                    @if ($signature)
                        <div style="background:#fff;display:inline-block;padding:10px;border-radius:8px;margin-bottom:10px;">
                            <img src="{{ Storage::url($signature) }}" alt="Signature" style="max-height:64px;display:block;">
                        </div>
                        <label style="display:flex;align-items:center;gap:6px;font-size:11.5px;color:var(--text-secondary);margin-bottom:10px;">
                            <input type="checkbox" name="remove_signature" value="1"> Remove current signature
                        </label>
                    @endif
                    <div class="eos-field">
                        <label class="eos-label">Upload Signature</label>
                        <input type="file" name="signature" accept="image/*" class="eos-input">
                    </div>
                </div>
            </div>

            {{-- ── SMTP ── --}}
            <div x-show="tab === 'smtp'" x-cloak>
                <div class="eos-card" style="margin-bottom:14px;">
                    <div class="eos-card-header"><div class="eos-card-title">Brevo API <span style="color:var(--accent-teal);font-weight:700;">— Recommended</span></div></div>
                    <p style="font-size:11.5px;color:var(--text-dim);margin-bottom:14px;line-height:1.7;">
                        Sends mail through Brevo's HTTPS API instead of SMTP. This is the reliable option on this
                        server — the host blocks outbound SMTP ports, so plain SMTP times out. Get the key from
                        Brevo → <strong>SMTP &amp; API → API keys &amp; MCP</strong>. When an API key is set, it is
                        used and the SMTP fields below are ignored. A <strong>From Address</strong> (below) is still required.
                    </p>
                    <div class="eos-field">
                        <label class="eos-label">Brevo API Key {{ $brevo_api_key_set ? '(saved — leave blank to keep)' : '' }}</label>
                        <input type="password" name="brevo_api_key" value="" class="eos-input" autocomplete="new-password" placeholder="{{ $brevo_api_key_set ? '••••••••' : 'xkeysib-…' }}">
                        @if ($brevo_api_key_preview)
                            @php $looksWrong = ! str_starts_with($brevo_api_key_preview, 'xkeysi'); @endphp
                            <div style="font-size:11px;margin-top:6px;color:{{ $looksWrong ? 'var(--accent-red, #ef4444)' : 'var(--accent-teal, #10b981)' }};font-family:ui-monospace,monospace;">
                                {{ $looksWrong ? '✕' : '✓' }} Stored in DB: <strong>{{ $brevo_api_key_preview }}</strong>
                                @if ($looksWrong)
                                    <div style="color:var(--accent-red, #ef4444);margin-top:4px;">
                                        This doesn't start with <code>xkeysib-</code>. You likely pasted the <strong>MCP key</strong> instead of the <strong>v3 API key</strong>. Brevo will reject it with <code>401 Key not found</code>.
                                    </div>
                                @endif
                            </div>
                        @else
                            <div style="font-size:11px;margin-top:6px;color:var(--text-dim);">Not saved in DB yet.</div>
                        @endif
                        @error('brevo_api_key') <div class="eos-error">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="eos-card" style="margin-bottom:14px;">
                    <div class="eos-card-header"><div class="eos-card-title">SMTP / Email Sending <span style="color:var(--text-dim);">— fallback</span></div></div>
                    <p style="font-size:11.5px;color:var(--text-dim);margin-bottom:14px;">
                        Classic SMTP. Used only when no Brevo API key is set above. May not work on this host
                        because outbound SMTP ports are commonly blocked.
                    </p>
                    <div class="eos-form-grid">
                        <div class="eos-field">
                            <label class="eos-label">SMTP Host</label>
                            <input type="text" name="smtp_host" value="{{ old('smtp_host', $smtp['smtp_host']) }}" class="eos-input" placeholder="smtp.gmail.com">
                            @error('smtp_host') <div class="eos-error">{{ $message }}</div> @enderror
                        </div>
                        <div class="eos-field">
                            <label class="eos-label">Port</label>
                            <input type="number" name="smtp_port" value="{{ old('smtp_port', $smtp['smtp_port']) }}" class="eos-input" placeholder="587">
                            @error('smtp_port') <div class="eos-error">{{ $message }}</div> @enderror
                        </div>
                        <div class="eos-field">
                            <label class="eos-label">Username</label>
                            <input type="text" name="smtp_username" value="{{ old('smtp_username', $smtp['smtp_username']) }}" class="eos-input" autocomplete="off" placeholder="you@gmail.com">
                            @error('smtp_username') <div class="eos-error">{{ $message }}</div> @enderror
                        </div>
                        <div class="eos-field">
                            <label class="eos-label">Password {{ $smtp_password_set ? '(saved — leave blank to keep)' : '' }}</label>
                            <input type="password" name="smtp_password" value="" class="eos-input" autocomplete="new-password" placeholder="{{ $smtp_password_set ? '••••••••' : 'App password' }}">
                            @error('smtp_password') <div class="eos-error">{{ $message }}</div> @enderror
                        </div>
                        <div class="eos-field">
                            <label class="eos-label">Encryption</label>
                            <select name="smtp_encryption" class="eos-select">
                                @foreach (['tls' => 'TLS (port 587)', 'ssl' => 'SSL (port 465)', 'none' => 'None'] as $val => $lbl)
                                    <option value="{{ $val }}" @selected(old('smtp_encryption', $smtp['smtp_encryption']) === $val)>{{ $lbl }}</option>
                                @endforeach
                            </select>
                            @error('smtp_encryption') <div class="eos-error">{{ $message }}</div> @enderror
                        </div>
                        <div class="eos-field">
                            <label class="eos-label">From Address</label>
                            <input type="email" name="smtp_from_address" value="{{ old('smtp_from_address', $smtp['smtp_from_address']) }}" class="eos-input" placeholder="billing@ehlomdigital.com">
                            @error('smtp_from_address') <div class="eos-error">{{ $message }}</div> @enderror
                        </div>
                        <div class="eos-field">
                            <label class="eos-label">From Name</label>
                            <input type="text" name="smtp_from_name" value="{{ old('smtp_from_name', $smtp['smtp_from_name']) }}" class="eos-input" placeholder="Ehlom Digital">
                            @error('smtp_from_name') <div class="eos-error">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── EMAIL TEMPLATES ── --}}
            <div x-show="tab === 'templates'" x-cloak>
                <div class="eos-card" style="margin-bottom:14px;">
                    <div class="eos-card-header"><div class="eos-card-title">Email Templates</div></div>
                    <p style="font-size:11.5px;color:var(--text-dim);margin-bottom:14px;">
                        Subject and body used when emailing invoices and renewal reminders. Placeholders are
                        replaced with real data on send. Line breaks become new lines in the email.
                    </p>

                    <div style="font-size:12px;color:var(--text-secondary);font-weight:600;margin-bottom:8px;">Invoice Email</div>
                    <div class="eos-field">
                        <label class="eos-label">Subject</label>
                        <input type="text" name="email_invoice_subject" value="{{ old('email_invoice_subject', $templates['email_invoice_subject']) }}" class="eos-input">
                        @error('email_invoice_subject') <div class="eos-error">{{ $message }}</div> @enderror
                    </div>
                    <div class="eos-field">
                        <label class="eos-label">Body</label>
                        <textarea name="email_invoice_body" rows="7" class="eos-textarea">{{ old('email_invoice_body', $templates['email_invoice_body']) }}</textarea>
                        @error('email_invoice_body') <div class="eos-error">{{ $message }}</div> @enderror
                    </div>

                    <div style="font-size:12px;color:var(--text-secondary);font-weight:600;margin:14px 0 8px;">Renewal Reminder Email</div>
                    <div class="eos-field">
                        <label class="eos-label">Subject</label>
                        <input type="text" name="email_renewal_subject" value="{{ old('email_renewal_subject', $templates['email_renewal_subject']) }}" class="eos-input">
                        @error('email_renewal_subject') <div class="eos-error">{{ $message }}</div> @enderror
                    </div>
                    <div class="eos-field">
                        <label class="eos-label">Body</label>
                        <textarea name="email_renewal_body" rows="7" class="eos-textarea">{{ old('email_renewal_body', $templates['email_renewal_body']) }}</textarea>
                        @error('email_renewal_body') <div class="eos-error">{{ $message }}</div> @enderror
                    </div>

                    <div style="font-size:12px;color:var(--text-secondary);font-weight:600;margin:14px 0 8px;">Payment Confirmation Email</div>
                    <div class="eos-field">
                        <label class="eos-label">Subject</label>
                        <input type="text" name="email_payment_subject" value="{{ old('email_payment_subject', $templates['email_payment_subject']) }}" class="eos-input">
                        @error('email_payment_subject') <div class="eos-error">{{ $message }}</div> @enderror
                    </div>
                    <div class="eos-field">
                        <label class="eos-label">Body</label>
                        <textarea name="email_payment_body" rows="7" class="eos-textarea">{{ old('email_payment_body', $templates['email_payment_body']) }}</textarea>
                        @error('email_payment_body') <div class="eos-error">{{ $message }}</div> @enderror
                    </div>

                    <div style="font-size:12px;color:var(--text-secondary);font-weight:600;margin:14px 0 8px;">Project Completion Email</div>
                    <div class="eos-field">
                        <label class="eos-label">Subject</label>
                        <input type="text" name="email_completion_subject" value="{{ old('email_completion_subject', $templates['email_completion_subject']) }}" class="eos-input">
                        @error('email_completion_subject') <div class="eos-error">{{ $message }}</div> @enderror
                    </div>
                    <div class="eos-field">
                        <label class="eos-label">Body</label>
                        <textarea name="email_completion_body" rows="7" class="eos-textarea">{{ old('email_completion_body', $templates['email_completion_body']) }}</textarea>
                        @error('email_completion_body') <div class="eos-error">{{ $message }}</div> @enderror
                    </div>

                    <div style="font-size:11.5px;color:var(--text-dim);margin-top:6px;line-height:1.7;">
                        <i class="ti ti-info-circle"></i> Available placeholders:
                        <code>{client_name}</code> <code>{invoice_number}</code> <code>{amount}</code>
                        <code>{due_date}</code> <code>{items}</code> <code>{payment_date}</code>
                        <code>{product_name}</code> <code>{expiry_date}</code> <code>{renewal_amount}</code>
                        <code>{project_title}</code> <code>{completed_at}</code><br>
                        <code>{items}</code> lists the invoice line items &amp; charges. <code>{payment_date}</code> is for the payment confirmation. <code>{project_title}</code> &amp; <code>{completed_at}</code> are for the project completion email.
                    </div>
                </div>
            </div>

            {{-- ── BILLING ── --}}
            <div x-show="tab === 'billing'" x-cloak>
                <div class="eos-card" style="margin-bottom:14px;">
                    <div class="eos-card-header"><div class="eos-card-title">Ehlom Billing Methods</div></div>
                    <p style="font-size:11.5px;color:var(--text-dim);margin-bottom:14px;line-height:1.7;">These methods are used when clients pay Ehlom for invoices, hosting renewals, and platform services. They are separate from each client store's own checkout settings.</p>
                    <div class="eos-form-grid" style="margin-bottom:16px;">
                        <label class="eos-toggle-row"><input type="checkbox" name="billing_razorpay_enabled" value="1" @checked(old('billing_razorpay_enabled', $billing_methods['razorpay']))><span><strong>Razorpay</strong><small>Automatic online payment confirmation</small></span></label>
                        <label class="eos-toggle-row"><input type="checkbox" name="billing_bank_enabled" value="1" @checked(old('billing_bank_enabled', $billing_methods['bank']))><span><strong>Bank transfer / UPI</strong><small>Manual reconciliation after a transfer</small></span></label>
                        <label class="eos-toggle-row"><input type="checkbox" name="billing_cash_enabled" value="1" @checked(old('billing_cash_enabled', $billing_methods['cash']))><span><strong>Cash / manual payment</strong><small>Confirm only after Ehlom receives payment</small></span></label>
                    </div>
                    <div style="font-size:12px;font-weight:700;margin:0 0 10px;">Razorpay</div>
                    <div class="eos-form-grid">
                        <div class="eos-field"><label class="eos-label">Razorpay Key ID</label><input type="text" name="billing_razorpay_key" class="eos-input" value="" placeholder="rzp_live_...">@if($billing_razorpay_key_preview)<div style="font-size:11px;margin-top:6px;color:var(--accent-teal);">Saved: {{ $billing_razorpay_key_preview }}</div>@endif</div>
                        <div class="eos-field"><label class="eos-label">Razorpay Key Secret</label><input type="password" name="billing_razorpay_secret" class="eos-input" value="" placeholder="Leave blank to keep saved secret">@if($billing_razorpay_secret_set)<div style="font-size:11px;margin-top:6px;color:var(--accent-teal);">Saved securely</div>@endif</div>
                    </div>
                    <div class="eos-field"><label class="eos-label">Billing webhook endpoint</label><input type="text" class="eos-input" readonly value="{{ url('/webhook/razorpay/billing') }}"></div>
                    <div class="eos-field"><label class="eos-label">Webhook secret</label><input type="password" name="billing_razorpay_webhook_secret" class="eos-input" value="" placeholder="Set the same value in Razorpay Dashboard">@if($billing_razorpay_webhook_secret_set)<div style="font-size:11px;margin-top:6px;color:var(--accent-teal);">Saved securely</div>@endif</div>
                    <div style="font-size:12px;font-weight:700;margin:18px 0 10px;">Manual payment instructions</div>
                    <div class="eos-form-grid">
                        <div class="eos-field"><label class="eos-label">Bank / UPI label</label><input type="text" name="billing_bank_label" class="eos-input" value="{{ old('billing_bank_label', $billing_methods['bank_label']) }}" placeholder="Bank transfer / UPI"></div>
                        <div class="eos-field"><label class="eos-label">Cash / manual instructions</label><textarea name="billing_cash_instructions" class="eos-textarea" rows="3" placeholder="Where and how clients should arrange cash payment">{{ old('billing_cash_instructions', $billing_methods['cash_instructions']) }}</textarea></div>
                    </div>
                    <div class="eos-field" style="margin-bottom:0;"><label class="eos-label">Bank / UPI instructions</label><textarea name="billing_bank_instructions" class="eos-textarea" rows="4" placeholder="Account name, account number, IFSC, bank name, or UPI ID">{{ old('billing_bank_instructions', $billing_methods['bank_instructions']) }}</textarea></div>
                </div>
            </div>

            <button class="eos-btn eos-btn-primary"><i class="ti ti-device-floppy"></i> Save Settings</button>
        </form>

        {{-- Test email — separate form, shown alongside the SMTP tab --}}
        <div x-show="tab === 'smtp'" x-cloak class="eos-card" style="margin-top:14px;">
            <div class="eos-card-header"><div class="eos-card-title">Send Test Email</div></div>
            <p style="font-size:11.5px;color:var(--text-dim);margin-bottom:12px;">
                Save your SMTP settings first, then send a test email to confirm they work.
            </p>
            <form method="POST" action="{{ route('settings.testEmail') }}" style="display:flex;gap:8px;align-items:flex-end;flex-wrap:wrap;">
                @csrf
                <div class="eos-field" style="margin-bottom:0;min-width:240px;">
                    <label class="eos-label">Recipient (defaults to From Address)</label>
                    <input type="email" name="test_email" value="{{ old('test_email') }}" class="eos-input" placeholder="you@example.com">
                </div>
                <button class="eos-btn eos-btn-secondary"><i class="ti ti-send"></i> Send Test</button>
            </form>
        </div>
    </div>
@endsection
