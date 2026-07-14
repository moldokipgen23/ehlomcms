<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\EmailTemplate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminEmailTemplateController extends Controller
{
    private const SYSTEM_KEYS = ['invoice_new', 'invoice_reminder', 'invoice_paid', 'welcome_tenant'];

    public function index(): View
    {
        $templates = EmailTemplate::orderBy('key')->get();
        return view('email-templates.index', compact('templates'));
    }

    public function create(): View
    {
        return view('email-templates.form');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'key' => 'required|string|max:100|unique:email_templates,key',
            'name' => 'required|string|max:255',
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
            'variables' => 'nullable|json',
        ]);

        // validate() omits 'variables' from the array when absent from the
        // request (nullable fields aren't added as null) - direct access
        // throws "Undefined array key" the moment a template is
        // saved with no variables field. Confirmed live (500 error).
        $validated['variables'] = !empty($validated['variables']) ? json_decode($validated['variables'], true) : [];

        EmailTemplate::create($validated);

        AuditLog::log('email_template_created', "Created email template {$validated['name']}", 'email_template');

        return redirect()->route('email-templates.index')->with('success', 'Email template created.');
    }

    public function edit(EmailTemplate $emailTemplate): View
    {
        return view('email-templates.form', ['template' => $emailTemplate]);
    }

    public function update(Request $request, EmailTemplate $emailTemplate): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
            'variables' => 'nullable|json',
        ]);

        // validate() omits 'variables' from the array when absent from the
        // request (nullable fields aren't added as null) - direct access
        // throws "Undefined array key" the moment a template is
        // saved with no variables field. Confirmed live (500 error).
        $validated['variables'] = !empty($validated['variables']) ? json_decode($validated['variables'], true) : [];

        $emailTemplate->update($validated);

        AuditLog::log('email_template_updated', "Updated email template {$validated['name']}", 'email_template', $emailTemplate->id);

        return redirect()->route('email-templates.index')->with('success', 'Email template updated.');
    }

    public function destroy(EmailTemplate $emailTemplate): RedirectResponse
    {
        if (in_array($emailTemplate->key, self::SYSTEM_KEYS)) {
            return back()->with('error', 'System templates cannot be deleted.');
        }

        AuditLog::log('email_template_deleted', "Deleted email template {$emailTemplate->name}", 'email_template', $emailTemplate->id);

        $emailTemplate->delete();

        return redirect()->route('email-templates.index')->with('success', 'Email template deleted.');
    }
}
