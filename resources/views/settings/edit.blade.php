@extends('layouts.app')

@section('title', 'Settings')
@section('subtitle', 'Company branding for invoices & agreements')

@section('content')
    <form method="POST" action="{{ route('settings.update') }}" enctype="multipart/form-data">
        @csrf

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

        <div class="eos-card" style="margin-bottom:14px;">
            <div class="eos-card-header"><div class="eos-card-title">SMTP / Email Sending</div></div>
            <p style="font-size:11.5px;color:var(--text-dim);margin-bottom:14px;">
                Outgoing mail server used to email invoices and renewal reminders. Get these from
                your email provider (e.g. Gmail App Password, Zoho, Mailgun, Brevo).
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

            <div style="font-size:11.5px;color:var(--text-dim);margin-top:6px;line-height:1.7;">
                <i class="ti ti-info-circle"></i> Available placeholders:
                <code>{client_name}</code> <code>{invoice_number}</code> <code>{amount}</code>
                <code>{due_date}</code> <code>{items}</code> <code>{payment_date}</code>
                <code>{product_name}</code> <code>{expiry_date}</code> <code>{renewal_amount}</code><br>
                <code>{items}</code> lists the invoice line items &amp; charges. <code>{payment_date}</code> is used in the payment confirmation.
            </div>
        </div>

        <button class="eos-btn eos-btn-primary"><i class="ti ti-device-floppy"></i> Save Settings</button>
    </form>

    <div class="eos-card" style="margin-top:14px;">
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
@endsection
