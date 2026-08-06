@extends('layouts.app')

@section('title', 'Jongerenkaart')

@section('content')
    <section class="home-hero">
        <div class="container home-hero-inner">
            <div class="home-hero-copy">
                <p class="eyebrow">De Jongerenkaart</p>
                <h3>Want je hoeft het niet alleen uit te zoeken.</h3>
                <p>Vertel waar je mee zit en vind laagdrempelige hulp in Vlaanderen en Brussel. Zoek op woorden zoals stress, wonen, school, geld, klacht of noodhulp.</p>
                <form method="GET" action="{{ route('search') }}" class="home-search-form">
                    <input
                        type="search"
                        name="q"
                        placeholder="Waar zoek je hulp voor?"
                        aria-label="Waar zoek je hulp voor?"
                        autocomplete="off"
                    >
                    <button type="submit" class="btn btn-primary">Zoeken</button>
                </form>
            </div>

            <div class="home-hero-panel">
                <span>Europees noodnummer</span>
                <strong>112</strong>
                <span>Bel het Europese noodnummer bij acuut gevaar. De app 112 BE kan je locatie automatisch delen met de noodcentrale.</span>
                <div class="home-emergency-actions">
                    <a href="https://112.be/nl" target="_blank" rel="noopener noreferrer" class="btn btn-outline-primary btn-sm">Info over 112</a>
                    <a href="https://112.be/nl/mobiele-applicatie" target="_blank" rel="noopener noreferrer" class="btn btn-primary btn-sm">App 112 BE</a>
                </div>
            </div>
        </div>
    </section>

    <section class="container home-section home-location-section">
        <a href="{{ route('map') }}" class="home-location-card">
            <span class="home-location-icon" aria-hidden="true">
                <i class="fa-solid fa-location-dot"></i>
            </span>
            <span class="home-location-copy">
                <strong>Zoek organisaties via locatie</strong>
                <small>Vind hulp in je buurt of rond een plaats waar je vaak bent.</small>
            </span>
            <span class="home-location-action">Zoeken</span>
        </a>
    </section>

    <section class="container home-section">
        <div class="section-heading">
            <div>
                <h2>Zoek per categorie</h2>
            </div>
            <a href="{{ route('search') }}" class="btn btn-outline-primary btn-sm">Alle organisaties</a>
        </div>

        <div class="map-filter-bar home-filter-bar">
            @foreach ($categories as $category)
                <x-search.filter-pill
                    :label="$category['label']"
                    :href="route('search', ['categorie' => $category['label']])"
                    :color="$category['color']"
                />
            @endforeach
        </div>
    </section>

    <section class="container home-section">
        <div class="section-heading">
            <div>
                <h2>Start met een herkenbare vraag</h2>
            </div>
        </div>

        <div class="home-guides">
            <a href="{{ route('search', ['q' => 'vrienden ontmoeten']) }}" class="home-guide-card" style="--guide-color: {{ config('jongerenkaart.category_colors')['Vrije tijd'] ?? '#8b5cf6' }}">
                <span>Vrienden ontmoeten</span>
                <small>Vrije tijd, ontmoeting en activiteiten</small>
            </a>
            <a href="{{ route('search', ['q' => 'stress']) }}" class="home-guide-card" style="--guide-color: {{ config('jongerenkaart.category_colors')['Gezondheid'] ?? '#3b82f6' }}">
                <span>Stress</span>
                <small>Luisteren, mentale steun en begeleiding</small>
            </a>
            <a href="{{ route('search', ['q' => 'hulp studeren']) }}" class="home-guide-card" style="--guide-color: {{ config('jongerenkaart.category_colors')['Onderwijs'] ?? '#10b981' }}">
                <span>Hulp studeren</span>
                <small>School, leren en studiebegeleiding</small>
            </a>
        </div>
    </section>

    <section class="container home-ls-section" id="home-ls-section" hidden>
        <div class="home-ls-grid" id="home-ls-grid"></div>
    </section>

    <script>
        (function () {
            const favoritesKey = 'jongerenkaart.favoriteOrganisaties';
            const favoriteItemsKey = 'jongerenkaart.favoriteOrganisatieItems';
            const recentItemsKey = 'jongerenkaart.recentOrganisaties';
            const recentLimit = 5;
            let favorites = [];
            let recent = [];

            try { favorites = Object.values(favoriteItemMap()); } catch (error) {}
            try { recent = JSON.parse(localStorage.getItem(recentItemsKey) || '[]').slice(0, recentLimit); } catch (error) {}

            if (favorites.length === 0 && recent.length === 0) {
                return;
            }

            document.querySelector('#home-ls-section').hidden = false;
            renderPanels();

            document.querySelector('#home-ls-grid').addEventListener('click', (event) => {
                const clearRecentButton = event.target.closest('[data-clear-recent]');

                if (clearRecentButton) {
                    localStorage.removeItem(recentItemsKey);
                    recent = [];
                    renderPanels();
                    return;
                }

                const button = event.target.closest('[data-unfavorite-id]');

                if (!button) {
                    return;
                }

                const id = button.dataset.unfavoriteId;
                const favoriteItems = favoriteItemMap();
                const favoriteIds = new Set(JSON.parse(localStorage.getItem(favoritesKey) || '[]'));

                delete favoriteItems[id];
                favoriteIds.delete(id);
                localStorage.setItem(favoriteItemsKey, JSON.stringify(favoriteItems));
                localStorage.setItem(favoritesKey, JSON.stringify(Array.from(favoriteIds)));
                favorites = Object.values(favoriteItems);
                renderPanels();
            });

            function renderPanels() {
                if (favorites.length === 0 && recent.length === 0) {
                    document.querySelector('#home-ls-section').hidden = true;
                    return;
                }

                document.querySelector('#home-ls-grid').innerHTML =
                    panel('Favorieten', favorites, true) +
                    panel('Recent bekeken', recent, false, true);
            }

            function panel(title, items, canUnfavorite, canClearRecent = false) {
                let html = `<div class="home-ls-panel">
                    <div class="home-ls-panel-heading">
                        <h2>${escapeHtml(title)}</h2>
                        ${canClearRecent && items.length ? '<button type="button" class="ls-clear-recent" data-clear-recent>Verwijder recent bekeken</button>' : ''}
                    </div>`;

                if (!items.length) {
                    html += '<p class="ls-empty">Nog niets om te tonen.</p>';
                } else {
                    items.forEach((organisatie) => {
                        html += `
                            <div class="ls-row">
                                <a href="${escapeHtml(organisatie.url)}" class="ls-row-link">
                                    <span class="ls-card-name">${escapeHtml(organisatie.name)}</span>
                                    ${organisatie.meta ? `<span class="ls-card-meta">${escapeHtml(organisatie.meta)}</span>` : ''}
                                </a>
                                ${canUnfavorite ? `
                                    <button type="button" class="ls-unfavorite" data-unfavorite-id="${escapeHtml(organisatie.id)}" aria-label="Verwijder ${escapeHtml(organisatie.name)} uit favorieten">
                                        <i class="fa-solid fa-star" aria-hidden="true"></i>
                                    </button>
                                ` : ''}
                            </div>
                        `;
                    });
                }

                return `${html}</div>`;
            }

            function favoriteItemMap() {
                try {
                    return JSON.parse(localStorage.getItem(favoriteItemsKey) || '{}');
                } catch (error) {
                    return {};
                }
            }

            function escapeHtml(value) {
                return String(value ?? '')
                    .replaceAll('&', '&amp;')
                    .replaceAll('<', '&lt;')
                    .replaceAll('>', '&gt;')
                    .replaceAll('"', '&quot;')
                    .replaceAll("'", '&#039;');
            }
        })();
    </script>
@endsection
