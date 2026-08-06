<?php

namespace App\Console\Commands;

use App\Models\Organisatie;
use App\Services\OrganisatieFilterOptionsService;
use App\Services\OrganisatieSearchIndexService;
use Illuminate\Console\Command;

class RebuildDerivedOrganisationData extends Command
{
    protected $signature = 'jongerenkaart:rebuild-derived-data';

    protected $description = 'Rebuild organisation filter options and the Meilisearch index';

    public function handle(
        OrganisatieFilterOptionsService $filterOptions,
        OrganisatieSearchIndexService $searchIndex,
    ): int {
        $this->info('Filteropties opnieuw opbouwen...');
        $options = $filterOptions->rebuildFromOrganisations();

        foreach ($options as $field => $values) {
            $this->line($field.': '.count($values).' opties');
        }

        $this->info('Meilisearch index opnieuw opbouwen...');

        foreach ($searchIndex->rebuild() as $message) {
            $this->line($message);
        }

        $this->info(Organisatie::query()->count().' organisaties verwerkt.');

        return self::SUCCESS;
    }
}
