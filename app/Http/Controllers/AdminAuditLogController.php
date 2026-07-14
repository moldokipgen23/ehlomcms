<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\View\View;

class AdminAuditLogController extends Controller
{
    public function index(): View
    {
        $logs = AuditLog::with('user')->orderByDesc('created_at')->paginate(100);
        return view('audit-logs.index', compact('logs'));
    }
}
