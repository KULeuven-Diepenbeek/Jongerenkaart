<?php

namespace App\Http\Controllers;

use App\Services\OrganisatieFilterOptionsService;
use Illuminate\Contracts\View\View;

class HomePageController extends Controller
{
    public function __invoke(OrganisatieFilterOptionsService $filterOptions): View
    {
        $categoryColors = config('jongerenkaart.category_colors', []);
        $categories = $filterOptions->get()['categorie'] ?? [];

        return view('home', [
            'categories' => collect($categories)
                ->map(fn (string $category) => [
                    'label' => $category,
                    'color' => $categoryColors[$category] ?? '#6b7280',
                ])
                ->values(),
        ]);
    }
}
