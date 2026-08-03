<?php

namespace App\Console\Commands;

use App\Models\LeadSource;
use App\Services\LeadSourceManager;
use Illuminate\Console\Command;

class SyncLeadSources extends Command
{
    protected $signature = 'lead-sources:sync {source?} {--scheduled}';
    protected $description = 'Import and deduplicate leads from configured lead sources';

    public function handle(LeadSourceManager $manager): int
    {
        $sources = $this->argument('source')
            ? LeadSource::whereKey($this->argument('source'))->where('status', 'active')->get()
            : LeadSource::where('status', 'active')->get()->filter(fn (LeadSource $source) => !$this->option('scheduled') || data_get($source->settings, 'auto_sync', false));

        foreach ($sources as $source) {
            try {
                $result = $manager->sync($source);
                $this->info($source->name . ': ' . $result['imported'] . ' leads imported');
            } catch (\Throwable $e) {
                $source->update(['last_synced_at' => now(), 'last_sync_status' => 'failed', 'last_error' => $e->getMessage()]);
                $this->error($source->name . ': ' . $e->getMessage());
            }
        }

        return self::SUCCESS;
    }
}
