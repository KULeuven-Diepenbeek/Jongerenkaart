<?php

namespace Tests\Unit;

use App\Models\Organisatie;
use App\Services\OrganisatieLocationService;
use Tests\TestCase;

class OrganisatieContactAndLocationTest extends TestCase
{
    public function test_primary_address_is_formatted_and_linked_for_navigation(): void
    {
        $organisatie = new Organisatie([
            'adresgegevens' => [
                ['straatnaam' => 'Andere straat', 'huisnummer' => '1'],
                [
                    'hoofd_adres' => true,
                    'straatnaam' => 'Markt',
                    'huisnummer' => '12',
                    'postcode' => '1000',
                    'deelgemeente' => 'Brussel',
                ],
            ],
        ]);

        $address = $organisatie->hoofdAdres();

        $this->assertSame('Markt 12, 1000 Brussel', $organisatie->formatAdres($address));
        $this->assertSame(
            'https://www.google.com/maps/search/?api=1&query=Markt%2012%2C%201000%20Brussel',
            $organisatie->navigationUrlForAddress($address),
        );
    }

    public function test_primary_contact_values_are_preferred(): void
    {
        $organisatie = new Organisatie([
            'contactgegevens' => [
                'telefoonnummers' => [
                    ['nummer' => '010 00 00 00'],
                    ['nummer' => '020 00 00 00', 'primair' => true],
                ],
                'emailadressen' => [
                    ['adres' => 'ander@example.test'],
                    ['adres' => 'primair@example.test', 'primair' => true],
                ],
                'online' => [
                    ['url' => 'https://ander.example.test'],
                    ['url' => 'https://primair.example.test', 'primair' => true],
                ],
            ],
        ]);

        $this->assertSame('020 00 00 00', $organisatie->getPrimairTelefoon());
        $this->assertSame('primair@example.test', $organisatie->getPrimairEmail());
        $this->assertSame('https://primair.example.test', $organisatie->getPrimairWebsite());
    }

    public function test_contact_emails_are_normalized_and_deduplicated(): void
    {
        $organisatie = new Organisatie([
            'contactgegevens' => [
                'emailadressen' => [
                    ['adres' => ' INFO@EXAMPLE.TEST '],
                ],
            ],
            'contactpersonen' => [
                ['email' => 'info@example.test'],
                ['email' => 'persoon@example.test'],
            ],
        ]);

        $this->assertSame(
            ['info@example.test', 'persoon@example.test'],
            $organisatie->contactEmailAddresses(),
        );
        $this->assertTrue($organisatie->hasContactEmail(' Info@Example.Test '));
        $this->assertFalse($organisatie->hasContactEmail('onbekend@example.test'));
    }

    public function test_geojson_coordinates_are_read_as_longitude_then_latitude(): void
    {
        $organisatie = new Organisatie([
            'adresgegevens' => [[
                'hoofd_adres' => true,
                'locatie' => ['coordinates' => [4.3517, 50.8503]],
            ]],
        ]);

        $this->assertSame(50.8503, $organisatie->getLat());
        $this->assertSame(4.3517, $organisatie->getLon());
    }

    public function test_invalid_coordinates_are_rejected(): void
    {
        $service = new OrganisatieLocationService;

        $this->assertNull($service->addressWithCoordinates(['lat' => 91, 'lon' => 4]));
        $this->assertNull($service->addressWithCoordinates(['lat' => 50, 'lon' => 181]));
        $this->assertNull($service->addressWithCoordinates(['lat' => 'onbekend', 'lon' => 4]));
    }

    public function test_radius_filter_includes_boundary_and_sorts_nearest_first(): void
    {
        $service = new OrganisatieLocationService;
        $near = $this->organisationAt(50.8503, 4.3517);
        $far = $this->organisationAt(50.895, 4.3517);
        $outside = $this->organisationAt(51.05, 4.3517);

        $results = $service->filterWithinRadius(
            collect([$outside, $far, $near]),
            ['lat' => 50.8503, 'lon' => 4.3517],
            5,
        );

        $this->assertCount(2, $results);
        $this->assertSame($near, $results->get(0));
        $this->assertSame($far, $results->get(1));
        $this->assertSame(0.0, $near->getAttribute('distance'));
        $this->assertLessThanOrEqual(5, $far->getAttribute('distance'));
    }

    public function test_radius_options_include_small_distances_and_stop_at_eighty(): void
    {
        $options = (new OrganisatieLocationService)->radiusOptions();

        $this->assertSame([1, 3, 5, 10], array_slice($options, 0, 4));
        $this->assertSame(80, end($options));
    }

    private function organisationAt(float $lat, float $lon): Organisatie
    {
        return new Organisatie([
            'adresgegevens' => [[
                'hoofd_adres' => true,
                'lat' => $lat,
                'lon' => $lon,
            ]],
        ]);
    }
}
