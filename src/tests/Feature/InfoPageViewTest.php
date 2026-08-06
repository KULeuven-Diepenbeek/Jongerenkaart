<?php

namespace Tests\Feature;

use App\Models\ContentPage;
use App\Models\Medewerker;
use Tests\TestCase;

class InfoPageViewTest extends TestCase
{
    public function test_managed_employees_render_below_custom_info_content(): void
    {
        $contentPage = new ContentPage([
            'slug' => 'info',
            'html' => '<section>Opgeslagen inhoud</section>',
        ]);
        $medewerker = new Medewerker([
            'regio' => 'Regio Test',
            'naam' => 'Actuele Medewerker',
            'email' => 'actueel@example.test',
        ]);

        $this->view('pages.info', [
            'contentPage' => $contentPage,
            'contentHtml' => $contentPage->html,
            'medewerkers' => collect([$medewerker]),
        ])
            ->assertSee('Opgeslagen inhoud')
            ->assertSee('Team Sociale Kaart')
            ->assertSee('Actuele Medewerker')
            ->assertSee('actueel@example.test');
    }
}
