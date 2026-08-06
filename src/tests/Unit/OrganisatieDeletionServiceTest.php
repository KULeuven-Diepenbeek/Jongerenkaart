<?php

namespace Tests\Unit;

use App\Models\Organisatie;
use App\Services\OrganisatieDeletionService;
use App\Services\OrganisatieFilterOptionsService;
use App\Services\TrafficRecorder;
use Mockery;
use Tests\TestCase;

class OrganisatieDeletionServiceTest extends TestCase
{
    public function test_deleting_an_organisation_updates_all_derived_data(): void
    {
        $organisatie = Mockery::mock(Organisatie::class);
        $filterOptions = Mockery::mock(OrganisatieFilterOptionsService::class);
        $traffic = Mockery::mock(TrafficRecorder::class);
        $snapshot = ['categorie' => ['Gezondheid']];

        $filterOptions
            ->shouldReceive('snapshot')
            ->once()
            ->with($organisatie)
            ->andReturn($snapshot);
        $organisatie->shouldReceive('unsearchable')->once();
        $organisatie->shouldReceive('delete')->once()->andReturnTrue();
        $filterOptions
            ->shouldReceive('syncOrganisationChange')
            ->once()
            ->with($snapshot, null)
            ->andReturn([]);
        $traffic
            ->shouldReceive('recordOrganisationChange')
            ->once()
            ->with('deleted');

        (new OrganisatieDeletionService($filterOptions, $traffic))->delete($organisatie);

        $this->addToAssertionCount(5);
    }
}
