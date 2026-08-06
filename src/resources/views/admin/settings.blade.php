@extends('layouts.app')

@section('title', 'Instellingen')

@php
    $statusItems = [];

    if (is_array($status)) {
        $statusItems = array_values(array_filter(array_merge(
            $status['items'] ?? [],
            $status['cache_items'] ?? [],
            $status['search_items'] ?? [],
        )));
    }
@endphp

@section('content')
    <section class="manage-page">
        <div class="manage-heading">
            <div>
                <p class="eyebrow">Admin</p>
                <h1>Instellingen</h1>
            </div>
        </div>

        @if (is_array($status))
            <div class="settings-status {{ ($status['type'] ?? null) === 'error' ? 'settings-status-error' : '' }}">
                <strong>{{ $status['title'] ?? 'Actie uitgevoerd' }}</strong>
                @if ($statusItems)
                    <ul>
                        @foreach ($statusItems as $item)
                            <li>{{ $item }}</li>
                        @endforeach
                    </ul>
                @endif
            </div>
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

        <div class="settings-help">
            <h2>Hoe gebruik je deze pagina?</h2>
            <p>Gebruik deze pagina voor onderhoud, backups en beheer van mensen met toegang tot de beheerschermen. Alleen admins kunnen deze pagina openen en alleen admins kunnen nieuwe admin gebruikers aanmaken.</p>
            <ul>
                <li><strong>Database controleren</strong> verandert niets en is veilig om eerst te gebruiken.</li>
                <li><strong>Contactadres</strong> bepaalt aan wie berichten vanaf de publieke contactpagina worden gericht.</li>
                <li><strong>Dropdown opties opnieuw opbouwen</strong> scant MongoDB opnieuw en herstelt de aparte option-count collectie plus cache.</li>
                <li><strong>Zoekindex vernieuwen</strong> gebruik je wanneer zoeken of kaartresultaten niet overeenkomen met MongoDB.</li>
                <li><strong>Reactief zoeken</strong> bepaalt of typen meteen zoekt op de zoek- en kaartpagina. Schakel dit uit om het aantal zoekaanvragen te beperken; bezoekers zoeken dan met Enter.</li>
                <li><strong>Backup exporteren</strong> downloadt alle organisaties uit MongoDB als JSON-bestand.</li>
                <li><strong>Mail alle organisaties</strong> opent je mailprogramma met alle algemene en contactpersoon e-mailadressen in BCC. Bij zeer veel adressen kan je mailprogramma of browser dit weigeren.</li>
                <li><strong>Gebruikers</strong> toont nieuwe accountaanvragen bovenaan. Controleer de identiteit en organisatie van de aanvrager voordat je het account activeert. Onjuiste of ongewenste aanvragen kun je verwijderen.</li>
            </ul>
        </div>

        <div class="settings-grid">
            <article class="settings-card settings-contact-address">
                <div>
                    <h2>Contactadres website</h2>
                    <p>De contactpagina opent het standaard mailprogramma van de bezoeker met dit adres als ontvanger. Bij een schone installatie is dit <strong>{{ \App\Models\SiteSetting::DEFAULT_CONTACT_EMAIL }}</strong>.</p>
                </div>
                <form method="POST" action="{{ route('admin.settings.contact-email') }}" class="settings-contact-form">
                    @csrf
                    @method('PUT')
                    <label for="settings-contact-email">E-mailadres</label>
                    <input
                        id="settings-contact-email"
                        type="email"
                        name="contact_email"
                        value="{{ old('contact_email', $contactEmail) }}"
                        autocomplete="email"
                        required
                    >
                    <button type="submit" class="btn btn-primary">Contactadres opslaan</button>
                </form>
            </article>

            <article class="settings-card settings-search-behaviour">
                <div>
                    <h2>Reactief zoeken</h2>
                    <p>Bij inschakelen worden resultaten tijdens het typen vernieuwd. Bij uitschakelen wordt pas gezocht wanneer de bezoeker op Enter drukt. Filters blijven direct werken.</p>
                </div>
                <form method="POST" action="{{ route('admin.settings.search-behaviour') }}">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="reactive_search_enabled" value="0">
                    <label class="settings-switch">
                        <input
                            type="checkbox"
                            name="reactive_search_enabled"
                            value="1"
                            @checked($reactiveSearchEnabled)
                            onchange="this.form.submit()"
                        >
                        <span class="settings-switch-track" aria-hidden="true"></span>
                        <span>{{ $reactiveSearchEnabled ? 'Ingeschakeld' : 'Uitgeschakeld' }}</span>
                    </label>
                </form>
            </article>

            <article class="settings-card">
                <div>
                    <h2>Database controleren</h2>
                    <p>Controleert of MongoDB en Meilisearch bereikbaar zijn, toont aantallen en laat zien of de dropdown cache al bestaat. Deze knop past niets aan.</p>
                </div>
                <form method="POST" action="{{ route('admin.settings.check') }}">
                    @csrf
                    <button type="submit" class="btn btn-outline-primary">Controle uitvoeren</button>
                </form>
            </article>

            <article class="settings-card">
                <div>
                    <h2>Dropdown opties opnieuw opbouwen</h2>
                    <p>Herbouwt de aparte option-count collectie en cache vanuit MongoDB. Normale edits doen dit niet volledig, maar werken alleen de gewijzigde waarden bij.</p>
                </div>
                <form method="POST" action="{{ route('admin.settings.cache') }}">
                    @csrf
                    <button type="submit" class="btn btn-primary">Opties opnieuw opbouwen</button>
                </form>
            </article>

            <article class="settings-card">
                <div>
                    <h2>Zoekindex vernieuwen</h2>
                    <p>Synchroniseert Meilisearch instellingen, leegt de oude index en importeert alle organisaties opnieuw vanuit MongoDB.</p>
                </div>
                <form method="POST" action="{{ route('admin.settings.search') }}">
                    @csrf
                    <button type="submit" class="btn btn-primary">Zoekindex vernieuwen</button>
                </form>
            </article>

            <article class="settings-card settings-card-strong">
                <div>
                    <h2>Alles controleren en vernieuwen</h2>
                    <p>Voert de volledige onderhoudsstap uit: databasecontrole, cache opnieuw opbouwen en zoekindex opnieuw opbouwen.</p>
                </div>
                <form method="POST" action="{{ route('admin.settings.all') }}">
                    @csrf
                    <button type="submit" class="btn btn-primary">Alles uitvoeren</button>
                </form>
            </article>

            <article class="settings-card">
                <div>
                    <h2>MongoDB backup exporteren</h2>
                    <p>Maakt een volledig JSON-bestand van de organisaties in MongoDB. De download start in je browser; de pop-up blijft zichtbaar terwijl de export wordt voorbereid.</p>
                </div>
                <a href="{{ route('admin.settings.backup') }}" class="btn btn-primary" data-backup-download data-download-name="jongerenkaart-mongodb-organisaties.json">Backup downloaden</a>
            </article>

            <article class="settings-card">
                <div>
                    <h2>Mail alle organisaties</h2>
                    <p>Opent je standaard mailprogramma met {{ $organisationMailCount }} unieke e-mailadressen in BCC en als onderwerp <strong>Jongerenkaart:</strong>.</p>
                </div>
                <a href="{{ route('admin.settings.mail-organisaties') }}" class="btn btn-outline-primary">Nieuwe e-mail maken</a>
            </article>
        </div>

        <section class="settings-section">
            <div class="settings-section-heading">
                <div>
                    <h2>Publieke pagina's</h2>
                    <p>Bewerk de inhoud van Info, Privacy en FAQ. Reset verwijdert de aangepaste versie en gebruikt opnieuw de standaardpagina uit de code.</p>
                </div>
            </div>

            <div class="settings-page-list">
                @foreach ($editablePages as $slug => $label)
                    <div class="settings-page-row">
                        <div>
                            <strong>{{ $label }}</strong>
                            <span>{{ in_array($slug, $customPageSlugs, true) ? 'Aangepaste inhoud actief' : 'Standaardinhoud actief' }}</span>
                        </div>
                        <div class="settings-row-actions">
                            <a href="{{ route('admin.pages.edit', ['slug' => $slug]) }}" class="btn btn-outline-primary btn-sm">Bewerk</a>
                            <a href="{{ route($slug) }}" class="btn btn-outline-primary btn-sm">Bekijk</a>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        <section class="settings-section">
            <div class="settings-section-heading">
                <div>
                    <h2>Medewerkers publieke info pagina</h2>
                    <p>Deze personen verschijnen op de info pagina bij Team Sociale Kaart.</p>
                </div>
            </div>

            <div class="settings-table">
                @foreach ($medewerkers as $medewerker)
                    <div class="settings-table-row">
                        <form id="medewerker-update-{{ $medewerker->getKey() }}" method="POST" action="{{ route('admin.medewerkers.update', ['medewerker' => (string) $medewerker->getKey()]) }}" class="settings-inline-form">
                            @csrf
                            @method('PUT')
                            <input name="regio" value="{{ old('regio', $medewerker->regio) }}" placeholder="Regio" required>
                            <input name="naam" value="{{ old('naam', $medewerker->naam) }}" placeholder="Naam" required>
                            <input type="email" name="email" value="{{ old('email', $medewerker->email) }}" placeholder="E-mail" required>
                        </form>
                        <div class="settings-row-actions">
                            <button type="submit" class="btn btn-outline-primary btn-sm" form="medewerker-update-{{ $medewerker->getKey() }}">Opslaan</button>
                            <form method="POST" action="{{ route('admin.medewerkers.destroy', ['medewerker' => (string) $medewerker->getKey()]) }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger btn-sm">Verwijder</button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>

            <form method="POST" action="{{ route('admin.medewerkers.store') }}" class="settings-create-form">
                @csrf
                <input name="regio" placeholder="Regio" required>
                <input name="naam" placeholder="Naam" required>
                <input type="email" name="email" placeholder="E-mail" required>
                <button type="submit" class="btn btn-primary">Medewerker toevoegen</button>
            </form>
        </section>

        <section class="settings-section">
            <div class="settings-section-heading">
                <div>
                    <h2>Gebruikers</h2>
                    <p>Bekijk admins en contactpersonen, activeer nieuwe aanvragen, reset wachtwoorden of verwijder accounts. Een nieuw contactpersoonaccount kan pas inloggen nadat een admin het hier heeft geactiveerd.</p>
                </div>
                <form method="GET" action="{{ route('admin.settings.index') }}" class="settings-search-form">
                    <input type="search" name="user_q" value="{{ $userQuery }}" placeholder="Zoek gebruiker">
                    <button type="submit" class="btn btn-outline-primary btn-sm">Zoek</button>
                </form>
            </div>

            <div class="settings-user-list">
                @foreach ($users as $user)
                    <div class="settings-user-row">
                        <div>
                            <strong>{{ $user->name }}</strong>
                            <span>{{ $user->email }}</span>
                        </div>
                        <span class="settings-role-badge">{{ $user->role === \App\Models\User::ROLE_ADMIN ? 'Admin' : 'Contactpersoon' }}</span>
                        @if (! $user->isActive())
                            <span class="badge text-bg-warning">Wacht op activatie</span>
                            <form method="POST" action="{{ route('admin.users.activate', ['user' => (string) $user->getKey()]) }}">
                                @csrf
                                <button type="submit" class="btn btn-primary btn-sm">Activeer</button>
                            </form>
                        @else
                            <span class="badge text-bg-success">Actief</span>
                        @endif
                        <form method="POST" action="{{ route('admin.users.password', ['user' => (string) $user->getKey()]) }}" class="settings-password-form">
                            @csrf
                            @method('PUT')
                            <input type="password" name="password" placeholder="Nieuw wachtwoord" required>
                            <input type="password" name="password_confirmation" placeholder="Herhaal" required>
                            <button type="submit" class="btn btn-outline-primary btn-sm">Reset</button>
                        </form>
                        @if ((string) auth()->id() !== (string) $user->getKey())
                            <form method="POST" action="{{ route('admin.users.destroy', ['user' => (string) $user->getKey()]) }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger btn-sm">Verwijder</button>
                            </form>
                        @else
                            <span class="settings-self-note">Je huidige account</span>
                        @endif
                    </div>
                @endforeach
            </div>

            <form method="POST" action="{{ route('admin.users.admins.store') }}" class="settings-create-form settings-admin-create">
                @csrf
                <input name="name" placeholder="Naam admin" required>
                <input type="email" name="email" placeholder="E-mail admin" required>
                <input type="password" name="password" placeholder="Wachtwoord" required>
                <input type="password" name="password_confirmation" placeholder="Herhaal wachtwoord" required>
                <button type="submit" class="btn btn-primary">Admin gebruiker maken</button>
            </form>
        </section>

        <div class="settings-progress-modal" data-backup-modal hidden>
            <div>
                <strong data-backup-modal-title>Backup wordt voorbereid</strong>
                <p data-backup-modal-text>De download start zodra MongoDB het JSON-bestand klaar heeft.</p>
                <div class="settings-progress-bar"><span></span></div>
                <button type="button" class="btn btn-outline-primary btn-sm" data-backup-modal-close>Sluiten</button>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        const backupLink = document.querySelector('[data-backup-download]');
        const backupModal = document.querySelector('[data-backup-modal]');
        const backupClose = document.querySelector('[data-backup-modal-close]');

        const backupTitle = document.querySelector('[data-backup-modal-title]');
        const backupText = document.querySelector('[data-backup-modal-text]');

        backupLink?.addEventListener('click', async (event) => {
            event.preventDefault();
            backupModal.hidden = false;

            if (backupTitle) backupTitle.textContent = 'Backup wordt voorbereid';
            if (backupText) backupText.textContent = 'De download start zodra MongoDB het JSON-bestand klaar heeft.';

            try {
                const response = await fetch(backupLink.href, {
                    headers: {
                        Accept: 'application/json',
                    },
                });

                if (!response.ok) {
                    throw new Error('Backup kon niet worden gedownload.');
                }

                const disposition = response.headers.get('Content-Disposition') || '';
                const filenameMatch = disposition.match(/filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/);
                const filename = filenameMatch
                    ? filenameMatch[1].replaceAll('"', '').replaceAll("'", '')
                    : backupLink.dataset.downloadName;
                const blob = await response.blob();
                const url = URL.createObjectURL(blob);
                const download = document.createElement('a');

                download.href = url;
                download.download = filename;
                document.body.appendChild(download);
                download.click();
                download.remove();
                URL.revokeObjectURL(url);

                if (backupTitle) backupTitle.textContent = 'Backup klaar';
                if (backupText) backupText.textContent = 'De download is gestart.';
                setTimeout(() => {
                    backupModal.hidden = true;
                }, 1200);
            } catch (error) {
                if (backupTitle) backupTitle.textContent = 'Backup mislukt';
                if (backupText) backupText.textContent = error.message || 'Probeer opnieuw of controleer de logs.';
            }
        });

        backupClose?.addEventListener('click', () => {
            backupModal.hidden = true;
        });
    </script>
@endpush
