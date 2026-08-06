<?php

namespace Tests\Unit;

use App\Services\InfoPageContentService;
use PHPUnit\Framework\TestCase;

class InfoPageContentServiceTest extends TestCase
{
    public function test_legacy_static_team_section_is_removed_from_editable_html(): void
    {
        $html = <<<'HTML'
<section><h1>Bewerkbare info</h1></section>
<div class="padding-side contact">
    <h1>Team Sociale Kaart</h1>
    <div class="contact-card">Oude medewerker</div>
</div>
HTML;

        $result = (new InfoPageContentService)->editableHtml($html);

        $this->assertStringContainsString('Bewerkbare info', $result);
        $this->assertStringNotContainsString('Team Sociale Kaart', $result);
        $this->assertStringNotContainsString('Oude medewerker', $result);
    }

    public function test_unrelated_custom_html_is_preserved(): void
    {
        $html = '<section><h1>Eigen inhoud</h1><p>Vrije tekst</p></section>';

        $this->assertSame(
            $html,
            (new InfoPageContentService)->editableHtml($html),
        );
    }
}
