<?php

namespace Tests\Unit;

use App\Services\OrganisatieSearchFilterBuilder;
use Illuminate\Http\Request;
use PHPUnit\Framework\TestCase;

class OrganisatieSearchFilterBuilderTest extends TestCase
{
    private OrganisatieSearchFilterBuilder $builder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->builder = new OrganisatieSearchFilterBuilder;
    }

    public function test_multiple_categories_use_an_in_filter(): void
    {
        $filters = $this->build([
            'categorie' => ['Gezondheid', 'Welzijn'],
        ]);

        $this->assertSame(
            ['category IN ["Gezondheid", "Welzijn"]'],
            $filters,
        );
    }

    public function test_age_and_boolean_aliases_build_typed_filters(): void
    {
        $filters = $this->build([
            'leeftijd' => '16',
            'isFree' => 'true',
            'anoniem' => 'ja',
        ]);

        $this->assertSame([
            'ages = 16',
            'isFree = true',
            'isAnonymous = true',
        ], $filters);
    }

    public function test_invalid_age_and_boolean_values_are_ignored(): void
    {
        $filters = $this->build([
            'leeftijd' => '121',
            'anoniem' => 'misschien',
        ]);

        $this->assertSame([], $filters);
    }

    public function test_duplicate_values_are_removed(): void
    {
        $filters = $this->build([
            'rubriek' => ['Jeugd', 'Jeugd'],
        ]);

        $this->assertSame(['rubriek = "Jeugd"'], $filters);
    }

    public function test_quotes_and_backslashes_are_escaped(): void
    {
        $filters = $this->build([
            'aanbod' => 'Hulp "thuis" \\ mobiel',
        ]);

        $this->assertSame(['aanbod = "Hulp \\"thuis\\" \\\\ mobiel"'], $filters);
    }

    private function build(array $query): array
    {
        return $this->builder->build(Request::create('/search/results', 'GET', $query));
    }
}
