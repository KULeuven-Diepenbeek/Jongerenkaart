@extends('layouts.app')

@section('title', 'Statistieken')

@php
    $data = $snapshot?->data ?? [];
    $summary = $data['summary'] ?? [];
    $daily = $data['daily'] ?? [];
    $topPages = $data['top_pages'] ?? [];
    $devices = $data['devices'] ?? [];
    $changes = $data['organisation_changes'] ?? [];
    $maxDailyViews = max([1, ...array_map(fn ($day) => (int) ($day['page_views'] ?? 0), $daily)]);
    $deviceTotal = max(1, array_sum($devices));
@endphp

@section('content')
    <section class="manage-page statistics-page">
        <header class="manage-heading statistics-heading">
            <div>
                <p class="eyebrow">Admin</p>
                <h1>Statistieken</h1>
            </div>
            <form method="POST" action="{{ route('admin.statistics.refresh') }}">
                @csrf
                <button type="submit" class="btn btn-primary">Dashboard bijwerken</button>
            </form>
        </header>

        @if (session('status'))
            <div class="settings-status" role="status">{{ session('status') }}</div>
        @endif

        <div class="statistics-intro">
            @if ($snapshot)
                <p><strong>Laatst bijgewerkt:</strong> {{ $snapshot->generated_at->timezone('Europe/Brussels')->format('d/m/Y H:i') }}</p>
                <p>De cijfers hieronder blijven ongewijzigd tot een admin opnieuw op <strong>Dashboard bijwerken</strong> klikt.</p>
            @else
                <p><strong>Er is nog geen momentopname.</strong> Gebruik de knop om het dashboard voor het eerst op te bouwen.</p>
            @endif
            <p class="statistics-privacy">De meting gebeurt lokaal in Laravel. Er worden geen IP-adressen, volledige browsergegevens of ingevoerde zoekteksten opgeslagen. Gelijktijdig actief betekent: gezien in de laatste 5 minuten op het moment van bijwerken.</p>
        </div>

        @if ($snapshot)
            <div class="statistics-kpi-grid" aria-label="Belangrijkste statistieken">
                <article class="statistics-kpi">
                    <span>Actieve bezoekers</span>
                    <strong>{{ number_format($summary['concurrent_visitors'] ?? 0, 0, ',', '.') }}</strong>
                    <small>laatste 5 minuten</small>
                </article>
                <article class="statistics-kpi">
                    <span>Actieve logins</span>
                    <strong>{{ number_format($summary['concurrent_logins'] ?? 0, 0, ',', '.') }}</strong>
                    <small>laatste 5 minuten</small>
                </article>
                <article class="statistics-kpi">
                    <span>Bezoekers vandaag</span>
                    <strong>{{ number_format($summary['today_unique_visitors'] ?? 0, 0, ',', '.') }}</strong>
                    <small>{{ number_format($summary['today_page_views'] ?? 0, 0, ',', '.') }} paginaweergaven</small>
                </article>
                <article class="statistics-kpi">
                    <span>Bezoekers in 7 dagen</span>
                    <strong>{{ number_format($summary['seven_day_unique_visitors'] ?? 0, 0, ',', '.') }}</strong>
                    <small>{{ number_format($summary['seven_day_page_views'] ?? 0, 0, ',', '.') }} paginaweergaven</small>
                </article>
                <article class="statistics-kpi">
                    <span>Zoekopdrachten</span>
                    <strong>{{ number_format($summary['seven_day_search_requests'] ?? 0, 0, ',', '.') }}</strong>
                    <small>laatste 7 dagen</small>
                </article>
                <article class="statistics-kpi">
                    <span>Kaartaanvragen</span>
                    <strong>{{ number_format($summary['seven_day_map_requests'] ?? 0, 0, ',', '.') }}</strong>
                    <small>laatste 7 dagen</small>
                </article>
                <article class="statistics-kpi">
                    <span>Organisaties</span>
                    <strong>{{ number_format($summary['organisations'] ?? 0, 0, ',', '.') }}</strong>
                    <small>huidig totaal</small>
                </article>
                <article class="statistics-kpi">
                    <span>Gebruikers</span>
                    <strong>{{ number_format($summary['users'] ?? 0, 0, ',', '.') }}</strong>
                    <small>{{ $summary['admins'] ?? 0 }} admins, {{ $summary['contact_users'] ?? 0 }} contactpersonen</small>
                </article>
            </div>

            <section class="statistics-section" aria-labelledby="statistics-trend-title">
                <div class="statistics-section-heading">
                    <div>
                        <h2 id="statistics-trend-title">Verkeer per dag</h2>
                        <p>Paginaweergaven en unieke bezoekers van de laatste 14 dagen.</p>
                    </div>
                    <p class="statistics-period">Start meting: {{ ($data['tracking_started_at'] ?? null) ? \Illuminate\Support\Carbon::parse($data['tracking_started_at'])->format('d/m/Y') : 'vandaag' }}</p>
                </div>
                <div class="statistics-chart" role="img" aria-label="Paginaweergaven per dag over de laatste 14 dagen">
                    @foreach ($daily as $day)
                        @php $height = max(3, (int) round((($day['page_views'] ?? 0) / $maxDailyViews) * 100)); @endphp
                        <div class="statistics-chart-column" title="{{ $day['label'] }}: {{ $day['page_views'] }} weergaven, {{ $day['unique_visitors'] }} bezoekers">
                            <span class="statistics-chart-value">{{ $day['page_views'] }}</span>
                            <span class="statistics-chart-bar" style="height: {{ $height }}%"></span>
                            <small>{{ $day['label'] }}</small>
                        </div>
                    @endforeach
                </div>
            </section>

            <div class="statistics-columns">
                <section class="statistics-section" aria-labelledby="statistics-pages-title">
                    <h2 id="statistics-pages-title">Meest bekeken pagina's</h2>
                    <p>Publieke paginaweergaven van de laatste 30 dagen.</p>
                    <ol class="statistics-ranking">
                        @forelse ($topPages as $page)
                            <li><span>{{ $page['label'] }}</span><strong>{{ number_format($page['views'], 0, ',', '.') }}</strong></li>
                        @empty
                            <li class="statistics-empty">Nog geen paginaweergaven gemeten.</li>
                        @endforelse
                    </ol>
                </section>

                <section class="statistics-section" aria-labelledby="statistics-devices-title">
                    <h2 id="statistics-devices-title">Apparaten</h2>
                    <p>Globale apparaattypeherkenning over de laatste 30 dagen.</p>
                    <div class="statistics-device-list">
                        @foreach (['mobile' => 'Smartphone', 'tablet' => 'Tablet', 'desktop' => 'Laptop/desktop'] as $key => $label)
                            @php
                                $count = (int) ($devices[$key] ?? 0);
                                $percentage = (int) round(($count / $deviceTotal) * 100);
                            @endphp
                            <div class="statistics-device-row">
                                <div><span>{{ $label }}</span><strong>{{ $count }} ({{ $percentage }}%)</strong></div>
                                <span class="statistics-device-track"><span style="width: {{ $percentage }}%"></span></span>
                            </div>
                        @endforeach
                    </div>
                </section>
            </div>

            <div class="statistics-columns">
                <section class="statistics-section" aria-labelledby="statistics-health-title">
                    <h2 id="statistics-health-title">Technische signalen</h2>
                    <dl class="statistics-definition-list">
                        <div><dt>Gemiddelde responstijd</dt><dd>{{ $summary['seven_day_average_response_ms'] ?? 0 }} ms</dd></div>
                        <div><dt>Foutresponses</dt><dd>{{ $summary['seven_day_errors'] ?? 0 }}</dd></div>
                        <div><dt>Botaanvragen</dt><dd>{{ $summary['seven_day_bot_requests'] ?? 0 }}</dd></div>
                    </dl>
                    <p class="statistics-footnote">Gebaseerd op de laatste 7 dagen. Gebruik serverlogs voor diepgaand technisch onderzoek.</p>
                </section>

                <section class="statistics-section" aria-labelledby="statistics-changes-title">
                    <h2 id="statistics-changes-title">Organisatiebeheer</h2>
                    <dl class="statistics-definition-list">
                        <div><dt>Aangemaakt</dt><dd>{{ $changes['created'] ?? 0 }}</dd></div>
                        <div><dt>Bijgewerkt</dt><dd>{{ $changes['updated'] ?? 0 }}</dd></div>
                        <div><dt>Verwijderd</dt><dd>{{ $changes['deleted'] ?? 0 }}</dd></div>
                    </dl>
                    <p class="statistics-footnote">Wijzigingen via Laravel in de laatste 30 dagen.</p>
                </section>
            </div>
        @endif
    </section>
@endsection
