@extends('layouts.app')

@section('content')
    <x-admin.page-edit-bar slug="faq" :content-page="$contentPage ?? null" />
    @if (! empty($contentPage?->html))
        <div class="editable-content-page">
            {!! $contentPage->html !!}
        </div>
    @else
    <div class="margin-side">
        <div class="title-banner">
            <h1><span style="color: var(--jk-primary)">Vragen?</span><br>
                We helpen je op weg.
            </h1>

            <p>
                Je weg vinden kan lastig zijn. Of je nu op zoek bent naar ondersteuning, je afvraagt hoe actueel de gegevens
                zijn, of gewoon nieuwsgierig bent hoe het hier werkt - vind de antwoorden die je nodig hebt hieronder.
            </p>
        </div>

        <div class="main-container">
            <div class="sidebar-pc">
                <h2>Veelgestelde vragen</h2>
                <div class="sidebar-content">
                    <a id="sidebar-sociale" href="#content-btn-sociale">
                        <p>Wat is de Sociale Kaart?</p><br>
                    </a>
                    <a id="sidebar-doel" href="#content-btn-doel">
                        <p>Wat is het doel, de doelgroep en welke gegevens zijn opgenomen?</p><br>
                    </a>
                    <a id="sidebar-symbolen" href="#content-btn-symbolen">
                        <p>Wat betekenen de symbolen op de website?</p><br>
                    </a>
                    <a id="sidebar-actueel" href="#content-btn-actueel">
                        <p>Hoe actueel zijn de gegevens? Fout gezien?</p><br>
                    </a>
                    <a id="sidebar-burger" href="#content-btn-burger">
                        <p>Hoe meld ik mij aan? Als burger?</p><br>
                    </a>
                    <a id="sidebar-aanpassen" href="#content-btn-aanpassen">
                        <p>Hoe de gegevens van mijn voorziening/praktijk aanpassen?</p><br>
                    </a>
                    <a id="sidebar-fiche" href="#content-btn-fiche">
                        <p>Hoe voeg ik een nieuwe fiche toe?</p><br>
                    </a>
                    <a id="sidebar-gegevens" href="#content-btn-gegevens">
                        <p>Hoe gegevens downloaden en hergebruiken?</p><br>
                    </a>
                    <a id="sidebar-promotie" href="#content-btn-promotie">
                        <p>Welk promotie- en vormingsaanbod is er?</p><br>
                    </a>
                    <a id="sidebar-profiel" href="#content-btn-profiel">
                        <p>Wat gebeurt er met de gegevens die we bijhouden via mijn profiel?</p><br>
                    </a>
                </div>
            </div>


            <div class="sidebar-phone">
                <button id="sidebar-phone-btn"  class="extra-info-btn">
                    <p>Veelgestelde vragen</p><span class="arrow"><i class="fa-solid fa-angle-down"></i></span>
                </button>
                <div id="sidebar-phone" class="extra-info sidebar-content">
                    <a id="sidebar-sociale" href="#content-btn-sociale">
                        <p>Wat is de Sociale Kaart?</p><br>
                    </a>
                    <a id="sidebar-doel" href="#content-btn-doel">
                        <p>Wat is het doel, de doelgroep en welke gegevens zijn opgenomen?</p><br>
                    </a>
                    <a id="sidebar-symbolen" href="#content-btn-symbolen">
                        <p>Wat betekenen de symbolen op de website?</p><br>
                    </a>
                    <a id="sidebar-actueel" href="#content-btn-actueel">
                        <p>Hoe actueel zijn de gegevens? Fout gezien?</p><br>
                    </a>
                    <a id="sidebar-burger" href="#content-btn-burger">
                        <p>Hoe meld ik mij aan? Als burger?</p><br>
                    </a>
                    <a id="sidebar-aanpassen" href="#content-btn-aanpassen">
                        <p>Hoe de gegevens van mijn voorziening/praktijk aanpassen?</p><br>
                    </a>
                    <a id="sidebar-fiche" href="#content-btn-fiche">
                        <p>Hoe voeg ik een nieuwe fiche toe?</p><br>
                    </a>
                    <a id="sidebar-gegevens" href="#content-btn-gegevens">
                        <p>Hoe gegevens downloaden en hergebruiken?</p><br>
                    </a>
                    <a id="sidebar-promotie" href="#content-btn-promotie">
                        <p>Welk promotie- en vormingsaanbod is er?</p><br>
                    </a>
                    <a id="sidebar-profiel" href="#content-btn-profiel">
                        <p>Wat gebeurt er met de gegevens die we bijhouden via mijn profiel?</p><br>
                    </a>
                </div>
            </div>


            <div class="content">
                <p>Vanaf 20 januari is er een bijkomende veiligheidscontrole bij het aanmelden met itsme op
                    toepassingen van de Vlaamse overheid. Personen die een nieuwe eID kregen nadat de itsme-account
                    eerder was geactiveerd, moeten een wijziging doorvoeren. Meer info op de <a
                        href="https://www.vlaanderen.be/digitaal-vlaanderen/nieuws/update-beveiliging-itsme-aanmelding-op-toepassingen-van-de-vlaamse-overheid?_cldee=QD0M9QBTC9h2vx36PMO0ZkKynDiKgmyKrKOZX_WafOViQ4HFMPI7MHin1SL7EbId&amp;recipientid=contact-9f84ff65ed54eb11bb23000d3abaa1bc-6136bc4cd54d472cbbb05b7205b60d6a&amp;esid=f03c5ee3-c2f2-f011-8406-7ced8d2cb050"
                        rel="noopener noreferrer" target="_blank">website van Digitaal Vlaanderen</a>.</p>
                <button id="content-btn-sociale"  class="extra-info-btn">
                    <p>Wat is de Sociale Kaart?</p><span class="arrow"><i class="fa-solid fa-angle-down"></i></span>
                </button>
                <div id="content-sociale" class="extra-info">
                    <p>De Vlaamse Sociale Kaart wil iedereen informeren over het zorgaanbod in Vlaanderen en Brussel en doet
                        dit door alle zorgaanbieders te bundelen op één website. U kunt er als burger of hulpverlener zoeken
                        naar gepaste zorg voor uzelf of een cliënt. Lokale besturen krijgen via de Sociale Kaart een
                        overzicht van de welzijns- en zorgactoren op hun grondgebied.</p>
                    <p>Welke zorgaanbieders tot de scope behoren, leest u in de scopebeschrijving.</p><a
                        class="socka-quill-file-upload" download="SK-scope-beschrijving.pdf"
                        href="https://www.desocialekaart.be/api/files/1772100106-SK-scope-beschrijving.pdf">SK-scope-beschrijving.pdf</a>
                    <p>Elke zorgaanbieder heeft een eigen fiche waarop u adres- en contactgegevens, informatie over de
                        werking, doelgroep, erkenning, enz vindt.</p>
                    <p>Hoe u snel een zorgaanbieder opzoekt en wat u met de fiche kan doen (doorsturen, afdrukken,
                        opslaan...), leest u op de <a
                            href="https://www.desocialekaart.be/handleiding/zoeken-functionaliteiten"
                            rel="noopener noreferrer" target="_blank">pagina rond het zoeken en extra functionaliteiten voor
                            gebruikers met een profiel</a>. Via <a href="https://www.desocialekaart.be/handleiding/profiel"
                            rel="noopener noreferrer" target="_blank">het stappenplan</a> kan je eenvoudig een profiel
                        aanmaken.</p>
                    <ul>
                        <li><u>Kijktip:</u> bekijk ons <a href="https://youtu.be/njjZGE6-wE0" rel="noopener noreferrer"
                                target="_blank">promotiefilmpje</a>.</li>
                        <li><u>Leestip:</u> het <a
                                href="https://weliswaar.be/welzijn-zorg/alle-info-van-zorgaanbieders-voor-professionelen-en-zorgvragers-op-%C3%A9%C3%A9n-plek"
                                rel="noopener noreferrer" target="_blank">interview</a> over onze samenwerking met VIVEL.
                        </li>
                    </ul>
                </div>
                <button id="content-btn-doel" class="extra-info-btn">
                    <p>Wat is het doel, de doelgroep en welke
                        gegevens zijn opgenomen?</p><span class="arrow"><i class="fa-solid fa-angle-down"></i></span>
                </button>
                <div id="content-doel" class="extra-info">
                    <p class="ql-align-justify">Elke burger heeft het recht om geïnformeerd te worden over het zorgaanbod in
                        Vlaanderen en Brussel. De Sociale Kaart verzekert dat recht.</p>
                    <p class="ql-align-justify">Op de <a
                            href="https://www.desocialekaart.be/handleiding/doel-doelgroep-inhoud" rel="noopener noreferrer"
                            target="_blank">pagina met uitgebreide uitleg</a> leest u meer over welke
                        gegevens we opnemen en
                        wie onze doelgroep is. Daarnaast kunt u er meer info vinden over de koppeling met authentieke
                        bronnen.</p>
                </div>
                <button id="content-btn-symbolen" class="extra-info-btn">
                    <p>Wat betekenen de symbolen op de
                        website?</p><span class="arrow"><i class="fa-solid fa-angle-down"></i></span>
                </button>
                <div id="content-symbolen" class="extra-info">
                    <p class="ql-align-justify">Op de <a href="https://www.desocialekaart.be/handleiding/symbolen"
                            rel="noopener noreferrer" target="_blank">uitlegpagina</a> sommen we de verschillende symbolen
                        op.</p>
                </div>
                <button id="content-btn-actueel" class="extra-info-btn">
                    <p>Hoe actueel zijn de gegevens? Fout
                        gezien?</p><span class="arrow"><i class="fa-solid fa-angle-down"></i></span>
                </button>
                <div id="content-actueel" class="extra-info">
                    <p>De welzijns- en gezondheidssector evolueert constant. Daarom is de betrokkenheid van voorzieningen en
                        zorgverstrekkers belangrijk om de databank accuraat en volledig te houden volgens het principe
                        van gedeelde verantwoordelijkheid. We werken daarom zoveel mogelijk met contactpersonen in de
                        voorzieningen en praktijken zelf.</p>
                    <p>In elke regio verbeteren beheerders dagelijks de kwaliteit van de gegevens in de Sociale Kaart. Ze
                        sporen nieuwe erkenningen en initiatieven actief op, corrigeren fouten en structureren de gegevens
                        op de fiches. Daarnaast zetten we gerichte datakwaliteit-acties op. Op <a
                            href="https://www.desocialekaart.be/handleiding/kwaliteitsacties" rel="noopener noreferrer"
                            target="_blank">deze pagina</a> leest u welke datakwaliteit-acties het Team Sociale Kaart
                        uitvoerde, welke acties lopend zijn en waar we de komende periode op inzetten.</p>
                    <p class="ql-align-justify">Bekijk <a href="https://www.desocialekaart.be/handleiding/beheer-fiche"
                            rel="noopener noreferrer" target="_blank">onze informatie</a> als u als voorziening of
                        zorgverlener uw gegevens wil toevoegen of aanpassen.</p>
                    <p>Merkt u andere fouten op de website of geven onze themapagina's of deze veelgestelde
                        vragenpagina geen antwoord op uw vraag? Contacteer ons via het <a
                            href="{{route('contact')}}" rel="noopener noreferrer"
                            target="_blank">contactformulier</a>.</p>
                </div>
                <button id="content-btn-burger" class="extra-info-btn">
                    <p>Hoe meld ik mij aan? Als burger?</p><span class="arrow"><i class="fa-solid fa-angle-down"></i></span>
                </button>
                <div id="content-burger" class="extra-info">
                    <p>Aanmelden gebeurt voortaan via een digitale sleutel. De stappen tonen we in <a
                            href="https://www.youtube.com/watch?v=Cefr4cDXbd4" rel="noopener noreferrer" target="_blank">een
                            filmpje</a> of via <a href="https://www.desocialekaart.be/handleiding/profiel"
                            rel="noopener noreferrer" target="_blank">het stappenplan</a>.</p>
                    <p>De optie <strong>‘aanmelden als zorgverlener’</strong> is enkel bedoeld voor de
                        gezondheidszorgberoepen zoals huisartsen, apothekers, kinesitherapeuten,… Alle anderen (bv.
                        maatschappelijk werkers, secretariaatsmedewerkers,…) moeten ‘<strong>aanmelden als burger’</strong>.
                        De formulering ‘inloggen als burger’ zorgt soms voor verwarring. Enkel uw officiële voornaam,
                        familienaam en rijkregisternummer laden we in. Enkel uw voornaam en familienaam zijn voor ons
                        zichtbaar. Tot andere gegevens hebben wij geen toegang. Als u inlogt voor uw werk, geef dan uw
                        werk-e-mailadres op tijdens de inlogprocedure.</p>
                </div>
                <button id="content-btn-aanpassen" class="extra-info-btn">
                    <p>Hoe de gegevens van mijn voorziening/praktijk
                        aanpassen?</p><span class="arrow"><i class="fa-solid fa-angle-down"></i></span>
                </button>
                <div id="content-aanpassen" class="extra-info">
                    <p>Als fichebeheerder kunt u zelf uw fiche beheren. Meer uitleg over hoe u het beheer over de gegevens
                        kunt aanvragen vindt u op de handleidingenpagina '<a
                            href="https://www.desocialekaart.be/handleiding/beheer-aanvragen" rel="noopener noreferrer"
                            target="_blank">Hoe word je fichebeheerder?</a>'.</p>
                    <p><span style="color: rgb(51, 50, 50);">Op elke fiche staat er onder de drie puntjes een knop
                            'suggestie tot wijziging'. Langs deze weg worden wij op de hoogte gebracht van uw suggestie. In
                            een </span><a href="https://youtu.be/upsRbuTq_yE" rel="noopener noreferrer"
                            target="_blank">filmpje</a><span style="color: rgb(51, 50, 50);"> wordt uitgelegd over het
                            indienen van een 'suggestie tot wijziging'.</span></p>
                </div>
                <button id="content-btn-fiche" class="extra-info-btn">
                    <p>Hoe voeg ik een nieuwe fiche toe?</p><span class="arrow"><i class="fa-solid fa-angle-down"></i></span>
                </button>
                <div id="content-fiche" class="extra-info">
                    <p>U kunt zelf een nieuwe fiche toevoegen. Meer uitleg hierover kunt u op de <a
                            href="https://www.desocialekaart.be/handleiding/nieuwe-fiche" rel="noopener noreferrer"
                            target="_blank">handleidingenpagina</a> terugvinden.</p>
                </div>
                <button id="content-btn-gegevens" class="extra-info-btn">
                    <p>Hoe gegevens downloaden en
                        hergebruiken?</p><span class="arrow"><i class="fa-solid fa-angle-down"></i></span>
                </button>
                <div id="content-gegevens" class="extra-info">

                    <p>De gegevens uit de databank Sociale Kaart kunnen op verschillende manieren hergebruikt worden door
                        externe partijen: via API, door integratie van een widget in de eigen website, door opmaak van een
                        Sociale Kaart op maat of door het downloaden van gegevens.</p>
                    <p>Downloaden van gegevens kan als u ingelogd bent. Het bestand downloadt niet onmiddellijk om
                        het systeem niet te overbelasten. Daarom krijgt u een e-mail met de link naar het bestand
                        downloaden. We gebruiken het e-mailadres dat bij uw profiel hoort. Meer info vindt
                        u in het filmpje <a href="https://youtu.be/aPoyu7BC6w4" rel="noopener noreferrer"
                            target="_blank">‘gegevens downloaden’</a>.</p>
                    <p><span style="color: rgb(51, 50, 50);">Voor meer informatie over de API, widget of Sociale Kaart op
                            maat; zie de handleidingenpagina rond het </span><a
                            href="https://www.desocialekaart.be/handleiding/hergebruik-van-gegevens"
                            rel="noopener noreferrer" target="_blank">hergebruik van gegevens</a><span
                            style="color: rgb(51, 50, 50);">.</span></p>
                </div>
                <button id="content-btn-promotie" class="extra-info-btn">
                    <p>Welk promotie- en vormingsaanbod is
                        er?</p><span class="arrow"><i class="fa-solid fa-angle-down"></i></span>
                </button>
                <div id="content-promotie" class="extra-info">
                    <p>Er zijn affiches en een flyer ter beschikking. Deze zijn te downloaden via onderstaande linken of te
                        bestellen via het <a
                            href="https://www.vlaanderen.be/publicaties?q.LIKE=Sociale%20Kaart&amp;order_publicationdate=desc"
                            rel="noopener noreferrer" target="_blank">bestelloket</a>.</p><a
                        class="socka-quill-file-upload"
                        download="ZORG_DeSocialeKaart_AD_liggend_vind-jij-de-zorg-die-je-nodig-hebt-gecomprimeerd.pdf"
                        href="https://www.desocialekaart.be/api/files/1706267294-ZORG_DeSocialeKaart_AD_liggend_vind-jij-de-zorg-die-je-nodig-hebt-gecomprimeerd.pdf">ZORG_DeSocialeKaart_AD_liggend_vind-jij-de-zorg-die-je-nodig-hebt-gecomprimeerd.pdf</a>
                    <p><a class="socka-quill-file-upload"
                            download="ZORG_DeSocialeKaart_AD_staand_vind-jij-de-zorg-die-je-nodig-hebt-gecomprimeerd.pdf"
                            style="cursor: text;"
                            href="https://www.desocialekaart.be/api/files/1706267296-ZORG_DeSocialeKaart_AD_staand_vind-jij-de-zorg-die-je-nodig-hebt-gecomprimeerd.pdf">ZORG_DeSocialeKaart_AD_staand_vind-jij-de-zorg-die-je-nodig-hebt-gecomprimeerd.pdf</a>
                    </p>
                    <p><br></p><a class="socka-quill-file-upload"
                        download="ZORG_DeSocialeKaart_AD_liggend_vinden-mensen-jouw-zorg-online-gecomprimeerd.pdf"
                        href="https://www.desocialekaart.be/api/files/1706267294-ZORG_DeSocialeKaart_AD_liggend_vinden-mensen-jouw-zorg-online-gecomprimeerd.pdf">ZORG_DeSocialeKaart_AD_liggend_vinden-mensen-jouw-zorg-online-gecomprimeerd.pdf</a>
                    <p><a class="socka-quill-file-upload"
                            download="ZORG_DeSocialeKaart_AD_staand_vinden-mensen-jouw-zorg-online-gecomprimeerd.pdf"
                            style="cursor: text;"
                            href="https://www.desocialekaart.be/api/files/1706267295-ZORG_DeSocialeKaart_AD_staand_vinden-mensen-jouw-zorg-online-gecomprimeerd.pdf">ZORG_DeSocialeKaart_AD_staand_vinden-mensen-jouw-zorg-online-gecomprimeerd.pdf</a>
                    </p>
                    <p><br></p><a class="socka-quill-file-upload"
                        download="ZORG_DeSocialeKaart_AD_liggend_we-zetten-zorg-op-de-kaart-gecomprimeerd.pdf"
                        href="https://www.desocialekaart.be/api/files/1706267294-ZORG_DeSocialeKaart_AD_liggend_we-zetten-zorg-op-de-kaart-gecomprimeerd.pdf">ZORG_DeSocialeKaart_AD_liggend_we-zetten-zorg-op-de-kaart-gecomprimeerd.pdf</a>
                    <p><a class="socka-quill-file-upload"
                            download="ZORG_DeSocialeKaart_AD_staand_we-zetten-zorg-op-de-kaart-gecomprimeerd.pdf"
                            style="cursor: text;"
                            href="https://www.desocialekaart.be/api/files/1706267296-ZORG_DeSocialeKaart_AD_staand_we-zetten-zorg-op-de-kaart-gecomprimeerd.pdf">ZORG_DeSocialeKaart_AD_staand_we-zetten-zorg-op-de-kaart-gecomprimeerd.pdf</a>
                    </p>
                    <p><br></p>
                    <p>De pavés zijn enkel digitaal beschikbaar.</p><a class="socka-quill-file-upload"
                        download="SocialeKaart_Pavés_vierkant.pdf"
                        href="https://www.desocialekaart.be/api/files/1706267332-SocialeKaart_Pav%C3%A9s_vierkant.pdf">SocialeKaart_Pavés_vierkant.pdf</a>
                    <p><br></p>
                    <p>Het vormingsaanbod van het Team Sociale Kaart kunt u lezen op de <a
                            href="https://www.desocialekaart.be/handleiding/vorming" rel="noopener noreferrer"
                            target="_blank">pagina rond vorming</a></p>
                    <p>Vormingsmateriaal voor het onderwijs vindt u op een <a
                            href="https://www.departementzorg.be/nl/vormingsmateriaal-de-sociale-kaart"
                            rel="noopener noreferrer" target="_blank">aparte pagina</a>.</p>
                </div>
                <button id="content-btn-profiel" class="extra-info-btn">
                    <p>Wat gebeurt er met de gegevens die we
                        bijhouden via mijn profiel?</p><span class="arrow"><i class="fa-solid fa-angle-down"></i></span>
                </button>
                <div id="content-profiel" class="extra-info">
                    <p>De gegevens zijn nodig om extra functionaliteiten te gebruiken: favorieten bijhouden, zoekopdrachten
                        opslaan en persoonlijke commentaar toevoegen aan een fiche. De initiatiefnemers kregen toestemming
                        van de bevoegde instanties om deze gegevens bij te houden. Voorwaarde is dat de databank ze
                        versleuteld opslaat. De functionaris voor gegevensbescherming van het Departement Zorg ziet er
                        strikt op toe dat deze voorwaarde correct nageleefd wordt, ook door de technische ontwikkelaar.</p>
                    <p>Meer info over de privacy kunt u vinden bij de <a href="{{route('privacy')}}"
                            rel="noopener noreferrer" target="_blank">privacy</a> pagina.</p>
                </div>

            </div>

        </div>

    </div>
    @endif
@endsection
