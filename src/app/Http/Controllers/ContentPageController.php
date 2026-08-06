<?php

namespace App\Http\Controllers;

use App\Models\ContentPage;
use App\Models\Medewerker;
use App\Services\InfoPageContentService;
use Illuminate\Contracts\View\View;

class ContentPageController extends Controller
{
    public function __construct(
        private readonly InfoPageContentService $infoPageContent,
    ) {}

    public function info(): View
    {
        $contentPage = ContentPage::findBySlug('info');

        return view('pages.info', [
            'contentPage' => $contentPage,
            'contentHtml' => $this->infoPageContent->editableHtml($contentPage?->html),
            'medewerkers' => Medewerker::socialeKaartTeam(),
        ]);
    }

    public function privacy(): View
    {
        return view('pages.privacy', [
            'contentPage' => ContentPage::findBySlug('privacy'),
        ]);
    }

    public function faq(): View
    {
        return view('pages.faq', [
            'contentPage' => ContentPage::findBySlug('faq'),
        ]);
    }
}
