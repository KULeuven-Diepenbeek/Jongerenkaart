<?php

namespace App\Services;

use App\Models\Organisatie;

class OrganisatieDeletionService
{
    public function __construct(
        private readonly OrganisatieFilterOptionsService $filterOptions,
        private readonly TrafficRecorder $traffic,
    ) {}

    public function delete(Organisatie $organisatie): void
    {
        $before = $this->filterOptions->snapshot($organisatie);

        $organisatie->unsearchable();
        $organisatie->delete();

        $this->filterOptions->syncOrganisationChange($before, null);
        $this->traffic->recordOrganisationChange('deleted');
    }
}
