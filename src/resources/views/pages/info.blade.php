@extends('layouts.app')

@section('content')
    <x-admin.page-edit-bar slug="info" :content-page="$contentPage ?? null" />
    @if (! empty($contentHtml))
        <div class="editable-content-page">
            {!! $contentHtml !!}
        </div>
    @else
    <div class="title-banner">
        <h1>
            Uw gids door<br><span style="color: var(--jk-primary)">De Jongerenkaart.</span>
        </h1>
        <p>
            De Vlaamse Sociale Kaart informeert elke burger en zorgverstrekker over het beschikbare zorg- en hulpaanbod in Vlaanderen en Brussel. Van kinderopvang en huisartsen tot gespecialiseerde verslavingshulp. De jongerenkaart is een gespecialiseerde versie die de drempel voor jongeren die hulp zoeken te verlagen.
        </p>
        <a href="{{ route('contact') }}" class="contact-btn">
            Contacteer ons team
        </a>
    </div>

    <div class="padding-side info-intro-section">
        <div class="content-header">
            <h2>Inleiding</h2>
            <p>
                U kan zoeken naar antwoorden op zorgvragen zoals, "waar vind ik kinderopvang in mijn gemeente?", "welke huisartsen zijn er in mijn buurt?", "waar vindt een verslaafd familielid hulp?", enz. Ook hulpverleners kunnen het instrument gebruiken om gericht door te verwijzen. De lokale besturen krijgen een overzicht van de welzijns-en zorgactoren op hun grondgebied. Neem zeker steeds contact op met de zorgaanbieder zelf om uw zorgvraag op te lossen.
            </p>
        </div>

        <div class="content">
            <div class="left">
                <h2>Wie staat op de Sociale kaart en Jongerenkaart?</h2>
                <p>
                    Welke zorgvoorzieningen en gezondheidszorgverstrekkers wel/niet opgenomen worden in de Sociale kaart wordt verankerd in het <a rel="noopener" target="_blank" ref="https://codex.vlaanderen.be/Zoeken/Document.aspx?DID=1031834&amp;param=inhoud">Decreet houdende de Sociale Kaart van 3 mei '19</a> (B.S. 26/06/19), artikel 2. Op basis van het decreet wordt de scope van de Sociale Kaart bepaald.
                </p>

                <div class="card-data">
                    <div class="info-card-header">
                        <i class="fa-solid fa-square-poll-vertical"></i>
                        <h3>Beschikbare Data</h3>
                    </div>
                    <ul class="data-list">
                        <li><i class="fa-solid fa-circle-check" style="color: green"></i> Contactgegevens</li>
                        <li><i class="fa-solid fa-circle-check" style="color: green"></i> Adresgegevens</li>
                        <li><i class="fa-solid fa-circle-check" style="color: green"></i> Aanbodomschrijving</li>
                        <li><i class="fa-solid fa-circle-check" style="color: green"></i> Doelgroepen</li>
                        <li><i class="fa-solid fa-circle-check" style="color: green"></i> Openingsuren</li>
                        <li><i class="fa-solid fa-circle-check" style="color: green"></i> Toegankelijkheid</li>
                    </ul>
                </div>
            </div>

            <div class="right">
                <h2>Kwaliteit door Connectie</h2>
                <p>We combineren <span style="font-weight: bold">authentieke gegevensbronnen</span> met handmatige updates voor maximale betrouwbaarheid.</p>
                <div class="grid">
                    <div class="small-info-card info-source-card">
                        <h3>AUTHENTIEKE BRONNEN</h3>
                        <p>KBO, Erkenningsdata (WVG), RIZIV, Adresregister/Urbis.</p>
                    </div>

                    <div class="small-info-card info-partner-card">
                        <h3>PARTNERSHIPS</h3>
                        <p>Departement Zorg, VGC, Kenniscentrum WWZ, Zorggraden, Opgroeien, VAPH.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @endif

    <div class="info-university-band" aria-label="UHasselt">
        <div class="container text-center">
            <img
                src="{{ asset('uhasselt-liggend-wit.png') }}"
                alt="UHasselt"
                class="info-university-logo"
            >
        </div>
    </div>

    <div class="padding-side contact">
        <div class="contact-header">
            <h1>Team Sociale Kaart</h1>
            <p>Onze regiobeheerders zorgen ervoor dat de kaart in elke uithoek van Vlaanderen actueel blijft.</p>
        </div>

        <div class="contact-grid">
            @foreach ($medewerkers as $medewerker)
                <x-medewerker-card :medewerker="$medewerker" />
            @endforeach
        </div>
    </div>
@endsection
