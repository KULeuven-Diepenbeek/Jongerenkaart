<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AdminContentPageController;
use App\Http\Controllers\AdminPeopleController;
use App\Http\Controllers\AdminSettingsController;
use App\Http\Controllers\AdminStatisticsController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\ContactPageController;
use App\Http\Controllers\ContentPageController;
use App\Http\Controllers\HomePageController;
use App\Http\Controllers\MapController;
use App\Http\Controllers\OrganisatieController;
use App\Http\Controllers\OrganisatiePageController;
use App\Http\Controllers\OrganisationManagementController;
use App\Http\Controllers\SearchPageController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomePageController::class)->name('home');

Route::get('/info', [ContentPageController::class, 'info'])->name('info');
Route::get('/contact', ContactPageController::class)->name('contact');
Route::get('/faq', [ContentPageController::class, 'faq'])->name('faq');
Route::get('/privacy', [ContentPageController::class, 'privacy'])->name('privacy');
Route::get('/kaart', [MapController::class, 'index'])->name('map');
Route::get('/kaart/places', [MapController::class, 'places'])->name('map.places');
Route::get('/kaart/results', [MapController::class, 'results'])->name('map.results');
Route::get('/search', SearchPageController::class)->name('search');
Route::get('/search/results', [OrganisatieController::class, 'search'])->name('search.results');
Route::get('/organisaties/{id}', [OrganisatiePageController::class, 'show'])->name('organisaties.show');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');
    Route::get('/account-aanmaken', [RegisterController::class, 'create'])->name('register.create');
    Route::post('/account-aanmaken', [RegisterController::class, 'store'])->name('register.store');
});

Route::middleware(['auth', 'session.fresh'])->group(function (): void {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');
    Route::get('/mijn-account', [AccountController::class, 'show'])->name('account.show');
    Route::put('/wachtwoord-wijzigen', [PasswordController::class, 'update'])->name('password.update');
    Route::get('/beheer/organisaties', [OrganisationManagementController::class, 'index'])->name('manage.organisations.index');
    Route::get('/beheer/organisaties/nieuw', [OrganisationManagementController::class, 'create'])->name('manage.organisations.create');
    Route::post('/beheer/organisaties', [OrganisationManagementController::class, 'store'])->name('manage.organisations.store');
    Route::get('/beheer/organisaties/{organisatie}/bewerken', [OrganisationManagementController::class, 'edit'])->name('manage.organisations.edit');
    Route::put('/beheer/organisaties/{organisatie}', [OrganisationManagementController::class, 'update'])->name('manage.organisations.update');

    Route::middleware('role:admin')->group(function (): void {
        Route::delete('/beheer/organisaties/{organisatie}', [OrganisationManagementController::class, 'destroy'])->name('manage.organisations.destroy');
        Route::get('/beheer/statistieken', [AdminStatisticsController::class, 'index'])->name('admin.statistics.index');
        Route::post('/beheer/statistieken/bijwerken', [AdminStatisticsController::class, 'refresh'])->name('admin.statistics.refresh');
        Route::get('/beheer/instellingen', [AdminSettingsController::class, 'index'])->name('admin.settings.index');
        Route::post('/beheer/instellingen/check', [AdminSettingsController::class, 'check'])->name('admin.settings.check');
        Route::post('/beheer/instellingen/cache', [AdminSettingsController::class, 'rebuildCache'])->name('admin.settings.cache');
        Route::post('/beheer/instellingen/search', [AdminSettingsController::class, 'rebuildSearch'])->name('admin.settings.search');
        Route::post('/beheer/instellingen/alles', [AdminSettingsController::class, 'refreshAll'])->name('admin.settings.all');
        Route::put('/beheer/instellingen/zoekgedrag', [AdminSettingsController::class, 'updateSearchBehaviour'])->name('admin.settings.search-behaviour');
        Route::put('/beheer/instellingen/contactadres', [AdminSettingsController::class, 'updateContactEmail'])->name('admin.settings.contact-email');
        Route::get('/beheer/instellingen/backup', [AdminSettingsController::class, 'exportBackup'])->name('admin.settings.backup');
        Route::get('/beheer/instellingen/mail-organisaties', [AdminSettingsController::class, 'mailOrganisaties'])->name('admin.settings.mail-organisaties');
        Route::post('/beheer/medewerkers', [AdminPeopleController::class, 'storeMedewerker'])->name('admin.medewerkers.store');
        Route::put('/beheer/medewerkers/{medewerker}', [AdminPeopleController::class, 'updateMedewerker'])->name('admin.medewerkers.update');
        Route::delete('/beheer/medewerkers/{medewerker}', [AdminPeopleController::class, 'destroyMedewerker'])->name('admin.medewerkers.destroy');
        Route::post('/beheer/gebruikers/admins', [AdminPeopleController::class, 'createAdmin'])->name('admin.users.admins.store');
        Route::post('/beheer/gebruikers/{user}/activeren', [AdminPeopleController::class, 'activateUser'])->name('admin.users.activate');
        Route::put('/beheer/gebruikers/{user}/wachtwoord', [AdminPeopleController::class, 'resetUserPassword'])->name('admin.users.password');
        Route::delete('/beheer/gebruikers/{user}', [AdminPeopleController::class, 'destroyUser'])->name('admin.users.destroy');
        Route::get('/beheer/paginas/{slug}/bewerken', [AdminContentPageController::class, 'edit'])->name('admin.pages.edit');
        Route::put('/beheer/paginas/{slug}', [AdminContentPageController::class, 'update'])->name('admin.pages.update');
        Route::delete('/beheer/paginas/{slug}', [AdminContentPageController::class, 'reset'])->name('admin.pages.reset');
    });
});

Route::get('/tests/search', [OrganisatieController::class, 'search']);
Route::get('/tests/filter-options', [OrganisatieController::class, 'filterOptions']);

Route::get('/tests', function () {
    return response(<<<'HTML'
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Organisatie Search</title>
    <style>
        body { font-family: system-ui, sans-serif; margin: 2rem; max-width: 760px; }
        input, select { box-sizing: border-box; font: inherit; padding: .75rem; width: 100%; }
        .filters { display: grid; gap: .75rem; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); margin-top: 1rem; }
        label { display: grid; gap: .25rem; font-size: .85rem; }
        ul { list-style: none; padding: 0; }
        li { border-bottom: 1px solid #ddd; padding: 1rem 0; }
        h2 { font-size: 1rem; margin: 0 0 .25rem; }
        p { margin: .25rem 0; }
        .muted { color: #666; font-size: .9rem; }
        .match { color: #075fc4; font-weight: 700; }
    </style>
</head>
<body>
    <input id="search" type="search" placeholder="Search organisations..." autocomplete="off" autofocus>
    <div id="filters" class="filters"></div>
    <p id="status" class="muted"></p>
    <ul id="results"></ul>

    <script>
        const input = document.querySelector('#search');
        const filters = document.querySelector('#filters');
        const status = document.querySelector('#status');
        const results = document.querySelector('#results');
        const filterLabels = {
            leeftijdscategorie: 'Leeftijd',
            rubriek: 'Rubriek',
            werkingsgebieden: 'Werkingsgebied',
            taalAanbod: 'Taal',
            betalingswijze: 'Betaling',
            aanbod: 'Aanbod',
        };
        let timer;
        let controller;

        input.addEventListener('input', () => {
            clearTimeout(timer);
            timer = setTimeout(search, 200);
        });

        filters.addEventListener('change', () => {
            clearTimeout(timer);
            timer = setTimeout(search, 100);
        });

        loadFilters();

        async function loadFilters() {
            const response = await fetch('/tests/filter-options', {
                headers: { Accept: 'application/json' },
            });
            const data = await response.json();

            filters.innerHTML = Object.entries(filterLabels)
                .map(([field, label]) => renderFilter(field, label, data[field] ?? []))
                .join('');
        }

        async function search() {
            const query = input.value.trim();
            results.innerHTML = '';

            if (!query) {
                status.textContent = '';
                return;
            }

            if (controller) {
                controller.abort();
            }

            controller = new AbortController();
            status.textContent = 'Searching...';
            const params = new URLSearchParams({ q: query });

            document.querySelectorAll('[data-filter]').forEach((select) => {
                if (select.value) {
                    params.set(select.dataset.filter, select.value);
                }
            });

            try {
                const response = await fetch(`/tests/search?${params.toString()}`, {
                    signal: controller.signal,
                    headers: { Accept: 'application/json' },
                });

                const data = await response.json();
                status.textContent = data.length ? '' : 'No results';
                results.innerHTML = data.map((organisatie) => renderResult(organisatie, query)).join('');
            } catch (error) {
                if (error.name !== 'AbortError') {
                    status.textContent = 'Search failed';
                }
            }
        }

        function renderFilter(field, label, options) {
            return `
                <label>
                    ${escapeHtml(label)}
                    <select data-filter="${escapeHtml(field)}">
                        <option value="">All</option>
                        ${options.map((option) => `<option value="${escapeHtml(option)}">${escapeHtml(option)}</option>`).join('')}
                    </select>
                </label>
            `;
        }

        function renderResult(organisatie, query) {
            return `
                <li>
                    <h2>${highlight(organisatie.name ?? 'Untitled', query)}</h2>
                    <p class="muted">${highlight(organisatie.category ?? '', query)}</p>
                    <p>${highlight(organisatie.description ?? '', query)}</p>
                </li>
            `;
        }

        function highlight(value, query) {
            const text = escapeHtml(value);
            const needle = escapeHtml(query);

            if (!needle) {
                return text;
            }

            const pattern = new RegExp(escapeRegExp(needle), 'gi');

            return text.replace(pattern, (match) => `<strong class="match">${match}</strong>`);
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
</body>
</html>
HTML);
});
