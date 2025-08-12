<?php

namespace App\Events;

use App\Models\CapitalContribution;
use Illuminate\Foundation\Events\Dispatchable;

class CapitalContributionDeleted
{
    use Dispatchable;

    public $contribution;

    public function __construct(CapitalContribution $contribution)
    {
        $this->contribution = $contribution;
    }
}
