<?php

namespace App\Contracts;

use App\Models\LeadSource;

interface LeadSourceAdapter
{
    /** @return array<int, array<string, mixed>> */
    public function fetch(LeadSource $source): array;
}
