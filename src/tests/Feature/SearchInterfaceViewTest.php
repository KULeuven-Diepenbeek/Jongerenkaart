<?php

namespace Tests\Feature;

use Tests\TestCase;

class SearchInterfaceViewTest extends TestCase
{
    public function test_search_view_contains_anonymous_filter_and_result_badge(): void
    {
        $this->view('pages.search', [
            'categories' => collect([
                ['label' => 'Gezondheid', 'color' => '#3b82f6'],
            ]),
            'extraFilters' => collect([[
                'field' => 'anoniem',
                'label' => 'Anoniem',
                'type' => 'toggle',
                'options' => [[
                    'label' => 'Enkel anonieme hulp',
                    'value' => 'true',
                    'field' => 'anoniem',
                ]],
            ]]),
            'places' => collect(),
            'radiusOptions' => [1, 3, 5, 10],
            'categoryColors' => ['Gezondheid' => '#3b82f6'],
            'reactiveSearchEnabled' => false,
        ])
            ->assertSee('Enkel anonieme hulp')
            ->assertSee('data-extra-filter="anoniem"', false)
            ->assertSee('data-result-anonymous-badge', false)
            ->assertSee('Wis alle filters');
    }

    public function test_map_view_contains_anonymous_filter_and_small_radius_options(): void
    {
        $this->view('pages.map', [
            'categories' => collect([
                ['label' => 'Gezondheid', 'color' => '#3b82f6'],
            ]),
            'categoryColors' => ['Gezondheid' => '#3b82f6'],
            'places' => collect(),
            'radiusOptions' => [1, 3, 5, 10, 80],
            'reactiveSearchEnabled' => false,
        ])
            ->assertSee('id="map-anonymous-filter"', false)
            ->assertSee('<option value="1"', false)
            ->assertSee('<option value="3"', false)
            ->assertSee('<option value="80"', false);
    }

    public function test_home_view_renders_new_category_pills(): void
    {
        $this->view('home', [
            'categories' => collect([
                ['label' => 'Hulpverlening', 'color' => '#0f766e'],
                ['label' => 'Noodgevallen', 'color' => '#b91c1c'],
                ['label' => 'Digitale & Telefonische Hulp', 'color' => '#334155'],
            ]),
        ])
            ->assertSee('Hulpverlening')
            ->assertSee('Noodgevallen')
            ->assertSee('Digitale &amp; Telefonische Hulp', false);
    }
}
