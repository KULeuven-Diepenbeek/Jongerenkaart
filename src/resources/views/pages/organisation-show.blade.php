@extends('layouts.app')

@section('title', $organisatie->publiekeNaam ?? 'Organisatie')

@push('head')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
@endpush

@php
    $adres = $organisatie->hoofdAdres();
    $telefoon = $organisatie->getPrimairTelefoon();
    $email = $organisatie->getPrimairEmail();
    $website = $organisatie->getPrimairWebsite();
    $mapAdres = $organisatie->adresMetCoordinaten();
    $lat = $organisatie->getLat();
    $lon = $organisatie->getLon();
    $gemeente = $adres['deelgemeente'] ?? null;
    $postcode = $adres['postcode'] ?? null;
    $catColor = (config('jongerenkaart.category_colors') ?? [])[$organisatie->categorie] ?? '#6b7280';
    $adreslijst = $organisatie->adresgegevens ?? [];
    $cg = $organisatie->contactgegevens ?? [];
    $personen = $organisatie->contactpersonen ?? [];
    $erkenningen = $organisatie->erkenningen ?? [];
    $admin = $organisatie->administratieveGegevens ?? [];
    $doelgroep = $organisatie->doelgroep ?? [];
    $aanbod = $organisatie->aanbod ?? null;
    $zorgverleners = $organisatie->zorgverleners ?? null;
@endphp

@section('content')
    <div class="container detail-container" style="--detail-color: {{ $catColor }}">
        <div class="detail-back">
            <a href="{{ url()->previous() === url()->current() ? route('search') : url()->previous() }}" class="btn btn-outline-primary btn-sm">
                &larr; Terug naar zoekresultaten
            </a>
        </div>

        <div class="detail-header">
            <div>
                <h1>{{ $organisatie->publiekeNaam }}</h1>
                <div class="detail-badges">
                    @if ($organisatie->categorie)
                        <span class="badge" style="background:{{ $catColor }}">{{ $organisatie->categorie }}</span>
                    @endif
                    @if ($organisatie->subcategorie)
                        <span class="badge badge-sub">{{ $organisatie->subcategorie }}</span>
                    @endif
                    @if ($organisatie->leeftijdscategorie)
                        <span class="badge badge-leeftijd">{{ $organisatie->leeftijdscategorie }}</span>
                    @endif
                    @if ($organisatie->isGratis())
                        <span class="badge badge-free">Gratis</span>
                    @endif
                    @if ($organisatie->isAnoniem())
                        <span class="badge badge-anonymous">Anoniem mogelijk</span>
                    @endif
                </div>
            </div>
            <button id="fav-btn" class="fav-btn" title="Voeg toe aan favorieten" aria-label="Voeg toe aan favorieten">
                <i class="fa-regular fa-star" aria-hidden="true"></i>
            </button>
        </div>

        <div class="detail-quick-actions">
            @if ($telefoon)
                <a href="tel:{{ $telefoon }}"><i class="fa-solid fa-phone" aria-hidden="true"></i>{{ $telefoon }}</a>
            @endif
            @if ($email)
                <a href="mailto:{{ $email }}"><i class="fa-solid fa-envelope" aria-hidden="true"></i>{{ $email }}</a>
            @endif
            @if ($website)
                <a href="{{ $website }}" target="_blank" rel="noopener noreferrer"><i class="fa-solid fa-globe" aria-hidden="true"></i>Website</a>
            @endif
            @if ($adres)
                <a href="{{ $organisatie->navigationUrlForAddress($adres) }}" target="_blank" rel="noopener noreferrer"><i class="fa-solid fa-location-dot" aria-hidden="true"></i>{{ $organisatie->formatAdres($adres) }}</a>
            @endif
        </div>

        <div class="tab-layout">
            <div class="tab-nav" role="tablist">
                <button class="tab-btn active" data-tab="algemeen" role="tab">Algemeen</button>
                <button class="tab-btn" data-tab="adres" role="tab">Adresgegevens</button>
                <button class="tab-btn" data-tab="contact" role="tab">Contactgegevens</button>
                <button class="tab-btn" data-tab="personen" role="tab">Contactpersonen</button>
                <button class="tab-btn" data-tab="erkenningen" role="tab">Erkenningen</button>
                <button class="tab-btn" data-tab="admin" role="tab">Administratieve gegevens</button>
                <button class="tab-btn" data-tab="extra" role="tab">Extra info</button>
                <button class="tab-btn" data-tab="aanbod" role="tab">Aanbod</button>
            </div>

            <div class="tab-content">
                <div id="tab-algemeen" class="tab-panel active">
                    @if ($organisatie->algemeneWerking)
                        <section class="detail-section">
                            <h2>Algemene werking</h2>
                            <p>{{ $organisatie->algemeneWerking }}</p>
                        </section>
                    @endif

                    @if (! empty($doelgroep['opmerkingen']) || ! empty($doelgroep['leeftijdscategorieen']))
                        <section class="detail-section">
                            <h2>Doelgroep</h2>
                            @if (! empty($doelgroep['leeftijdscategorieen']))
                                <p><strong>Leeftijdscategorieen:</strong> {{ $doelgroep['leeftijdscategorieen'] }}</p>
                            @endif
                            @if (! empty($doelgroep['opmerkingen']))
                                <p>{{ $doelgroep['opmerkingen'] }}</p>
                            @endif
                        </section>
                    @endif

                    @if ($organisatie->werkingsgebieden)
                        <section class="detail-section">
                            <h2>Werkingsgebieden</h2>
                            <p>{{ $organisatie->werkingsgebieden }}</p>
                        </section>
                    @endif

                    @if (! empty($organisatie->openingsuren))
                        <section class="detail-section">
                            <h2>Openingsuren</h2>
                            @foreach (collect($organisatie->openingsuren)->groupBy('type') as $type => $uren)
                                @if (collect($organisatie->openingsuren)->groupBy('type')->count() > 1)
                                    <h3 class="detail-subsection-title">{{ $type }}</h3>
                                @endif
                                <table class="uren-table">
                                    <thead><tr><th>Dag</th><th>Van</th><th>Tot</th></tr></thead>
                                    <tbody>
                                        @foreach ($uren as $uur)
                                            <tr>
                                                <td>{{ ucfirst($uur['dag'] ?? '') }}</td>
                                                <td>{{ $uur['beginuur'] ?? '' }}</td>
                                                <td>{{ $uur['einduur'] ?? '' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @endforeach
                        </section>
                    @endif

                    @if (! empty($organisatie->rubrieken))
                        <section class="detail-section">
                            <h2>Rubrieken</h2>
                            <ul class="rubriek-list">
                                @foreach ($organisatie->rubrieken as $rubriek)
                                    <li><span class="rubriek-code">{{ $rubriek['code'] ?? '' }}</span> {{ $rubriek['naam'] ?? '' }}</li>
                                @endforeach
                            </ul>
                        </section>
                    @endif

                    @if (! $organisatie->algemeneWerking && empty($doelgroep) && ! $organisatie->werkingsgebieden && empty($organisatie->openingsuren) && empty($organisatie->rubrieken))
                        <p class="detail-empty">Geen algemene informatie beschikbaar.</p>
                    @endif
                </div>

                <div id="tab-adres" class="tab-panel">
                    @if (! empty($adreslijst))
                        <div class="adres-lijst">
                            @foreach ($adreslijst as $adr)
                                @php
                                    $adresTekst = $organisatie->formatAdres($adr);
                                    $navigatieUrl = $organisatie->navigationUrlForAddress($adr);
                                @endphp
                                <div class="adres-item {{ ($adr['hoofd_adres'] ?? false) ? 'adres-hoofdadres' : '' }}">
                                    <div class="adres-item-header">
                                        @if ($adr['hoofd_adres'] ?? false)
                                            <span class="adres-label-badge">Hoofdadres</span>
                                        @endif
                                        @if (! empty($adr['adrestype']))
                                            <span class="adres-type">{{ $adr['adrestype'] }}</span>
                                        @endif
                                    </div>
                                    @if ($adresTekst)
                                        <p>
                                            <a href="{{ $navigatieUrl }}" target="_blank" rel="noopener noreferrer" class="address-link">
                                                <i class="fa-solid fa-location-dot" aria-hidden="true"></i> {{ $adresTekst }}
                                            </a>
                                        </p>
                                    @endif
                                    @if (! empty($adr['nisCode']))<p class="adres-meta">NIS-code: {{ $adr['nisCode'] }}</p>@endif
                                    @if (! empty($adr['opmerkingen']))<p class="adres-meta"><em>{{ $adr['opmerkingen'] }}</em></p>@endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="detail-empty">Geen adresgegevens beschikbaar.</p>
                    @endif

                    @if ($lat && $lon)
                        <section class="detail-section">
                            <h2>Locatie op kaart</h2>
                            <div id="detail-map"></div>
                            @if ($mapAdres && $organisatie->formatAdres($mapAdres))
                                <p class="adres-meta mt-2">{{ $organisatie->formatAdres($mapAdres) }}</p>
                            @endif
                            <p class="adres-meta">Coordinaten: {{ round($lat, 6) }}, {{ round($lon, 6) }}</p>
                        </section>
                    @endif
                </div>

                <div id="tab-contact" class="tab-panel">
                    @php
                        $telefoons = $cg['telefoonnummers'] ?? [];
                        $emails = $cg['emailadressen'] ?? [];
                        $onlines = $cg['online'] ?? [];
                    @endphp

                    @if (! empty($telefoons))
                        <section class="detail-section">
                            <h2>Telefoonnummers</h2>
                            <ul class="contact-list">
                                @foreach ($telefoons as $t)
                                    <li><i class="fa-solid fa-phone" aria-hidden="true"></i><a href="tel:{{ $t['nummer'] }}">{{ $t['nummer'] }}</a>@if ($t['primair'] ?? false)<span class="primair-badge">primair</span>@endif</li>
                                @endforeach
                            </ul>
                        </section>
                    @endif

                    @if (! empty($emails))
                        <section class="detail-section">
                            <h2>E-mailadressen</h2>
                            <ul class="contact-list">
                                @foreach ($emails as $e)
                                    <li><i class="fa-solid fa-envelope" aria-hidden="true"></i><a href="mailto:{{ $e['adres'] }}">{{ $e['adres'] }}</a>@if ($e['primair'] ?? false)<span class="primair-badge">primair</span>@endif</li>
                                @endforeach
                            </ul>
                        </section>
                    @endif

                    @if (! empty($onlines))
                        <section class="detail-section">
                            <h2>Websites</h2>
                            <ul class="contact-list">
                                @foreach ($onlines as $o)
                                    <li><i class="fa-solid fa-globe" aria-hidden="true"></i><a href="{{ $o['url'] }}" target="_blank" rel="noopener noreferrer">{{ $o['url'] }}</a>@if ($o['primair'] ?? false)<span class="primair-badge">primair</span>@endif</li>
                                @endforeach
                            </ul>
                        </section>
                    @endif

                    @if ($organisatie->beschikbaarheid_telefoon || $organisatie->beschikbaarheid_chat)
                        <section class="detail-section">
                            <h2>Beschikbaarheid hulpkanalen</h2>
                            <dl class="extra-dl">
                                @if ($organisatie->beschikbaarheid_telefoon)
                                    <dt>Telefonisch</dt><dd>{{ $organisatie->beschikbaarheid_telefoon }}</dd>
                                @endif
                                @if ($organisatie->beschikbaarheid_chat)
                                    <dt>Chat</dt><dd>{{ $organisatie->beschikbaarheid_chat }}</dd>
                                @endif
                            </dl>
                        </section>
                    @endif

                    @if (empty($telefoons) && empty($emails) && empty($onlines) && ! $organisatie->beschikbaarheid_telefoon && ! $organisatie->beschikbaarheid_chat)
                        <p class="detail-empty">Geen contactgegevens beschikbaar.</p>
                    @endif
                </div>

                <div id="tab-personen" class="tab-panel">
                    @if (! empty($personen))
                        <div class="personen-grid">
                            @foreach ($personen as $p)
                                <div class="persoon-card">
                                    <div class="persoon-name">{{ trim(($p['voornaam'] ?? '').' '.($p['naam'] ?? '')) }}</div>
                                    @if (! empty($p['rol']))<div class="persoon-rol">{{ $p['rol'] }}</div>@endif
                                    @if (! empty($p['email']))<div class="persoon-contact"><a href="mailto:{{ $p['email'] }}"><i class="fa-solid fa-envelope" aria-hidden="true"></i> {{ $p['email'] }}</a></div>@endif
                                    @if (! empty($p['telefoonnummer']))<div class="persoon-contact"><a href="tel:{{ $p['telefoonnummer'] }}"><i class="fa-solid fa-phone" aria-hidden="true"></i> {{ $p['telefoonnummer'] }}</a></div>@endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="detail-empty">Geen contactpersonen beschikbaar.</p>
                    @endif
                </div>

                <div id="tab-erkenningen" class="tab-panel">
                    @if (! empty($erkenningen))
                        <div class="erkenning-list">
                            @foreach ($erkenningen as $erk)
                                <div class="erkenning-item">
                                    <div class="erkenning-als">{{ $erk['erkendAls'] ?? '' }}</div>
                                    <div class="erkenning-door">Erkend door: {{ $erk['erkendDoor'] ?? '' }}</div>
                                    @if (! empty($erk['hcoNummer']))<div class="erkenning-meta">HCO-nummer: {{ $erk['hcoNummer'] }}</div>@endif
                                    @if (isset($erk['capaciteitsnummer']) && $erk['capaciteitsnummer'] !== '')<div class="erkenning-meta">Capaciteit: {{ $erk['capaciteitsnummer'] }}</div>@endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="detail-empty">Geen erkenningen beschikbaar.</p>
                    @endif
                </div>

                <div id="tab-admin" class="tab-panel">
                    @if (! empty($admin))
                        <div class="admin-grid">
                            @if (! empty($admin['namen']))<div class="admin-block"><h3>Officiele namen</h3><ul>@foreach ($admin['namen'] as $n)<li>{{ $n }}</li>@endforeach</ul></div>@endif
                            @if (! empty($admin['juridischeVorm']))<div class="admin-block"><h3>Juridische vorm</h3><p>{{ implode(', ', (array) $admin['juridischeVorm']) }}</p></div>@endif
                            @if (! empty($admin['ondernemingsnummers']))<div class="admin-block"><h3>Ondernemingsnummer(s)</h3><ul>@foreach ($admin['ondernemingsnummers'] as $n)<li>{{ $n }}</li>@endforeach</ul></div>@endif
                            @if (! empty($admin['werkingstalen']))<div class="admin-block"><h3>Werkingstalen</h3><p>{{ implode(', ', (array) $admin['werkingstalen']) }}</p></div>@endif
                        </div>
                    @else
                        <p class="detail-empty">Geen administratieve gegevens beschikbaar.</p>
                    @endif
                </div>

                <div id="tab-extra" class="tab-panel">
                    @php
                        $attributes = $organisatie->getAttributes();
                        $kostprijs = $attributes[\App\Models\Organisatie::FIELD_KOSTPRIJS] ?? null;
                        $hasExtra = $organisatie->taalAanbod
                            || $organisatie->betalingswijze
                            || $organisatie->aanmeldingswijze
                            || $organisatie->wijken
                            || $organisatie->anoniem
                            || $organisatie->sociale_kaart_id
                            || $kostprijs;
                    @endphp
                    @if ($hasExtra)
                        <dl class="extra-dl">
                            @if ($organisatie->taalAanbod)<dt>Taalaanbod</dt><dd>{{ $organisatie->taalAanbod }}</dd>@endif
                            @if ($organisatie->betalingswijze)<dt>Betalingswijze</dt><dd>{{ $organisatie->betalingswijze }}</dd>@endif
                            @if ($kostprijs)<dt>Kostprijs</dt><dd>{{ $kostprijs }}</dd>@endif
                            @if ($organisatie->anoniem)<dt>Anoniem</dt><dd>{{ $organisatie->anoniem }}</dd>@endif
                            @if ($organisatie->aanmeldingswijze)<dt>Aanmeldingswijze</dt><dd>{{ $organisatie->aanmeldingswijze }}</dd>@endif
                            @if ($organisatie->wijken)<dt>Wijken</dt><dd>{{ $organisatie->wijken }}</dd>@endif
                            @if ($organisatie->sociale_kaart_id)<dt>Sociale Kaart-ID</dt><dd>{{ $organisatie->sociale_kaart_id }}</dd>@endif
                        </dl>
                    @else
                        <p class="detail-empty">Geen extra informatie beschikbaar.</p>
                    @endif
                </div>

                <div id="tab-aanbod" class="tab-panel">
                    @if ($aanbod || $zorgverleners)
                        @if ($aanbod && ! empty($aanbod['namen']))
                            <section class="detail-section"><h2>Aanbod</h2><ul class="aanbod-list">@foreach (explode(',', $aanbod['namen']) as $item)<li>{{ trim($item) }}</li>@endforeach</ul></section>
                        @endif
                        @if ($zorgverleners && ! empty($zorgverleners['naam']))
                            <section class="detail-section"><h2>Zorgverleners</h2><ul class="aanbod-list">@foreach (explode(',', $zorgverleners['naam']) as $item)<li>{{ trim($item) }}</li>@endforeach</ul></section>
                        @endif
                    @else
                        <p class="detail-empty">Geen aanbodinformatie beschikbaar.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    @if ($lat && $lon)
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    @endif
    <script>
        document.querySelectorAll('.tab-btn').forEach((button) => {
            button.addEventListener('click', function () {
                const tab = this.dataset.tab;
                document.querySelectorAll('.tab-btn').forEach((item) => item.classList.remove('active'));
                document.querySelectorAll('.tab-panel').forEach((item) => item.classList.remove('active'));
                this.classList.add('active');
                document.querySelector(`#tab-${tab}`).classList.add('active');

                if (tab === 'adres' && typeof detailMap !== 'undefined') {
                    setTimeout(() => detailMap.invalidateSize(), 50);
                }
            });
        });

        const organisatieData = {
            id: @json((string) $organisatie->getKey()),
            name: @json($organisatie->publiekeNaam),
            meta: @json(implode(' - ', array_filter([$organisatie->categorie, $organisatie->formatAdres($adres)]))),
            url: @json(route('organisaties.show', ['id' => (string) $organisatie->getKey()])),
        };
        const favoritesKey = 'jongerenkaart.favoriteOrganisaties';
        const favoriteItemsKey = 'jongerenkaart.favoriteOrganisatieItems';
        const recentItemsKey = 'jongerenkaart.recentOrganisaties';
        const favBtn = document.querySelector('#fav-btn');

        function favoriteIds() {
            try { return new Set(JSON.parse(localStorage.getItem(favoritesKey) || '[]')); } catch { return new Set(); }
        }

        function favoriteItemMap() {
            try { return JSON.parse(localStorage.getItem(favoriteItemsKey) || '{}'); } catch { return {}; }
        }

        function syncFavoriteButton() {
            const isFavorite = favoriteIds().has(organisatieData.id);
            favBtn.classList.toggle('active', isFavorite);
            favBtn.title = isFavorite ? 'Verwijder uit favorieten' : 'Voeg toe aan favorieten';
            favBtn.setAttribute('aria-label', favBtn.title);
            favBtn.innerHTML = `<i class="${isFavorite ? 'fa-solid' : 'fa-regular'} fa-star" aria-hidden="true"></i>`;
        }

        function saveRecent() {
            let items = [];
            try { items = JSON.parse(localStorage.getItem(recentItemsKey) || '[]'); } catch {}
            items = [organisatieData, ...items.filter((item) => item.id !== organisatieData.id)].slice(0, 6);
            localStorage.setItem(recentItemsKey, JSON.stringify(items));
        }

        favBtn.addEventListener('click', () => {
            const ids = favoriteIds();
            const items = favoriteItemMap();

            if (ids.has(organisatieData.id)) {
                ids.delete(organisatieData.id);
                delete items[organisatieData.id];
            } else {
                ids.add(organisatieData.id);
                items[organisatieData.id] = organisatieData;
            }

            localStorage.setItem(favoritesKey, JSON.stringify(Array.from(ids)));
            localStorage.setItem(favoriteItemsKey, JSON.stringify(items));
            syncFavoriteButton();
        });

        saveRecent();
        syncFavoriteButton();
    </script>
    @if ($lat && $lon)
        <script>
            const detailLat = @json($lat);
            const detailLon = @json($lon);
            const detailAddress = @json($mapAdres ? $organisatie->formatAdres($mapAdres) : null);
            const detailMap = L.map('detail-map').setView([detailLat, detailLon], 15);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
                maxZoom: 18,
            }).addTo(detailMap);

            L.marker([detailLat, detailLon]).addTo(detailMap)
                .bindPopup(`
                    <strong>${@json($organisatie->publiekeNaam)}</strong>
                    ${detailAddress ? `<br>${detailAddress}` : ''}
                    <br><small>${detailLat.toFixed(6)}, ${detailLon.toFixed(6)}</small>
                `)
                .openPopup();
        </script>
    @endif
@endpush
