@extends('layouts.app')

@section('title', 'Zoeken')

@section('content')
    <section class="search-page">
        <div class="search-shell">
            <div class="search-heading">
                <h1>Organisaties zoeken</h1>
                <p>Zoek op naam van een organisatie, categorie of beschrijving. Gebruik eventuele filters om de resultaten te verfijnen. Gebruik kernwoorden om te zoeken.</p>
            </div>

            <div class="search-input-wrap">
                <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
                <input
                    id="organisatie-search"
                    type="search"
                    placeholder="Waar zoek je hulp voor?"
                    autocomplete="off"
                    autofocus
                >
            </div>

            <div class="search-filter-row">
                <div class="search-category-list" aria-label="Categorie filters">
                    @foreach ($categories as $category)
                        <x-search.filter-pill
                            :label="$category['label']"
                            :color="$category['color']"
                            :data-cat="$category['label']"
                            aria-pressed="false"
                        />
                    @endforeach
                </div>

                @if ($extraFilters->isNotEmpty())
                    <div class="search-filter-actions">
                        <button
                            id="more-filters-toggle"
                            type="button"
                            class="more-filters-toggle"
                            aria-expanded="false"
                            aria-controls="more-filters-panel"
                        >
                            <i class="fa-solid fa-sliders" aria-hidden="true"></i>
                            <span>Meer filters</span>
                            <span id="more-filters-count" class="more-filters-count" hidden>0</span>
                        </button>
                        <button id="clear-search-filters" type="button" class="clear-search-filters" hidden>
                            Wis alle filters
                        </button>
                    </div>
                @endif
            </div>

            @if ($extraFilters->isNotEmpty())
                <div id="more-filters-panel" class="more-filters-panel" hidden>
                    <label class="extra-filter-field">
                        <span>Plaats</span>
                        <select id="search-place-filter">
                            <option value="">Alle</option>
                            @foreach ($places as $place)
                                <option value="{{ $place['key'] }}">{{ $place['label'] }}</option>
                            @endforeach
                        </select>
                    </label>

                    <label class="extra-filter-field">
                        <span>Afstand</span>
                        <select id="search-radius-filter">
                            @foreach ($radiusOptions as $radius)
                                <option value="{{ $radius }}" @selected($radius === 10)>{{ $radius }} km</option>
                            @endforeach
                        </select>
                    </label>

                    @foreach ($extraFilters as $filter)
                        <label class="extra-filter-field">
                            <span>{{ $filter['label'] }}</span>
                            @if (($filter['type'] ?? 'select') === 'number')
                                <input
                                    type="number"
                                    min="0"
                                    max="120"
                                    inputmode="numeric"
                                    placeholder="Alle"
                                    data-extra-filter="{{ $filter['field'] }}"
                                >
                            @elseif (($filter['type'] ?? 'select') === 'toggle')
                                @php $option = $filter['options'][0]; @endphp
                                <span class="extra-filter-toggle">
                                    <input
                                        type="checkbox"
                                        value="{{ $option['value'] }}"
                                        data-extra-filter="{{ $option['field'] }}"
                                    >
                                    <span>{{ $option['label'] }}</span>
                                </span>
                            @else
                                <select data-extra-filter="{{ $filter['field'] }}">
                                    <option value="">Alle</option>
                                    @foreach ($filter['options'] as $option)
                                        <option value="{{ $option['value'] }}" data-filter-field="{{ $option['field'] }}">{{ $option['label'] }}</option>
                                    @endforeach
                                </select>
                            @endif
                        </label>
                    @endforeach
                </div>
            @endif

            <div class="search-result-controls">
                <p id="search-status" class="search-status" aria-live="polite"></p>
                <button id="show-all-results-toggle" type="button" class="show-all-results-toggle" hidden>
                    Toon alle resultaten
                </button>
            </div>
            <ul id="search-results" class="search-results"></ul>
        </div>
    </section>

    <x-search.result-card-template />

    <script>
        const input = document.querySelector('#organisatie-search');
        const categoryButtons = document.querySelectorAll('[data-cat]');
        const moreFiltersToggle = document.querySelector('#more-filters-toggle');
        const moreFiltersPanel = document.querySelector('#more-filters-panel');
        const moreFiltersCount = document.querySelector('#more-filters-count');
        const clearFiltersButton = document.querySelector('#clear-search-filters');
        const placeFilter = document.querySelector('#search-place-filter');
        const radiusFilter = document.querySelector('#search-radius-filter');
        const extraFilterFields = document.querySelectorAll('[data-extra-filter]');
        const status = document.querySelector('#search-status');
        const showAllResultsToggle = document.querySelector('#show-all-results-toggle');
        const results = document.querySelector('#search-results');
        const resultTemplate = document.querySelector('#search-result-card-template');
        const categoryColors = @json($categoryColors);
        const reactiveSearchEnabled = @json($reactiveSearchEnabled);
        const initialParams = new URLSearchParams(window.location.search);
        const favoritesKey = 'jongerenkaart.favoriteOrganisaties';
        const favoriteItemsKey = 'jongerenkaart.favoriteOrganisatieItems';
        const recentItemsKey = 'jongerenkaart.recentOrganisaties';
        const collapsedDescriptionLength = 220;
        const initialVisibleResults = 5;
        const maximumReturnedResults = 100;
        let currentResults = [];
        let currentQuery = '';
        let showAllResults = false;
        let timer;
        let controller;

        input.value = initialParams.get('q') ?? '';
        placeFilter.value = initialParams.get('place') ?? '';
        radiusFilter.value = initialParams.get('radius') ?? radiusFilter.value;

        if (reactiveSearchEnabled) {
            input.addEventListener('input', queueSearch);
        }

        input.addEventListener('keydown', (event) => {
            if (event.key !== 'Enter') {
                return;
            }

            event.preventDefault();
            clearTimeout(timer);
            search();
        });
        placeFilter.addEventListener('change', () => {
            updateExtraFilterState();
            queueSearch();
        });
        radiusFilter.addEventListener('change', () => {
            updateExtraFilterState();
            queueSearch();
        });

        categoryButtons.forEach((button) => {
            if (initialValues('categorie').includes(button.dataset.cat)) {
                button.classList.add('active');
                button.setAttribute('aria-pressed', 'true');
            }

            button.addEventListener('click', () => {
                const isActive = button.classList.toggle('active');
                button.setAttribute('aria-pressed', String(isActive));
                updateCategoryState();
                queueSearch();
            });
        });

        extraFilterFields.forEach((field) => {
            const eventName = field.matches('input[type="number"]') ? 'input' : 'change';
            const values = initialValues(field.dataset.extraFilter);

            if (values.length) {
                if (field.matches('input[type="checkbox"]')) {
                    field.checked = values.includes(field.value);
                } else {
                    field.value = values[0];
                }
            }

            field.addEventListener(eventName, () => {
                updateExtraFilterState();
                queueSearch();
            });
        });

        if (moreFiltersToggle && moreFiltersPanel) {
            moreFiltersToggle.addEventListener('click', () => {
                const willOpen = moreFiltersPanel.hidden;

                moreFiltersPanel.hidden = !willOpen;
                moreFiltersToggle.setAttribute('aria-expanded', String(willOpen));
            });
        }

        clearFiltersButton.addEventListener('click', clearAllFilters);

        showAllResultsToggle.addEventListener('click', () => {
            showAllResults = !showAllResults;
            renderResults();
        });

        updateCategoryState();
        updateExtraFilterState();

        if (input.value.trim() || selectedCategories().length || selectedExtraFilters().length || selectedPlace()) {
            search();
        }

        function queueSearch() {
            clearTimeout(timer);
            updateClearFiltersButton();
            timer = setTimeout(search, 180);
        }

        async function search() {
            const query = input.value.trim();
            showAllResults = false;
            currentQuery = query;
            currentResults = [];
            results.innerHTML = '';
            showAllResultsToggle.hidden = true;

            if (!query && !selectedCategories().length && !selectedExtraFilters().length && !selectedPlace()) {
                status.textContent = '';
                return;
            }

            if (controller) {
                controller.abort();
            }

            controller = new AbortController();
            status.textContent = 'Zoeken...';

            const params = new URLSearchParams({ q: query });
            selectedCategories().forEach((category) => params.append('categorie[]', category));
            selectedExtraFilters().forEach(({ field, value }) => params.append(`${field}[]`, value));
            if (selectedPlace()) {
                params.set('place', selectedPlace());
                params.set('radius', selectedRadius());
            }

            try {
                const response = await fetch(`{{ route('search.results') }}?${params.toString()}`, {
                    signal: controller.signal,
                    headers: { Accept: 'application/json' },
                });

                const data = await response.json();
                currentResults = data;
                renderResults();
            } catch (error) {
                if (error.name !== 'AbortError') {
                    status.textContent = 'Zoeken is mislukt';
                }
            }
        }

        results.addEventListener('click', (event) => {
            const favoriteButton = event.target.closest('[data-favorite-button]');
            const descriptionToggle = event.target.closest('[data-description-toggle]');
            const card = event.target.closest('[data-organisatie-url]');

            if (favoriteButton) {
                toggleFavorite(favoriteButton);
                return;
            }

            if (descriptionToggle) {
                toggleDescription(descriptionToggle);
                return;
            }

            if (event.target.closest('a, button, input, select, textarea')) {
                return;
            }

            if (card) {
                storeRecent(card);
                window.location.href = card.dataset.organisatieUrl;
            }
        });

        results.addEventListener('keydown', (event) => {
            const card = event.target.closest('[data-organisatie-url]');

            if (!card || event.target.closest('a, button, input, select, textarea')) {
                return;
            }

            if (event.key === 'Enter') {
                storeRecent(card);
                window.location.href = card.dataset.organisatieUrl;
            }
        });

        function initialValues(name) {
            return [
                ...initialParams.getAll(name),
                ...initialParams.getAll(`${name}[]`),
                ...Array.from(initialParams.entries())
                    .filter(([key]) => key.startsWith(`${name}[`))
                    .map(([, value]) => value),
            ].filter(Boolean);
        }

        function selectedCategories() {
            return Array.from(document.querySelectorAll('[data-cat].active'))
                .map((button) => button.dataset.cat)
                .filter(Boolean);
        }

        function updateCategoryState() {
            const hasSelection = selectedCategories().length > 0;

            categoryButtons.forEach((button) => {
                button.classList.toggle('is-filtered-out', hasSelection && !button.classList.contains('active'));
            });
        }

        function selectedExtraFilters() {
            return Array.from(extraFilterFields)
                .filter(extraFilterIsActive)
                .map((field) => {
                    const selectedOption = field.selectedOptions ? field.selectedOptions[0] : null;

                    return {
                        field: selectedOption?.dataset.filterField || field.dataset.extraFilter,
                        value: field.value,
                    };
                });
        }

        function extraFilterIsActive(field) {
            return field.matches('input[type="checkbox"]') ? field.checked : Boolean(field.value);
        }

        function updateExtraFilterState() {
            if (!moreFiltersToggle || !moreFiltersCount) {
                return;
            }

            const activeCount = selectedExtraFilters().length;
            const locationCount = selectedPlace() ? 1 : 0;
            const totalCount = activeCount + locationCount;
            moreFiltersToggle.classList.toggle('has-active-filters', totalCount > 0);
            moreFiltersCount.hidden = totalCount === 0;
            moreFiltersCount.textContent = totalCount;
            clearFiltersButton.hidden = !hasAnySearchState();
        }

        function updateClearFiltersButton() {
            clearFiltersButton.hidden = !hasAnySearchState();
        }

        function selectedPlace() {
            return placeFilter.value;
        }

        function selectedRadius() {
            return radiusFilter.value || '10';
        }

        function hasAnySearchState() {
            return input.value.trim() !== ''
                || selectedCategories().length > 0
                || selectedExtraFilters().length > 0
                || selectedPlace() !== '';
        }

        function clearAllFilters() {
            input.value = '';
            placeFilter.value = '';
            radiusFilter.value = '10';

            categoryButtons.forEach((button) => {
                button.classList.remove('active', 'is-filtered-out');
                button.setAttribute('aria-pressed', 'false');
            });

            extraFilterFields.forEach((field) => {
                if (field.matches('input[type="checkbox"]')) {
                    field.checked = false;
                } else {
                    field.value = '';
                }
            });

            currentResults = [];
            currentQuery = '';
            showAllResults = false;
            results.innerHTML = '';
            status.textContent = '';
            showAllResultsToggle.hidden = true;
            updateCategoryState();
            updateExtraFilterState();
        }

        function renderResults() {
            const fragment = document.createDocumentFragment();
            const visibleResults = showAllResults ? currentResults : currentResults.slice(0, initialVisibleResults);

            status.textContent = statusText();
            showAllResultsToggle.hidden = currentResults.length <= initialVisibleResults;
            showAllResultsToggle.textContent = showAllResults ? 'Toon minder resultaten' : 'Toon alle resultaten';
            showAllResultsToggle.setAttribute('aria-pressed', String(showAllResults));

            visibleResults.forEach((organisatie) => {
                fragment.appendChild(createResultCard(organisatie, currentQuery));
            });

            results.replaceChildren(fragment);
        }

        function statusText() {
            if (!currentResults.length) {
                return 'Geen resultaten';
            }

            if (currentResults.length <= initialVisibleResults || showAllResults) {
                return currentResults.length >= maximumReturnedResults
                    ? `Enkel eerste ${maximumReturnedResults} resultaten worden getoond. Verfijn je zoekopdracht voor betere resultaten.`
                    : `${currentResults.length} resultaten`;
            }

            return currentResults.length >= maximumReturnedResults
                ? `Top ${initialVisibleResults} van de eerste ${maximumReturnedResults} resultaten. Verfijn je zoekopdracht voor betere resultaten.`
                : `Top ${initialVisibleResults} van ${currentResults.length} resultaten`;
        }

        function createResultCard(organisatie, query) {
            const card = resultTemplate.content.firstElementChild.cloneNode(true);
            const id = String(organisatie.id ?? '');
            const description = String(organisatie.description ?? '');
            const isLongDescription = description.length > collapsedDescriptionLength;

            card.dataset.organisatieId = id;
            card.dataset.organisatieUrl = organisatie.url ?? `/organisaties/${encodeURIComponent(id)}`;
            card.dataset.organisatieName = organisatie.name ?? 'Naam onbekend';
            card.dataset.organisatieMeta = [organisatie.category, organisatie.address].filter(Boolean).join(' - ');
            card.setAttribute('aria-label', `Bekijk details van ${organisatie.name ?? 'organisatie'}`);
            card.style.setProperty('--card-color', colorForCategory(organisatie.category));
            card.querySelector('[data-result-name]').innerHTML = highlight(organisatie.name ?? 'Naam onbekend', query);
            card.querySelector('[data-result-category]').innerHTML = highlight(organisatie.category ?? 'Categorie onbekend', query);
            card.querySelector('[data-result-free-badge]').hidden = !organisatie.isFree;
            card.querySelector('[data-result-anonymous-badge]').hidden = !organisatie.isAnonymous;
            const distanceBadge = card.querySelector('[data-result-distance]');
            if (organisatie.distance !== null && organisatie.distance !== undefined) {
                distanceBadge.textContent = `${organisatie.distance} km`;
                distanceBadge.hidden = false;
            }

            const addressLink = card.querySelector('[data-result-address-link]');

            if (organisatie.address && organisatie.addressUrl) {
                addressLink.href = organisatie.addressUrl;
                card.querySelector('[data-result-address]').textContent = organisatie.address;
            } else {
                addressLink.remove();
            }

            const contact = card.querySelector('[data-result-contact]');
            const emailLink = card.querySelector('[data-result-email-link]');

            if (organisatie.email) {
                emailLink.href = `mailto:${organisatie.email}`;
                card.querySelector('[data-result-email]').textContent = organisatie.email;
            } else {
                contact.remove();
            }

            const descriptionElement = card.querySelector('[data-result-description]');
            descriptionElement.innerHTML = highlight(description, query);

            const descriptionToggle = card.querySelector('[data-description-toggle]');

            if (isLongDescription) {
                descriptionElement.classList.add('is-collapsed');
                descriptionToggle.hidden = false;
            }

            updateFavoriteButton(card.querySelector('[data-favorite-button]'), id);

            return card;
        }

        function colorForCategory(category) {
            return categoryColors[category] ?? '#6b7280';
        }

        function toggleDescription(button) {
            const card = button.closest('[data-organisatie-id]');
            const description = card.querySelector('[data-result-description]');
            const willExpand = description.classList.contains('is-collapsed');

            description.classList.toggle('is-collapsed', !willExpand);
            button.textContent = willExpand ? 'Lees minder' : 'Lees meer';
        }

        function toggleFavorite(button) {
            const card = button.closest('[data-organisatie-id]');
            const id = card.dataset.organisatieId;
            const favorites = favoriteIds();
            const favoriteItems = favoriteItemMap();

            if (favorites.has(id)) {
                favorites.delete(id);
                delete favoriteItems[id];
            } else {
                favorites.add(id);
                favoriteItems[id] = cardSummary(card);
            }

            localStorage.setItem(favoritesKey, JSON.stringify(Array.from(favorites)));
            localStorage.setItem(favoriteItemsKey, JSON.stringify(favoriteItems));
            updateFavoriteButton(button, id);
        }

        function updateFavoriteButton(button, id) {
            const isFavorite = favoriteIds().has(id);
            const icon = button.querySelector('i');

            button.classList.toggle('is-favorite', isFavorite);
            button.setAttribute('aria-pressed', String(isFavorite));
            icon.classList.toggle('fa-regular', !isFavorite);
            icon.classList.toggle('fa-solid', isFavorite);
        }

        function favoriteIds() {
            try {
                return new Set(JSON.parse(localStorage.getItem(favoritesKey) ?? '[]'));
            } catch {
                return new Set();
            }
        }

        function favoriteItemMap() {
            try {
                return JSON.parse(localStorage.getItem(favoriteItemsKey) ?? '{}');
            } catch {
                return {};
            }
        }

        function storeRecent(card) {
            const summary = cardSummary(card);
            let items = [];

            try {
                items = JSON.parse(localStorage.getItem(recentItemsKey) ?? '[]');
            } catch {
                items = [];
            }

            items = [summary, ...items.filter((item) => item.id !== summary.id)].slice(0, 6);
            localStorage.setItem(recentItemsKey, JSON.stringify(items));
        }

        function cardSummary(card) {
            return {
                id: card.dataset.organisatieId,
                name: card.dataset.organisatieName,
                meta: card.dataset.organisatieMeta,
                url: card.dataset.organisatieUrl,
            };
        }

        function highlight(value, query) {
            const text = escapeHtml(value);
            const needle = escapeHtml(query);

            if (!needle) {
                return text;
            }

            return text.replace(new RegExp(escapeRegExp(needle), 'gi'), (match) => `<strong class="match">${match}</strong>`);
        }

        function escapeRegExp(value) {
            return String(value).replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        }

        function escapeHtml(value) {
            return String(value)
                .replaceAll('&', '&amp;')
                .replaceAll('<', '&lt;')
                .replaceAll('>', '&gt;')
                .replaceAll('"', '&quot;')
                .replaceAll("'", '&#039;');
        }
    </script>
@endsection
