<?php

namespace App\Services;

use App\Models\CapitalContribution;
use App\Events\CapitalContributionCreated;
use App\Events\CapitalContributionUpdated;
use App\Events\CapitalContributionDeleted;

class CapitalContributionService
{
    public function getAll()
    {
        return CapitalContribution::orderBy('date', 'desc')->get();
    }

    public function store($data)
    {
        $contribution = CapitalContribution::create($data);
        event(new CapitalContributionCreated($contribution));
        return $contribution;
    }

    public function update($id, $data)
    {
        $contribution = CapitalContribution::findOrFail($id);
        $contribution->update($data);
        event(new CapitalContributionUpdated($contribution));
        return $contribution;
    }

    public function destroy($id)
    {
        $contribution = CapitalContribution::findOrFail($id);
        event(new CapitalContributionDeleted($contribution));
        $contribution->state = 'deleted';
        $contribution->save();
        return $contribution;
    }
}
