@extends('layouts.app')

@section('title', 'Kaart')

@push('head')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css">
@endpush

@section('content')
    <section class="map-page">
        <div class="map-heading">
            <h1>Zoek organisaties via locatie</h1>
            <p>Kies een plaats en afstand. De kaart toont organisaties binnen die radius, gefilterd op categorie.</p>
        </div>

        <div class="map-toolbar">
            <label class="map-field">
                <span>Plaats</span>
                <select id="map-place-select">
                    <option value="">Kies een plaats</option>
                    @foreach ($places as $place)
                        <option value="{{ $place['key'] }}">{{ $place['label'] }}</option>
                    @endforeach
                </select>
            </label>

            <label class="map-field">
                <span>Afstand</span>
                <select id="map-radius-select">
                    @foreach ($radiusOptions as $radius)
                        <option value="{{ $radius }}" @selected($radius === 10)>{{ $radius }} km</option>
                    @endforeach
                </select>
            </label>

            <label class="map-field map-search-field">
                <span>Zoekterm</span>
                <div class="map-search-input-wrap">
                    <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
                    <input id="map-search-input" type="search" placeholder="Waar zoek je hulp voor?" autocomplete="off">
                </div>
            </label>

            <button id="map-pin-mode" type="button" class="map-pin-mode">
                <i class="fa-solid fa-location-crosshairs" aria-hidden="true"></i>
                Plaats zelf een pin
            </button>
        </div>

        <div class="map-filter-bar" aria-label="Categorie filters">
            @foreach ($categories as $category)
                <x-search.filter-pill
                    :label="$category['label']"
                    :color="$category['color']"
                    :data-cat="$category['label']"
                    aria-pressed="false"
                />
            @endforeach
            <label class="map-anonymous-filter">
                <input id="map-anonymous-filter" type="checkbox">
                <span>Anonieme hulp</span>
            </label>
        </div>

        <div class="map-layout">
            <div id="organisaties-map" class="organisaties-map" aria-label="Kaart met organisaties"></div>

            <aside class="map-results-panel" aria-live="polite">
                <div class="map-results-heading">
                    <h2>Organisaties in de buurt</h2>
                    <p id="map-status">Kies een plaats om te starten.</p>
                </div>
                <div id="map-results" class="map-results"></div>
            </aside>
        </div>
    </section>
@endsection

@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js"></script>
    <script>
        const categoryColors = @json($categoryColors);
        const reactiveSearchEnabled = @json($reactiveSearchEnabled);
        const placeSelect = document.querySelector('#map-place-select');
        const radiusSelect = document.querySelector('#map-radius-select');
        const mapSearchInput = document.querySelector('#map-search-input');
        const pinModeButton = document.querySelector('#map-pin-mode');
        const status = document.querySelector('#map-status');
        const resultsPanel = document.querySelector('#map-results');
        const categoryButtons = document.querySelectorAll('[data-cat]');
        const anonymousFilter = document.querySelector('#map-anonymous-filter');
        const selectedMarkerColor = '#111827';
        let customPlace = null;
        let pinMode = false;
        let searchTimer = null;
        let selectedPlaceMarker = null;
        let radiusCircle = null;
        let selectedPin = null;
        const pins = new Map();
        const categoryClusterGroups = new Map();

        const map = L.map('organisaties-map', {
            scrollWheelZoom: true,
        }).setView([50.85, 4.35], 9);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
            maxZoom: 18,
        }).addTo(map);

        placeSelect.addEventListener('change', () => {
            if (placeSelect.value !== 'custom') {
                customPlace = null;
                placeSelect.querySelector('option[value="custom"]')?.remove();
            }

            loadResults();
        });
        radiusSelect.addEventListener('change', loadResults);
        anonymousFilter.addEventListener('change', loadResults);
        if (reactiveSearchEnabled) {
            mapSearchInput.addEventListener('input', queueLoadResults);
        }

        mapSearchInput.addEventListener('keydown', (event) => {
            if (event.key !== 'Enter') {
                return;
            }

            event.preventDefault();
            clearTimeout(searchTimer);
            loadResults();
        });
        pinModeButton.addEventListener('click', () => {
            pinMode = !pinMode;
            pinModeButton.classList.toggle('active', pinMode);
            pinModeButton.setAttribute('aria-pressed', String(pinMode));
            status.textContent = pinMode ? 'Klik op de kaart om zelf een locatie te kiezen.' : 'Kies een plaats om te starten.';
        });
        map.on('click', (event) => {
            if (!pinMode) {
                return;
            }

            customPlace = {
                key: 'custom',
                label: 'Gekozen locatie',
                lat: event.latlng.lat,
                lon: event.latlng.lng,
            };
            setCustomPlaceOption();
            pinMode = false;
            pinModeButton.classList.remove('active');
            pinModeButton.setAttribute('aria-pressed', 'false');
            loadResults();
        });

        categoryButtons.forEach((button) => {
            button.addEventListener('click', () => {
                const isActive = button.classList.toggle('active');
                button.setAttribute('aria-pressed', String(isActive));
                updateCategoryState();
                loadResults();
            });
        });

        async function loadResults() {
            const place = selectedPlace();

            clearMap();

            if (!place) {
                status.textContent = 'Kies een plaats om te starten.';
                resultsPanel.innerHTML = '';
                return;
            }

            status.textContent = 'Organisaties laden...';

            const params = new URLSearchParams({
                radius: radiusSelect.value,
            });
            const query = mapSearchInput.value.trim();

            if (query) {
                params.set('q', query);
            }

            if (place.key === 'custom') {
                params.set('lat', place.lat);
                params.set('lon', place.lon);
                params.set('label', place.label);
            } else {
                params.set('place', place.key);
            }

            selectedCategories().forEach((category) => params.append('categorie[]', category));
            if (anonymousFilter.checked) {
                params.set('anoniem', 'true');
            }

            try {
                const response = await fetch(`${@json(route('map.results'))}?${params.toString()}`, {
                    headers: { Accept: 'application/json' },
                });
                const data = await response.json();

                renderArea(data.place, data.radius);
                renderPins(data.results);
                renderResults(data.results);
                fitMap(data.place, data.results);
                status.textContent = `${data.results.length} organisaties binnen ${data.radius} km`;
            } catch (error) {
                status.textContent = 'Organisaties laden is mislukt.';
            }
        }

        function queueLoadResults() {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(loadResults, 220);
        }

        function renderArea(place, radius) {
            if (!place) {
                return;
            }

            selectedPlaceMarker = L.marker([place.lat, place.lon], {
                icon: selectedPlaceIcon(),
                zIndexOffset: 1000,
            }).addTo(map).bindPopup(`<strong>${escapeHtml(place.label)}</strong>`);

            radiusCircle = L.circle([place.lat, place.lon], {
                radius: Number(radius) * 1000,
                color: 'var(--jk-primary)',
                weight: 2,
                fillColor: 'var(--jk-primary)',
                fillOpacity: .08,
            }).addTo(map);
        }

        function renderPins(organisaties) {
            organisaties.forEach((organisatie) => {
                const category = organisatie.category ?? 'Andere';
                const color = colorForCategory(category);
                const marker = L.marker([organisatie.lat, organisatie.lon], {
                    icon: categoryIcon(color),
                    categoryColor: color,
                });

                marker.bindPopup(popupHtml(organisatie), {
                    maxWidth: 280,
                    closeButton: true,
                    autoPan: true,
                });

                marker.on('click', () => {
                    highlightOrganisation(organisatie.id, true);
                    marker.openPopup();
                });
                clusterGroupForCategory(category, color).addLayer(marker);
                pins.set(String(organisatie.id), marker);
            });
        }

        function renderResults(organisaties) {
            if (!organisaties.length) {
                resultsPanel.innerHTML = '<p class="map-empty">Geen organisaties gevonden binnen deze radius.</p>';
                return;
            }

            resultsPanel.innerHTML = organisaties.map((organisatie) => `
                <button type="button" class="map-result-card" data-map-result-id="${escapeHtml(organisatie.id)}" style="--card-color: ${escapeHtml(colorForCategory(organisatie.category))}">
                    <span class="map-result-name">${escapeHtml(organisatie.name ?? 'Naam onbekend')}</span>
                    <span class="map-result-meta">${escapeHtml([organisatie.category, `${organisatie.distance} km`].filter(Boolean).join(' - '))}</span>
                    ${organisatie.isAnonymous ? '<span class="map-result-anonymous">Anoniem mogelijk</span>' : ''}
                    ${organisatie.address ? `<span class="map-result-address"><i class="fa-solid fa-location-dot" aria-hidden="true"></i>${escapeHtml(organisatie.address)}</span>` : ''}
                </button>
            `).join('');

            resultsPanel.querySelectorAll('[data-map-result-id]').forEach((card) => {
                card.addEventListener('click', () => highlightOrganisation(card.dataset.mapResultId, true));
            });
        }

        function highlightOrganisation(id, panToMarker = false) {
            if (selectedPin) {
                selectedPin.setZIndexOffset(0);
                selectedPin.setIcon(categoryIcon(selectedPin.options.categoryColor));
            }

            const marker = pins.get(String(id));
            if (!marker) {
                return;
            }

            selectedPin = marker;
            marker.setZIndexOffset(1200);
            marker.setIcon(categoryIcon(selectedMarkerColor, true));
            marker.openPopup();

            resultsPanel.querySelectorAll('.map-result-card').forEach((card) => {
                card.classList.toggle('active', card.dataset.mapResultId === String(id));
            });

            if (panToMarker) {
                map.panTo(marker.getLatLng());
            }
        }

        function fitMap(place, organisaties) {
            if (!place) {
                return;
            }

            const bounds = L.latLngBounds([[place.lat, place.lon]]);
            organisaties.forEach((organisatie) => bounds.extend([organisatie.lat, organisatie.lon]));

            if (radiusCircle) {
                bounds.extend(radiusCircle.getBounds());
            }

            map.fitBounds(bounds, { padding: [30, 30], maxZoom: 13 });
        }

        function clearMap() {
            categoryClusterGroups.forEach((group) => {
                group.clearLayers();
                map.removeLayer(group);
            });
            categoryClusterGroups.clear();
            pins.clear();
            selectedPin = null;

            if (selectedPlaceMarker) {
                selectedPlaceMarker.remove();
                selectedPlaceMarker = null;
            }

            if (radiusCircle) {
                radiusCircle.remove();
                radiusCircle = null;
            }
        }

        function selectedCategories() {
            return Array.from(document.querySelectorAll('[data-cat].active'))
                .map((button) => button.dataset.cat)
                .filter(Boolean);
        }

        function selectedPlace() {
            if (customPlace) {
                return customPlace;
            }

            if (!placeSelect.value) {
                return null;
            }

            return {
                key: placeSelect.value,
            };
        }

        function setCustomPlaceOption() {
            let option = placeSelect.querySelector('option[value="custom"]');

            if (!option) {
                option = document.createElement('option');
                option.value = 'custom';
                placeSelect.prepend(option);
            }

            option.textContent = 'Gekozen locatie';
            placeSelect.value = 'custom';
        }

        function updateCategoryState() {
            const hasSelection = selectedCategories().length > 0;

            categoryButtons.forEach((button) => {
                button.classList.toggle('is-filtered-out', hasSelection && !button.classList.contains('active'));
            });
        }

        function categoryIcon(color, selected = false) {
            const size = selected ? 30 : 22;

            return L.divIcon({
                className: 'category-map-pin-wrap',
                html: `<span class="category-map-pin" style="--pin-color:${escapeHtml(color)}; width:${size}px; height:${size}px;"></span>`,
                iconSize: [size, size],
                iconAnchor: [size / 2, size / 2],
            });
        }

        function clusterGroupForCategory(category, color) {
            if (categoryClusterGroups.has(category)) {
                return categoryClusterGroups.get(category);
            }

            const group = L.markerClusterGroup({
                showCoverageOnHover: false,
                spiderfyOnMaxZoom: true,
                disableClusteringAtZoom: 15,
                maxClusterRadius: 42,
                iconCreateFunction: (cluster) => categoryClusterIcon(color, cluster.getChildCount()),
            });

            group.addTo(map);
            categoryClusterGroups.set(category, group);

            return group;
        }

        function categoryClusterIcon(color, count) {
            const size = count > 24 ? 46 : count > 9 ? 40 : 34;

            return L.divIcon({
                className: 'category-cluster-wrap',
                html: `<span class="category-cluster" style="--cluster-color:${escapeHtml(color)}; width:${size}px; height:${size}px;">${count}</span>`,
                iconSize: [size, size],
                iconAnchor: [size / 2, size / 2],
            });
        }

        function selectedPlaceIcon() {
            return L.divIcon({
                className: 'selected-place-pin-wrap',
                html: '<span class="selected-place-pin"><i class="fa-solid fa-location-dot" aria-hidden="true"></i></span>',
                iconSize: [34, 34],
                iconAnchor: [17, 30],
            });
        }

        function popupHtml(organisatie) {
            return `
                <div class="map-popup">
                    <strong>${escapeHtml(organisatie.name ?? 'Naam onbekend')}</strong>
                    <span>${escapeHtml([organisatie.category, `${organisatie.distance} km`].filter(Boolean).join(' - '))}</span>
                    ${organisatie.isAnonymous ? '<small>Anonieme hulp mogelijk</small>' : ''}
                    ${organisatie.address ? `<small>${escapeHtml(organisatie.address)}</small>` : ''}
                    ${organisatie.description ? `<p>${escapeHtml(organisatie.description).slice(0, 150)}${organisatie.description.length > 150 ? '...' : ''}</p>` : ''}
                    <a href="${escapeHtml(organisatie.url)}">Details bekijken</a>
                </div>
            `;
        }

        function colorForCategory(category) {
            return categoryColors[category] ?? '#6b7280';
        }

        function escapeHtml(value) {
            return String(value ?? '')
                .replaceAll('&', '&amp;')
                .replaceAll('<', '&lt;')
                .replaceAll('>', '&gt;')
                .replaceAll('"', '&quot;')
                .replaceAll("'", '&#039;');
        }
    </script>
@endpush
