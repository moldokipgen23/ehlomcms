<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Runs the daily renewal jobs without an OS cron entry. The work happens in
 * terminate(), i.e. AFTER the response is sent to the browser, so the admin
 * never waits for it. It fires at most once per day, on the first request
 * received at or after 09:00.
 */
class RunDailyTasks
{
    /** Commands to run once per day, in order. */
    private const COMMANDS = [
        'reminders:send-renewals',
        'invoices:generate-renewals',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }

    public function terminate(Request $request, Response $response): void
    {
        $today = now()->toDateString();

        // Already ran today, or it's still before the 09:00 trigger time.
        if (Setting::get('daily_tasks_last_run') === $today || now()->hour < 9) {
            return;
        }

        // Only one request may run the batch; others skip immediately.
        $lock = Cache::lock('ehlom-daily-tasks', 300);

        if (! $lock->get()) {
            return;
        }

        try {
            // Mark first so a failure mid-run never causes a retry loop.
            Setting::put('daily_tasks_last_run', $today);

            foreach (self::COMMANDS as $command) {
                Artisan::call($command);
            }

            Log::info('[RunDailyTasks] Daily renewal jobs executed for ' . $today . '.');
        } catch (\Throwable $e) {
            Log::error('[RunDailyTasks] Daily jobs failed: ' . $e->getMessage());
        } finally {
            $lock->release();
        }
    }
}
