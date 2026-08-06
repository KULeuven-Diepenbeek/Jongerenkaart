<?php

namespace App\Http\Controllers;

use App\Models\ContentPage;
use App\Models\Medewerker;
use App\Services\InfoPageContentService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Blade;
use Symfony\Component\HttpFoundation\Response;

class AdminContentPageController extends Controller
{
    public function __construct(
        private readonly InfoPageContentService $infoPageContent,
    ) {}

    public function edit(string $slug): View
    {
        abort_unless(array_key_exists($slug, ContentPage::editablePages()), Response::HTTP_NOT_FOUND);

        return view('admin.content-page-edit', [
            'slug' => $slug,
            'label' => ContentPage::editablePages()[$slug],
            'contentPage' => ContentPage::findBySlug($slug),
            'editorHtml' => $this->editorHtml($slug),
        ]);
    }

    public function update(Request $request, string $slug): RedirectResponse
    {
        abort_unless(array_key_exists($slug, ContentPage::editablePages()), Response::HTTP_NOT_FOUND);

        $data = $request->validate([
            'html' => ['nullable', 'string'],
        ]);

        $html = trim((string) ($data['html'] ?? ''));

        if ($slug === 'info') {
            $html = $this->infoPageContent->editableHtml($html);
        }

        ContentPage::query()->updateOrCreate([
            'slug' => $slug,
        ], [
            'title' => ContentPage::editablePages()[$slug],
            'html' => $html,
            'updated_by' => (string) $request->user()->getKey(),
        ]);

        return redirect()
            ->route($slug)
            ->with('status', 'Pagina opgeslagen.');
    }

    public function reset(string $slug): RedirectResponse
    {
        abort_unless(array_key_exists($slug, ContentPage::editablePages()), Response::HTTP_NOT_FOUND);

        ContentPage::query()->where('slug', $slug)->delete();

        return redirect()
            ->route($slug)
            ->with('status', 'Pagina gebruikt opnieuw de standaardinhoud.');
    }

    private function editorHtml(string $slug): string
    {
        $contentPage = ContentPage::findBySlug($slug);

        if (! empty($contentPage?->html)) {
            return $slug === 'info'
                ? $this->infoPageContent->editableHtml($contentPage->html)
                : $contentPage->html;
        }

        return $this->defaultRenderedHtml($slug);
    }

    private function defaultRenderedHtml(string $slug): string
    {
        $path = resource_path('views/pages/'.$slug.'.blade.php');
        $source = file_get_contents($path) ?: '';
        $start = strpos($source, '@else');
        $end = strrpos($source, '@endif');

        if ($start === false || $end === false || $end <= $start) {
            return '';
        }

        $blade = trim(substr($source, $start + strlen('@else'), $end - ($start + strlen('@else'))));

        return trim(Blade::render($blade, [
            'contentPage' => null,
            'medewerkers' => $slug === 'info' ? Medewerker::socialeKaartTeam() : collect(),
        ]));
    }
}
