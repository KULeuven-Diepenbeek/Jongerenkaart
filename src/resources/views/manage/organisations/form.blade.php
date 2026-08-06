@extends('layouts.app')

@section('title', $mode === 'create' ? 'Organisatie toevoegen' : 'Organisatie bewerken')

@push('head')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
@endpush

@php
    $adres = old('adres', $organisatie->hoofdAdres() ?? []);
    $contactgegevens = $organisatie->contactgegevens ?? [];
    $contactEmail = old('contact_email', data_get($contactgegevens, 'emailadressen.0.adres'));
    $contactPhone = old('contact_phone', data_get($contactgegevens, 'telefoonnummers.0.nummer'));
    $website = old('website', data_get($contactgegevens, 'online.0.url') ?? $organisatie->url);
    $rubrieken = old('rubrieken', collect($organisatie->rubrieken ?? [])->pluck('naam')->filter()->values()->all());
    $werkingsgebieden = old('werkingsgebieden', (array) ($organisatie->werkingsgebieden ?? []));
    $taalAanbod = old('taalAanbod', (array) ($organisatie->taalAanbod ?? []));
    $aanbod = old('aanbod', collect(explode(',', (string) data_get($organisatie->aanbod, 'namen')))->map(fn ($item) => trim($item))->filter()->values()->all());
    $openingsuren = old('openingsuren', $organisatie->openingsuren ?? []);
    $contactpersonen = old('contactpersonen', $organisatie->contactpersonen ?? [[]]);
    $erkenningen = old('erkenningen', $organisatie->erkenningen ?? []);
    $administratieveGegevens = $organisatie->administratieveGegevens ?? [];
    $adminNamen = old('administratieveGegevens.namen', (array) data_get($administratieveGegevens, 'namen', []));
    $ondernemingsnummers = old('administratieveGegevens.ondernemingsnummers', (array) data_get($administratieveGegevens, 'ondernemingsnummers', []));
    $juridischeVorm = old('administratieveGegevens.juridischeVorm', (array) data_get($administratieveGegevens, 'juridischeVorm', []));
    $werkingstalen = old('administratieveGegevens.werkingstalen', (array) data_get($administratieveGegevens, 'werkingstalen', []));
    $wijken = old('wijken', collect(explode(',', (string) ($organisatie->wijken ?? '')))->map(fn ($item) => trim($item))->filter()->values()->all());
    $faciliteiten = old('faciliteiten', (array) ($organisatie->faciliteiten ?? []));
    $zorgverleners = old('zorgverleners', collect(explode(',', (string) data_get($organisatie->zorgverleners, 'naam')))->map(fn ($item) => trim($item))->filter()->values()->all());
    $days = ['maandag', 'dinsdag', 'woensdag', 'donderdag', 'vrijdag', 'zaterdag', 'zondag'];
@endphp

@section('content')
    <section class="manage-page">
        <div class="manage-heading">
            <div>
                <p class="eyebrow">Beheer</p>
                <h1>{{ $mode === 'create' ? 'Organisatie toevoegen' : 'Organisatie bewerken' }}</h1>
            </div>
            <a href="{{ route('manage.organisations.index') }}" class="btn btn-outline-primary btn-sm">Terug naar lijst</a>
        </div>

        @if (session('status'))
            <p class="auth-success">{{ session('status') }}</p>
        @endif

        @if ($errors->any())
            <div class="manage-error">
                <strong>Niet alles kon worden opgeslagen.</strong>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ $action }}" class="manage-form">
            @csrf
            @if ($mode === 'edit')
                @method('PUT')
            @endif

            <div class="tab-layout manage-tab-layout">
                <div class="tab-nav" role="tablist">
                    <button class="tab-btn active" type="button" data-tab="algemeen" role="tab">Algemeen</button>
                    <button class="tab-btn" type="button" data-tab="adres" role="tab">Adres</button>
                    <button class="tab-btn" type="button" data-tab="contact" role="tab">Contact</button>
                    <button class="tab-btn" type="button" data-tab="werking" role="tab">Werking</button>
                    <button class="tab-btn" type="button" data-tab="aanbod" role="tab">Aanbod</button>
                    <button class="tab-btn" type="button" data-tab="erkenningen" role="tab">Erkenningen</button>
                    <button class="tab-btn" type="button" data-tab="admin" role="tab">Administratieve gegevens</button>
                    <button class="tab-btn" type="button" data-tab="extra" role="tab">Extra info</button>
                </div>

                <div class="tab-content">
                    <div id="tab-algemeen" class="tab-panel active">
                        <div class="manage-grid">
                            <label class="manage-field span-2">
                                <span>Naam organisatie</span>
                                <input type="text" name="publiekeNaam" value="{{ old('publiekeNaam', $organisatie->publiekeNaam) }}" required>
                            </label>
                            @include('manage.organisations.partials.choice', ['name' => 'categorie', 'label' => 'Categorie', 'value' => $organisatie->categorie, 'options' => $options['categorie'] ?? []])
                            @include('manage.organisations.partials.choice', ['name' => 'subcategorie', 'label' => 'Subrubriek', 'value' => $organisatie->subcategorie, 'options' => $options['subcategorie'] ?? []])
                            @include('manage.organisations.partials.choice', ['name' => 'subsubcategorie', 'label' => 'Subsubrubriek', 'value' => $organisatie->subsubcategorie, 'options' => $options['subsubcategorie'] ?? []])
                            @include('manage.organisations.partials.choice', ['name' => 'leeftijdscategorie', 'label' => 'Leeftijd', 'value' => $organisatie->leeftijdscategorie, 'options' => $options['leeftijdscategorie'] ?? []])
                            <label class="manage-field span-2">
                                <span>Beschrijving</span>
                                <textarea name="algemeneWerking" rows="7">{{ old('algemeneWerking', $organisatie->algemeneWerking) }}</textarea>
                            </label>
                        </div>
                    </div>

                    <div id="tab-adres" class="tab-panel">
                        <div class="manage-grid">
                            <label class="manage-field"><span>Straat</span><input name="adres[straatnaam]" value="{{ $adres['straatnaam'] ?? '' }}"></label>
                            <label class="manage-field"><span>Huisnummer</span><input name="adres[huisnummer]" value="{{ $adres['huisnummer'] ?? '' }}"></label>
                            <label class="manage-field"><span>Bus</span><input name="adres[busnummer]" value="{{ $adres['busnummer'] ?? '' }}"></label>
                            <label class="manage-field"><span>Postcode</span><input name="adres[postcode]" value="{{ $adres['postcode'] ?? '' }}"></label>
                            <label class="manage-field"><span>Gemeente</span><input name="adres[deelgemeente]" value="{{ $adres['deelgemeente'] ?? '' }}"></label>
                            <label class="manage-field"><span>Adrestype</span><input name="adres[adrestype]" value="{{ $adres['adrestype'] ?? 'Hoofdadres' }}"></label>
                            <label class="manage-field"><span>Latitude</span><input id="address-lat" name="adres[geocoordinatenX]" value="{{ $adres['geocoordinatenX'] ?? $organisatie->getLat() }}"></label>
                            <label class="manage-field"><span>Longitude</span><input id="address-lon" name="adres[geocoordinatenY]" value="{{ $adres['geocoordinatenY'] ?? $organisatie->getLon() }}"></label>
                        </div>
                        <div id="manage-address-map" class="manage-address-map"></div>
                    </div>

                    <div id="tab-contact" class="tab-panel">
                        <div class="manage-grid">
                            <label class="manage-field"><span>Algemeen e-mailadres</span><input type="email" name="contact_email" value="{{ $contactEmail }}"></label>
                            <label class="manage-field"><span>Telefoon</span><input name="contact_phone" value="{{ $contactPhone }}"></label>
                            <label class="manage-field span-2"><span>Website</span><input type="url" name="website" value="{{ $website }}"></label>
                            <label class="manage-field span-2"><span>Beschikbaarheid telefonisch</span><input name="beschikbaarheid_telefoon" value="{{ old('beschikbaarheid_telefoon', $organisatie->beschikbaarheid_telefoon) }}"></label>
                            <label class="manage-field span-2"><span>Beschikbaarheid chat</span><input name="beschikbaarheid_chat" value="{{ old('beschikbaarheid_chat', $organisatie->beschikbaarheid_chat) }}"></label>
                        </div>

                        <h2>Contactpersonen</h2>
                        <div data-repeat-list="contactpersonen" class="repeat-list">
                            @foreach ($contactpersonen as $index => $persoon)
                                <div class="repeat-row contact-row">
                                    <input name="contactpersonen[{{ $index }}][voornaam]" value="{{ $persoon['voornaam'] ?? '' }}" placeholder="Voornaam">
                                    <input name="contactpersonen[{{ $index }}][naam]" value="{{ $persoon['naam'] ?? '' }}" placeholder="Naam">
                                    <input type="email" name="contactpersonen[{{ $index }}][email]" value="{{ $persoon['email'] ?? '' }}" placeholder="E-mail">
                                    <input name="contactpersonen[{{ $index }}][telefoonnummer]" value="{{ $persoon['telefoonnummer'] ?? '' }}" placeholder="Telefoon">
                                    <input name="contactpersonen[{{ $index }}][rol]" value="{{ $persoon['rol'] ?? '' }}" placeholder="Rol">
                                    <button type="button" class="repeat-remove">Verwijder</button>
                                </div>
                            @endforeach
                        </div>
                        <button type="button" class="btn btn-outline-primary btn-sm" data-add-repeat="contactpersonen">+ Contactpersoon</button>
                    </div>

                    <div id="tab-werking" class="tab-panel">
                        <x-manage.repeat-choice field="werkingsgebieden" label="Werkingsgebied" :values="$werkingsgebieden" :options="$options['werkingsgebieden'] ?? []" />
                        <x-manage.repeat-choice field="taalAanbod" label="Taal" :values="$taalAanbod" :options="$options['taalAanbod'] ?? []" />

                        <h2>Openingsuren</h2>
                        <div data-repeat-list="openingsuren" class="repeat-list">
                            @foreach ($openingsuren ?: [[]] as $index => $uur)
                                @php
                                    $openingType = $uur['type'] ?? '';
                                    $openingOptions = $options['openingsuren'] ?? [];
                                    $hasOpeningTypeInOptions = $openingType === '' || in_array($openingType, $openingOptions, true);
                                @endphp
                                <div class="repeat-row opening-row">
                                    <select name="openingsuren[{{ $index }}][type]" data-choice-select>
                                        <option value="">Type</option>
                                        @foreach ($openingOptions as $option)
                                            <option value="{{ $option }}" @selected($openingType === $option)>{{ $option }}</option>
                                        @endforeach
                                        <option value="__other" @selected(! $hasOpeningTypeInOptions)>Andere</option>
                                    </select>
                                    <input
                                        name="openingsuren[{{ $index }}][type_other]"
                                        value="{{ ! $hasOpeningTypeInOptions ? $openingType : '' }}"
                                        placeholder="Ander type"
                                        data-choice-other
                                        @if ($hasOpeningTypeInOptions) hidden @endif
                                    >
                                    <select name="openingsuren[{{ $index }}][dag]">
                                        <option value="">Dag</option>
                                        @foreach ($days as $day)
                                            <option value="{{ $day }}" @selected(($uur['dag'] ?? '') === $day)>{{ ucfirst($day) }}</option>
                                        @endforeach
                                    </select>
                                    <input type="time" name="openingsuren[{{ $index }}][beginuur]" value="{{ $uur['beginuur'] ?? '' }}">
                                    <input type="time" name="openingsuren[{{ $index }}][einduur]" value="{{ $uur['einduur'] ?? '' }}">
                                    <button type="button" class="repeat-remove">Verwijder</button>
                                </div>
                            @endforeach
                        </div>
                        <button type="button" class="btn btn-outline-primary btn-sm" data-add-repeat="openingsuren">+ Openingsuur</button>
                    </div>

                    <div id="tab-aanbod" class="tab-panel">
                        <x-manage.repeat-choice field="rubrieken" label="Rubriek" :values="$rubrieken" :options="$options['rubriek'] ?? []" />
                        @include('manage.organisations.partials.choice', ['name' => 'betalingswijze', 'label' => 'Betaling', 'value' => $organisatie->betalingswijze, 'options' => $options['betalingswijze'] ?? []])
                        <label class="manage-field span-2"><span>Kostprijs</span><input name="kostprijs" value="{{ old('kostprijs', $organisatie->kostprijs) }}"></label>
                        <x-manage.repeat-choice field="aanbod" label="Aanbod" :values="$aanbod" :options="$options['aanbod'] ?? []" />
                    </div>

                    <div id="tab-erkenningen" class="tab-panel">
                        <h2>Erkenningen</h2>
                        <div data-repeat-list="erkenningen" class="repeat-list">
                            @foreach ($erkenningen ?: [[]] as $index => $erkenning)
                                @php
                                    $erkendDoor = $erkenning['erkendDoor'] ?? '';
                                    $erkendAls = $erkenning['erkendAls'] ?? '';
                                    $erkendDoorOptions = $options['erkendDoor'] ?? [];
                                    $erkendAlsOptions = $options['erkendAls'] ?? [];
                                    $hasErkendDoorInOptions = $erkendDoor === '' || in_array($erkendDoor, $erkendDoorOptions, true);
                                    $hasErkendAlsInOptions = $erkendAls === '' || in_array($erkendAls, $erkendAlsOptions, true);
                                @endphp
                                <div class="repeat-row recognition-row">
                                    <div data-choice-wrapper>
                                        <select name="erkenningen[{{ $index }}][erkendDoor]" data-choice-select>
                                            <option value="">Erkend door</option>
                                            @foreach ($erkendDoorOptions as $option)
                                                <option value="{{ $option }}" @selected($erkendDoor === $option)>{{ $option }}</option>
                                            @endforeach
                                            <option value="__other" @selected(! $hasErkendDoorInOptions)>Andere</option>
                                        </select>
                                        <input
                                            name="erkenningen[{{ $index }}][erkendDoor_other]"
                                            value="{{ ! $hasErkendDoorInOptions ? $erkendDoor : '' }}"
                                            placeholder="Andere erkenner"
                                            data-choice-other
                                            @if ($hasErkendDoorInOptions) hidden @endif
                                        >
                                    </div>
                                    <div data-choice-wrapper>
                                        <select name="erkenningen[{{ $index }}][erkendAls]" data-choice-select>
                                            <option value="">Erkend als</option>
                                            @foreach ($erkendAlsOptions as $option)
                                                <option value="{{ $option }}" @selected($erkendAls === $option)>{{ $option }}</option>
                                            @endforeach
                                            <option value="__other" @selected(! $hasErkendAlsInOptions)>Andere</option>
                                        </select>
                                        <input
                                            name="erkenningen[{{ $index }}][erkendAls_other]"
                                            value="{{ ! $hasErkendAlsInOptions ? $erkendAls : '' }}"
                                            placeholder="Andere erkenning"
                                            data-choice-other
                                            @if ($hasErkendAlsInOptions) hidden @endif
                                        >
                                    </div>
                                    <input name="erkenningen[{{ $index }}][hcoNummer]" value="{{ $erkenning['hcoNummer'] ?? '' }}" placeholder="HCO-nummer">
                                    <input name="erkenningen[{{ $index }}][capaciteitsnummer]" value="{{ $erkenning['capaciteitsnummer'] ?? '' }}" placeholder="Capaciteit">
                                    <button type="button" class="repeat-remove">Verwijder</button>
                                </div>
                            @endforeach
                        </div>
                        <button type="button" class="btn btn-outline-primary btn-sm" data-add-repeat="erkenningen">+ Erkenning</button>
                    </div>

                    <div id="tab-admin" class="tab-panel">
                        <x-manage.repeat-choice field="administratieveGegevens[namen]" label="Officiele namen" :values="$adminNamen" :options="[]" />
                        <x-manage.repeat-choice field="administratieveGegevens[ondernemingsnummers]" label="Ondernemingsnummer" :values="$ondernemingsnummers" :options="[]" />
                        <x-manage.repeat-choice field="administratieveGegevens[juridischeVorm]" label="Juridische vorm" :values="$juridischeVorm" :options="$options['juridischeVorm'] ?? []" />
                        <x-manage.repeat-choice field="administratieveGegevens[werkingstalen]" label="Werkingstaal" :values="$werkingstalen" :options="$options['werkingstalen'] ?? []" />
                    </div>

                    <div id="tab-extra" class="tab-panel">
                        <div class="manage-grid">
                            <label class="manage-field span-2"><span>Anoniem</span><input name="anoniem" value="{{ old('anoniem', $organisatie->anoniem) }}" placeholder="Bijvoorbeeld: ja, neen of toelichting"></label>
                            <label class="manage-field span-2"><span>Sociale Kaart-ID</span><input name="sociale_kaart_id" value="{{ old('sociale_kaart_id', $organisatie->sociale_kaart_id) }}"></label>
                        </div>
                        @include('manage.organisations.partials.choice', ['name' => 'aanmeldingswijze', 'label' => 'Aanmeldingswijze', 'value' => $organisatie->aanmeldingswijze, 'options' => $options['aanmeldingswijze'] ?? []])
                        <x-manage.repeat-choice field="wijken" label="Wijk" :values="$wijken" :options="$options['wijken'] ?? []" />
                        <x-manage.repeat-choice field="faciliteiten" label="Faciliteit" :values="$faciliteiten" :options="$options['faciliteiten'] ?? []" />
                        <x-manage.repeat-choice field="zorgverleners" label="Zorgverlener" :values="$zorgverleners" :options="$options['zorgverleners'] ?? []" />
                    </div>
                </div>
            </div>

            <div class="manage-form-actions">
                <button type="submit" class="btn btn-primary">Opslaan</button>
                <a href="{{ route('manage.organisations.index') }}" class="btn btn-outline-primary">Annuleren</a>
            </div>
        </form>

        @if ($mode === 'edit' && auth()->user()->isAdmin())
            <section class="manage-danger-zone" aria-labelledby="delete-organisation-heading">
                <div>
                    <h2 id="delete-organisation-heading">Organisatie verwijderen</h2>
                    <p>Dit verwijdert de organisatie definitief uit MongoDB, de zoekindex en alle bijbehorende filteropties.</p>
                </div>
                <form method="POST" action="{{ route('manage.organisations.destroy', ['organisatie' => (string) $organisatie->getKey()]) }}" onsubmit="return confirm('Weet je zeker dat je deze organisatie definitief wilt verwijderen?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger">
                        <i class="fa-solid fa-trash" aria-hidden="true"></i>
                        Organisatie verwijderen
                    </button>
                </form>
            </section>
        @endif
    </section>
@endsection

@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        const otherValue = '__other';
        document.querySelectorAll('.tab-btn').forEach((button) => {
            button.addEventListener('click', function () {
                const tab = this.dataset.tab;
                document.querySelectorAll('.tab-btn').forEach((item) => item.classList.remove('active'));
                document.querySelectorAll('.tab-panel').forEach((item) => item.classList.remove('active'));
                this.classList.add('active');
                document.querySelector(`#tab-${tab}`).classList.add('active');
                if (tab === 'adres' && window.manageMap) {
                    setTimeout(() => window.manageMap.invalidateSize(), 50);
                }
            });
        });

        document.addEventListener('change', (event) => {
            if (!event.target.matches('[data-choice-select]')) return;
            const other = event.target.closest('[data-choice-wrapper]')?.querySelector('[data-choice-other]');
            if (other) other.hidden = event.target.value !== otherValue;
        });

        document.addEventListener('click', (event) => {
            const remove = event.target.closest('.repeat-remove');
            if (remove) {
                remove.closest('.repeat-row')?.remove();
                return;
            }
            const add = event.target.closest('[data-add-repeat]');
            if (add) addRepeatRow(add.dataset.addRepeat);
        });

        function addRepeatRow(type) {
            const list = document.querySelector(`[data-repeat-list="${type}"]`);
            const source = list.querySelector('.repeat-row');
            const clone = source.cloneNode(true);
            const index = list.querySelectorAll('.repeat-row').length;
            clone.querySelectorAll('input, select').forEach((field) => {
                field.name = field.name.replace(/\[\d+\]/, `[${index}]`);
                field.value = '';
                if (field.matches('[data-choice-other]')) field.hidden = true;
            });
            list.appendChild(clone);
        }

        const latInput = document.querySelector('#address-lat');
        const lonInput = document.querySelector('#address-lon');
        const initialLat = Number(latInput.value || 50.85);
        const initialLon = Number(lonInput.value || 4.35);
        window.manageMap = L.map('manage-address-map').setView([initialLat, initialLon], latInput.value && lonInput.value ? 15 : 8);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
            maxZoom: 18,
        }).addTo(window.manageMap);
        const marker = L.marker([initialLat, initialLon], { draggable: true }).addTo(window.manageMap);

        marker.on('dragend', () => {
            const position = marker.getLatLng();
            latInput.value = position.lat.toFixed(8);
            lonInput.value = position.lng.toFixed(8);
        });
        window.manageMap.on('click', (event) => {
            marker.setLatLng(event.latlng);
            latInput.value = event.latlng.lat.toFixed(8);
            lonInput.value = event.latlng.lng.toFixed(8);
        });
        [latInput, lonInput].forEach((input) => input.addEventListener('input', () => {
            const lat = Number(latInput.value);
            const lon = Number(lonInput.value);
            if (!Number.isNaN(lat) && !Number.isNaN(lon)) {
                marker.setLatLng([lat, lon]);
                window.manageMap.setView([lat, lon], 15);
            }
        }));
    </script>
@endpush
