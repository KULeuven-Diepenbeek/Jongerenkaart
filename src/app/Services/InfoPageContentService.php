<?php

namespace App\Services;

class InfoPageContentService
{
    public function editableHtml(?string $html): string
    {
        $html = trim((string) $html);

        if ($html === '' || stripos($html, 'Team Sociale Kaart') === false) {
            return $html;
        }

        $sectionStarts = collect([
            strripos($html, '<div class="padding-side contact">'),
            strripos($html, "<div class='padding-side contact'>"),
        ])->filter(fn (int|false $position) => $position !== false);

        if ($sectionStarts->isEmpty()) {
            return $html;
        }

        return rtrim(substr($html, 0, $sectionStarts->max()));
    }
}
