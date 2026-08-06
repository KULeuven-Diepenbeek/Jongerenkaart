<?php

namespace Tests\Feature;

use Tests\TestCase;

class AdminStatisticsViewTest extends TestCase
{
    public function test_statistics_view_renders_without_a_snapshot(): void
    {
        $this->view('admin.statistics', ['snapshot' => null])
            ->assertSee('Er is nog geen momentopname.')
            ->assertSee('Dashboard bijwerken');
    }
}
