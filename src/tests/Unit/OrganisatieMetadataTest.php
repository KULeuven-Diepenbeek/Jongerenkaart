<?php

namespace Tests\Unit;

use App\Models\Organisatie;
use Tests\TestCase;

class OrganisatieMetadataTest extends TestCase
{
    public function test_free_status_only_uses_kostprijs(): void
    {
        $gratis = new Organisatie([
            Organisatie::FIELD_KOSTPRIJS => 'Volledig gratis',
        ]);
        $kosteloos = new Organisatie([
            Organisatie::FIELD_KOSTPRIJS => 'Kosteloos voor jongeren',
        ]);
        $otherField = new Organisatie([
            Organisatie::FIELD_KOSTPRIJS => 'Betalend',
            'betalingswijze' => 'Gratis',
        ]);

        $this->assertTrue($gratis->isGratis());
        $this->assertTrue($kosteloos->isGratis());
        $this->assertFalse($otherField->isGratis());
    }

    public function test_anonymous_status_accepts_yes_with_an_explanation(): void
    {
        $anonymous = new Organisatie([
            Organisatie::FIELD_ANONIEM => 'Ja (op verzoek)',
        ]);
        $notAnonymous = new Organisatie([
            Organisatie::FIELD_ANONIEM => 'Neen',
        ]);

        $this->assertTrue($anonymous->isAnoniem());
        $this->assertTrue($anonymous->is_anoniem);
        $this->assertFalse($notAnonymous->isAnoniem());
        $this->assertFalse((new Organisatie)->isAnoniem());
    }

    public function test_new_metadata_is_available_through_model_accessors(): void
    {
        $organisatie = new Organisatie([
            Organisatie::FIELD_BESCHIKBAARHEID_TELEFOON => 'Elke werkdag van 9 tot 17 uur',
            Organisatie::FIELD_BESCHIKBAARHEID_CHAT => 'Maandag tot donderdag',
            Organisatie::FIELD_SOCIALE_KAART_ID => 'SK-1234',
        ]);

        $this->assertSame('Elke werkdag van 9 tot 17 uur', $organisatie->beschikbaarheid_telefoon);
        $this->assertSame('Maandag tot donderdag', $organisatie->beschikbaarheid_chat);
        $this->assertSame('SK-1234', $organisatie->sociale_kaart_id);
    }

    public function test_search_document_contains_derived_boolean_filters(): void
    {
        $organisatie = new Organisatie([
            'publiekeNaam' => 'Testorganisatie',
            'categorie' => 'Hulpverlening',
            Organisatie::FIELD_KOSTPRIJS => 'Gratis',
            Organisatie::FIELD_ANONIEM => 'Ja',
        ]);

        $searchDocument = $organisatie->toSearchableArray();

        $this->assertTrue($searchDocument['isFree']);
        $this->assertTrue($searchDocument['isAnonymous']);
    }

    public function test_age_range_includes_both_boundaries(): void
    {
        $organisatie = new Organisatie([
            'leeftijdscategorie' => '12 - 18 jaar',
        ]);

        $ages = $organisatie->toSearchableArray()['ages'];

        $this->assertContains(12, $ages);
        $this->assertContains(18, $ages);
        $this->assertNotContains(11, $ages);
        $this->assertNotContains(19, $ages);
    }

    public function test_context_contains_category_subcategories_and_description(): void
    {
        $organisatie = new Organisatie([
            'publiekeNaam' => 'De Wegwijzer',
            'categorie' => 'Onderwijs',
            'subcategorie' => 'Studiebegeleiding',
            'subsubcategorie' => 'Huiswerk',
            'algemeneWerking' => 'Begeleiding na schooltijd.',
        ]);

        $context = $organisatie->toSearchableArray()['context'];

        $this->assertStringContainsString('De Wegwijzer', $context);
        $this->assertStringContainsString('Onderwijs', $context);
        $this->assertStringContainsString('Studiebegeleiding', $context);
        $this->assertStringContainsString('Huiswerk', $context);
        $this->assertStringContainsString('Begeleiding na schooltijd.', $context);
    }

    public function test_all_known_categories_have_a_configured_color(): void
    {
        $expectedCategories = [
            'Gezondheid',
            'Onderwijs',
            'Op eigen benen staan',
            'Vrije tijd',
            'Welzijn',
            'Hulpverlening',
            'Noodgevallen',
            'Rechten',
            'Inspraak',
            'Klachten',
            'Overheid',
            'Digitale & Telefonische Hulp',
        ];

        $configuredCategories = array_keys(config('jongerenkaart.category_colors', []));

        $this->assertEqualsCanonicalizing($expectedCategories, $configuredCategories);
    }
}
