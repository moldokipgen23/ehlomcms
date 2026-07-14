<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminDomainController extends Controller
{
    public function index(): View
    {
        // Previously only listed tenants that ALREADY had a custom_domain,
        // which made the feature impossible to use for the first time - a
        // tenant could never appear here to have a domain assigned. Now
        // lists every active tenant; the view shows a "Set Domain" form for
        // ones without one yet and the existing verify/SSL actions for ones
        // that do.
        $tenants = Tenant::where('status', 'active')
            ->orderByRaw("domain_status = 'none'")
            ->orderBy('name')
            ->get();

        return view('domains.admin-index', compact('tenants'));
    }

    public function issueSsl(Tenant $tenant): RedirectResponse
    {
        $domain = $tenant->custom_domain;

        if (!$domain || $tenant->domain_status !== 'verified') {
            return back()->with('error', 'Domain must be verified first.');
        }

        if (!$this->isValidDomainFormat($domain)) {
            return back()->with('error', 'Stored domain has an invalid format; refusing to run certbot.');
        }

        $email = config('mail.from.address', 'admin@' . config('app.tenant_domain'));
        $webroot = base_path('public');

        // Every value below is escaped even though $domain is already format-
        // validated above - defense in depth, since this builds a real shell
        // command and a future change to isValidDomainFormat() must not
        // silently reopen command injection here.
        $command = 'certbot certonly --webroot -w ' . escapeshellarg($webroot)
            . ' -d ' . escapeshellarg($domain)
            . ' --non-interactive --agree-tos -m ' . escapeshellarg($email)
            . ' --deploy-hook ' . escapeshellarg('nginx -s reload') . ' 2>&1';

        $output = [];
        $exitCode = 0;
        exec($command, $output, $exitCode);

        $outputStr = implode("\n", $output);

        AuditLog::log('ssl_issued', "SSL cert requested for {$domain}", 'tenant', $tenant->id, [
            'exit_code' => $exitCode,
            'output' => $outputStr,
        ]);

        if ($exitCode === 0) {
            return back()->with('success', "SSL certificate issued for {$domain}.");
        }

        if (str_contains($outputStr, 'Certificate already exists')) {
            return back()->with('success', "Certificate already exists for {$domain}. Run renew to update.");
        }

        return back()->with('error', "SSL issuance failed: " . substr($outputStr, 0, 500));
    }

    public function renewSsl(Tenant $tenant): RedirectResponse
    {
        $domain = $tenant->custom_domain;

        if (!$domain) {
            return back()->with('error', 'No custom domain set.');
        }

        if (!$this->isValidDomainFormat($domain)) {
            return back()->with('error', 'Stored domain has an invalid format; refusing to run certbot.');
        }

        $command = 'certbot renew --cert-name ' . escapeshellarg($domain)
            . ' --non-interactive --agree-tos --deploy-hook ' . escapeshellarg('nginx -s reload') . ' 2>&1';
        $output = [];
        $exitCode = 0;
        exec($command, $output, $exitCode);

        $outputStr = implode("\n", $output);

        AuditLog::log('ssl_renewed', "SSL cert renewed for {$domain}", 'tenant', $tenant->id);

        if ($exitCode === 0) {
            return back()->with('success', "SSL certificate renewed for {$domain}.");
        }

        return back()->with('error', "SSL renewal failed: " . substr($outputStr, 0, 500));
    }

    /**
     * The missing piece that made this feature unusable end-to-end: nothing
     * previously set custom_domain in the first place, only acted on tenants
     * that already had one. Strict format validation here is the primary
     * defense against command injection into the certbot exec() calls above
     * (escapeshellarg is the second layer, in case this validation is ever
     * loosened without the exec() call sites being re-reviewed).
     */
    public function store(Request $request, Tenant $tenant): RedirectResponse
    {
        $validated = $request->validate([
            'custom_domain' => ['required', 'string', 'max:255'],
        ]);

        $domain = strtolower(trim($validated['custom_domain']));

        if (!$this->isValidDomainFormat($domain)) {
            return back()->with('error', 'Enter a plain domain name (e.g. shop.example.com) — no http://, paths, or spaces.');
        }

        $tenant->update([
            'custom_domain' => $domain,
            'domain_status' => 'pending',
            'domain_verified_at' => null,
        ]);

        AuditLog::log('domain_set', "Custom domain {$domain} set for {$tenant->name}", 'tenant', $tenant->id);

        return back()->with('success', "Domain {$domain} saved. Point its CNAME record, then click Verify.");
    }

    /**
     * Whitelist format check: lowercase letters, digits, hyphens, dots only,
     * each label 1-63 chars, no leading/trailing dot or hyphen, max 255
     * total. Deliberately conservative - real-world domains fit this easily,
     * and this is the primary guard against shell metacharacters ever
     * reaching the certbot exec() calls in issueSsl()/renewSsl().
     */
    private function isValidDomainFormat(string $domain): bool
    {
        return (bool) preg_match(
            '/^(?=.{1,255}$)(?!-)[a-z0-9-]{1,63}(?<!-)(\.(?!-)[a-z0-9-]{1,63}(?<!-))+$/',
            $domain
        );
    }

    public function verify( Tenant $tenant): RedirectResponse
    {
        $domain = $tenant->custom_domain;

        if (!$domain) {
            return back()->with('error', 'No custom domain set.');
        }

        $host = parse_url(config('app.url'), PHP_URL_HOST);
        $subdomain = preg_replace('/^https?:\/\//', '', $host);

        $records = @dns_get_record($domain, DNS_CNAME);

        $verified = false;
        if ($records) {
            foreach ($records as $r) {
                if (str_ends_with($r['target'] ?? '', $subdomain) || str_ends_with($r['target'] ?? '', config('app.tenant_domain'))) {
                    $verified = true;
                    break;
                }
            }
        }

        if ($verified) {
            $tenant->update([
                'domain_status' => 'verified',
                'domain_verified_at' => now(),
            ]);

            AuditLog::log('domain_verified', "Verified custom domain {$domain}", 'tenant', $tenant->id);

            return back()->with('success', "Domain {$domain} verified.");
        }

        return back()->with('error', "CNAME not found. Point {$domain} → {$subdomain}.");
    }

    public function remove( Tenant $tenant): RedirectResponse
    {
        $domain = $tenant->custom_domain;

        $tenant->update([
            'custom_domain' => null,
            'domain_status' => 'none',
            'domain_verified_at' => null,
        ]);

        AuditLog::log('domain_removed', "Removed custom domain {$domain}", 'tenant', $tenant->id);

        return back()->with('success', 'Custom domain removed.');
    }
}
