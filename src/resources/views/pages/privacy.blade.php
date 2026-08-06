@extends('layouts.app')

@section('content')
    <x-admin.page-edit-bar slug="privacy" :content-page="$contentPage ?? null" />
    @if (! empty($contentPage?->html))
        <div class="editable-content-page">
            {!! $contentPage->html !!}
        </div>
    @else
    <div class="margin-side">
        <div class="info_privacy-banner">
            <h1>
                <span style="color: var(--jk-primary)">Privacybeleid</span><br>
            </h1>
        </div>

        <div class="shortcuts">
            <a href="#cookies">
                <i class="fa-solid fa-cookie-bite"></i>
                <p>Cookies</p>
            </a>

            <a href="#privacy">
                <i class="fa-solid fa-gavel"></i>
                <p>privacy</p>
            </a>

            <a href="#toegankelijkheid">
                <i class="fa-solid fa-person"></i>
                <p>toegankelijkheid</p>
            </a>
        </div>

        <section id="cookies">
            <div class="title-container">
                <div class="icon-container">
                    <i class="fa-solid fa-cookie-bite"></i>
                </div>
                <h2>Cookiebeleid</h2>
            </div>

            <div class="recap-card">
                <h4>In het kort</h4>
                <p>
                    We gebruiken cookies om de website goed te laten werken en uw voorkeuren te onthouden. Noodzakelijke
                    cookies worden altijd geplaatst, maar u kunt niet-essentiële cookies weigeren. We verzamelen ook
                    beperkte technische gegevens over het gebruik van de site om deze te verbeteren. Publieke gegevens van
                    zorgaanbieders kunnen geëxporteerd worden, maar zijn mogelijk snel verouderd. Gebruik deze gegevens
                    daarom zorgvuldig en verwijder ze wanneer ze niet meer nodig zijn.
                </p>
            </div>

            <button id="cookies-extra-info-btn" class="extra-info-btn">
                <p>Volledige informatie
                <p>
                    <span class="arrow"><i class="fa-solid fa-angle-down"></i></span>
            </button>

            <div id="cookies-extra-info" class="extra-info">
                <p>Onze website gebruikt cookies en gelijkaardige technologieën om uw gebruikersvoorkeuren te onderscheiden
                    van die van andere gebruikers van onze website. Een cookie is een klein tekst- en cijferbestand dat wij
                    opslaan in uw browser of op de harde schijf van uw computer. Op die manier kunnen wij uw voorkeuren bij
                    het gebruik van onze website onthouden. U kan de niet-essentiële cookies weigeren. Cookies die nodig
                    zijn om de goede werking van de website te garanderen, kan u niet weigeren. Tenzij u uw
                    browserinstellingen hebt aangepast zodat die cookies zal weigeren, zal ons systeem deze essentiële
                    cookies plaatsen van zodra u onze website bezoekt.</p>
                <p>Welke cookies gebruiken wij?</p>
                <ul>
                    <li>__utm*, _ga*: Google Analytics</li>
                    <li>XSRF-TOKEN: voor security cross side scripting</li>
                </ul>
                <p>Voor verdere informatie over het verwijderen of blokkeren van cookies kunt u volgende websites bezoeken:
                </p>
                <ul>
                    <li><a href="https://support.microsoft.com/nl-nl/windows/cookies-verwijderen-en-beheren-168dab11-0753-043d-7c16-ede5947fc64d"
                            target="_blank" rel="noopener">Microsoft Internet Explorer</a></li>
                    <li><a href="https://support.microsoft.com/nl-nl/help/4468242/microsoft-edge-browsing-data-and-privacy-microsoft-privacy"
                            target="_blank" rel="noopener">Microsoft Edge</a></li>
                    <li><a href="https://support.google.com/accounts/answer/32050?hl=nl" target="_blank"
                            rel="noopener">Google Chrome</a></li>
                    <li><a href="https://support.mozilla.org/nl/kb/cookies-verwijderen-gegevens-wissen-websites-opgeslagen"
                            target="_blank" rel="noopener">Mozilla Firefox</a></li>
                    <li><a href="https://support.apple.com/kb/PH5042" target="_blank" rel="noopener">Apple Safari</a></li>
                </ul>
                <h1>Logfiles over websitegebruik</h1>
                <p>Logfiles registreren bepaalde informatie die gebruikt wordt voor interne doeleinden, bijvoorbeeld om het
                    websitegebruik na te gaan en de website aan te passen. Deze informatie bevat geen persoonlijke gegevens
                    maar wel gegevens over uw PC.</p>
                <h1>Exporteren van publieke gegevens van zorgaanbieders</h1>
                <p>De mogelijkheid bestaat om het publieke deel van de gegevens van zorgaanbieders uit de Sociale Kaart te
                    exporteren. We raden aan om die functionaliteit enkel te gebruiken voor een set van gegevens en voor
                    eenmalig gebruik. De meest actuele gegevens zijn steeds online terug te vinden. Daarom kan de kwaliteit
                    van de databank niet beoordeeld worden aan de hand van geëxporteerde lijsten en andere, al dan niet
                    gedrukte, afgeleide producten. </p>
                <p>Een functionaliteit aanbieden om lijsten met gegevens uit de Sociale Kaart te exporteren valt binnen de
                    opdracht van de Vlaamse overheid om het aanbod van zorgvoorzieningen en zorgverstrekkers breed bekend te
                    maken als open data. Degene die een lijst exporteert, heeft de verantwoordelijkheid om de gegevens met
                    de grootste omzichtigheid te behandelen en de lijst te verwijderen wanneer de gegevens niet langer
                    gebruikt worden.</p>
            </div>
        </section>

        <section id="privacy">
            <div class="title-container">
                <div class="icon-container">
                    <i class="fa-solid fa-gavel"></i>
                </div>
                <h2>Privacy</h2>
            </div>

            <div class="recap-card">
                <h4>In het kort</h4>
                <p>
                    We verwerken persoonsgegevens enkel wanneer dat nodig is voor de werking van de Sociale Kaart en volgens
                    de geldende privacywetgeving. Gegevens worden gebruikt om zorginformatie beschikbaar te maken,
                    gebruikersprofielen te beheren en de kwaliteit van de databank te garanderen. Sommige gegevens zijn
                    publiek zichtbaar, andere enkel voor beheerders. We nemen maatregelen om uw gegevens te beschermen en
                    geven ze niet door buiten de EU. U hebt altijd recht op inzage, aanpassing of verwijdering van uw
                    gegevens. Hoewel we de informatie zo correct mogelijk houden, kunnen fouten voorkomen en zijn we
                    afhankelijk van externe input. Laat het ons zeker weten als u iets opmerkt. Ook voor externe websites
                    waarnaar we linken zijn wij niet verantwoordelijk.
                </p>
            </div>

            <button id="privacy-extra-info-btn" class="extra-info-btn">
                <p>Volledige informatie
                <p>
                    <span class="arrow"><i class="fa-solid fa-angle-down"></i></span>
            </button>

            <div id="privacy-extra-info" class="extra-info">
                <h1><strong>Inleiding</strong></h1>
                <p>Het DEPARTEMENT ZORG respecteert uw rechten bij de verwerking van uw persoonsgegevens. In
                    deze verklaring vindt u hoe we uw persoonsgegevens verzamelen, verwerken en gebruiken.
                    Deze privacyverklaring geeft het algemene beleid aan op het vlak van gegevensverwerking en -
                    bescherming van:</p>
                <p>DEPARTEMENT ZORG</p>
                <p>Koning Albert II-laan 15 Postbus 495</p>
                <p>1030 Brussel</p>
                <h1><strong>Wie verwerkt uw persoonsgegevens?</strong>
                </h1>
                <p>Het DEPARTEMENT ZORG verwerkt uw persoonsgegevens. In deze
                    privacyverklaring gebruiken we het persoonlijk voornaamwoord ‘we’ om te verwijzen naar het
                    DEPARTEMENT ZORG. Het DEPARTEMENT ZORG is verantwoordelijk voor de verwerking van uw
                    persoonsgegevens die in deze verklaring worden omschreven en toegelicht.</p>
                <p>We verwerken alleen persoonsgegevens en we laten alleen persoonsgegevens
                    verwerken als dat noodzakelijk is om de taken die ons zijn toebedeeld te kunnen verrichten. We
                    verwerken de persoonsgegevens altijd in overeenstemming met de bepalingen van de Algemene
                    Verordening Gegevensbescherming (AVG), en met de bepalingen van de federale en Vlaamse
                    regelgeving over de bescherming van natuurlijke personen bij de verwerking van
                    persoonsgegevens.</p>
                <p>Als u algemene vragen hebt over de manier waarop we uw
                    persoonsgegevens verwerken, kunt u contact opnemen met de functionaris voor gegevensbescherming
                    (hierna: FG), in het Engels de Data Protection Officer (DPO), van het DEPARTEMENT</span><span
                        style="color: rgb(103, 109, 112);"> </span>ZORG.</span><span style="color: rgb(103, 109, 112);">
                    </span>Dit kan door te
                    mailen naar </span><a href="https://dpo.zorg@vlaanderen.be" rel="noopener noreferrer"
                        target="_blank">dpo.zorg@vlaanderen.be</a>.</span><span style="color: rgb(103, 109, 112);"> </span>U
                    kan ook terecht
                    bij de FG met opmerkingen en suggesties en om uw rechten uit te oefenen.</p>
                <p>De zorgraden die een decretale opdracht hebben m.b.t. de Sociale Kaart
                    hebben de mogelijkheid om in de Sociale Kaart contactgegevens (naam, e-mailadres en
                    telefoonnummer) bij te houden van personen in een organisatie/praktijk die van belang zijn voor
                    de uitvoering van hun opdrachten. Deze contactgegevens zijn enkel zichtbaar voor de medewerkers
                    van de zorgraad die rechten kregen in de applicatie Sociale Kaart.</p>
                <h1><strong>Wanneer verzamelen en verwerken we uw
                        persoonsgegevens?</strong></h1>
                <p>We verzamelen en verwerken uw persoonsgegevens in het kader van de
                    Sociale Kaart. De databank Sociale Kaart verzamelt, onderhoudt gegevens over zorgvoorzieningen
                    en zorgverstrekkers en publiceert ze via het internet op </span><a href="{{route('home')}}"
                        rel="noopener noreferrer" target="_blank">www.desocialekaart.be</a>.</p>
                <p>De Sociale Kaart wil een toegangsweg bieden tot de hulpverlening die het
                    best een antwoord kan formuleren op een probleem of hulpvraag. Burgers en
                    professionele</span><span style="color: rgb(103, 109, 112);"> </span>hulpverleners dienen steeds met de
                    voorzieningen en zorgverstrekkers zelf
                    contact op te nemen om een beroep te kunnen doen op zorg en hulpverlening.</p>
                <h1><strong>Welke gegevens verwerken we over u?</strong>
                </h1>
                <p>Wat betreft de soort persoonsgegevens die we verzamelen, maken we een
                    onderscheid tussen vier verschillende groepen persoonsgegevens. </p>
                <ul>
                    <li><u>De profielgegevens</u></li>
                </ul>
                <p>Allereerst verzamelen we profielgegevens, indien een burger een profiel
                    aanmaakt. Een individuele burger hoeft geen profiel aan te maken en in te loggen om in de
                    Sociale Kaart antwoorden te zoeken op zijn/haar zorgvraag. Opzoekingen (zonder profiel) via de
                    website verlopen volledig anoniem.</p>
                <ul>
                    <li><u>De gepubliceerde persoonsgegevens van zorgverstrekkers en werknemers en/of contactpersonen in
                            zorgvoorzieningen</u></li>
                </ul>
                <p>Daarnaast verzamelen we ook persoonsgegevens van zorgverstrekkers,
                    werknemers en/of contactpersonen in zorgvoorzieningen. We verzamelen volgende gegevens: naam,
                    voornaam, telefoonnummer, professioneel e-mailadres en adres.</p>
                <p>Uit de CoBRHA databank worden volgende persoonsgegevens gepubliceerd in
                    de Sociale Kaart: beroepsgroep, diploma, RIZIV-nummer, kwalificatie- of bevoegdheidscode van het
                    RIZIV, conventiestatus, erkende specialisaties, accrediteringsperiode.</p>
                <ul>
                    <li><u>De niet-gepubliceerde persoonsgegevens van zorgverstrekkers en werknemers en/of
                            contactpersonen in zorgvoorzieningen</u></li>
                </ul>
                <p>We verzamelen niet-gepubliceerde contactgegevens van zorgverstrekkers,
                    werknemers en/of contactpersonen in zorgvoorzieningen. Dit contactgegeven is het
                    e-mailadres.</p>
                <p>Deze persoonsgegevens worden niet gepubliceerd op de publieke website,
                    wel in de beheermodus die toegankelijk is voor interne en externe beheerders die deze
                    bevoegdheid hebben.</p>
                <p>Uit de CoBRHA databank worden persoonsgegevens met betrekking tot de
                    relevante hoedanigheden van de zorgverlener verwerkt om na te gaan of deze nog erkend of actief
                    is als zorgverlener om actie te ondernemen in de Sociale Kaart (bv. bij schorsing of overlijden
                    wordt de zorgverlener automatisch gedepubliceerd in de Sociale Kaart).</p>
                <ul>
                    <li><u>De gegevens via eHealth verwerken</u></li>
                </ul>
                <p>Wanneer de gebruiker aanmeldt met de digitale sleutel (via eHealth)
                    verwerken we het rijksregisternummer om de persoon (burger en zorgverlener) te
                    identificeren.</p>
                <h1><strong>Hoe verzamelen en verwerken we
                        persoonsgegevens?</strong></h1>
                <p>De persoonsgegevens worden op verschillende manieren verkregen. Ten
                    eerste kunnen de gegevens rechtstreeks bij u worden opgevraagd via het formulier op de
                    website om een profiel aan te maken en via de invoermodule om een nieuwe organisatie/praktijk
                    toe te voegen.</p>
                <p>Ten tweede worden de gegevens verkregen via organisaties en
                    zorgverstrekkers. Het team Sociale Kaart is actief bezig met het opsporen en corrigeren van
                    hiaten en onvolmaaktheden, maar blijft vooral afhankelijk van input van de organisaties en
                    zorgverstrekkers zelf.</p>
                <p>Ten derde worden de gegevens verkregen via de CoBRHA databank. Interne en
                    externe beheerders die deze bevoegdheid hebben, hebben toegang tot de professionele
                    persoonsgegevens van zorgverstrekkers die uit CoBRHA opgehaald worden (zie hoger voor de
                    opsomming). Zij kunnen deze gegevens verder aanvullen en publiceren op de Sociale Kaart.</span>
                </p>
                <p>De persoonsgegevens op de website zijn zo zorgvuldig, begrijpelijk en
                    volledig mogelijk opgesteld, op basis van de huidige kennis. Ondanks onze inspanningen kan het
                    gebeuren dat de informatie niet meer volledig, juist, nauwkeurig of bijgewerkt is.</p>
                <p>Het team Sociale Kaart zal tekortkomingen zo snel mogelijk wegwerken. De
                    inhoud van de website kan steeds zonder aankondiging of kennisgeving aangepast, gewijzigd,
                    aangevuld of verwijderd worden.</p>
                <p>Op elke fiche staat de mogelijkheid om een 'wijziging te suggereren'. We
                    hebben het e-mailadres via het profiel en kunnen u contacteren mochten we bijkomende informatie
                    willen. Ook via het </span><a href="{{route('contact')}}" rel="noopener noreferrer"
                        target="_blank">contactformulier</a> zijn we bereikbaar.</p>
                <p>Daarnaast worden de gegevens van gebruikers (zorgverstrekkers) ook via
                    eHealth verwerkt door de aanmeldprocedure van eHealth.</p>
                <h1><strong>Waarvoor verwerken we uw
                        persoonsgegevens?</strong></h1>
                <ul>
                    <li><u>De profielgegevens</u></li>
                </ul>
                <p>De gegevens die u ons geeft om uw eigen profiel aan te maken worden
                    slechts gebruikt in het kader van gebruikersbeheer. Een gebruikersprofiel aanmaken heeft voor
                    professionele hulpverleners en zorgverstrekkers voordelen. Zij kunnen dan hun eigen – publieke –
                    gegevens beheren en die van hun organisatie of praktijk. Verder is het voor hen mogelijk om een
                    favorietenlijst bij te houden, veel gebruikte zoekopdrachten op te slaan en persoonlijke
                    notities bij een fiche toe te voegen. De contactgegevens van het profiel van erkende
                    zorgverstrekkers worden eveneens gebruikt met het oog op het beheer van de Sociale Kaart.</span>
                </p>
                <p>Het is niet nodig u te registreren als u enkel in de toepassing wilt
                    zoeken. Indien u als niet professional in de welzijns- en gezondheidssector toch een
                    gebruikersprofiel wilt aanmaken, vragen we u ervan bewust te zijn dat door deze actie wij
                    mogelijks gezondheidsgegevens van u verwerken (bv. opslaan van favoriete fiches of
                    zoekopdrachten). Door hiermee akkoord te gaan, geeft u toestemming tot verwerking van uw
                    persoonsgegevens.</p>
                <p>Daarnaast gebruiken we deze gegevens om u via e-mail te informeren over
                    functionaliteiten van de Sociale Kaart.</p>
                <ul>
                    <li><span style="color: rgb(103, 109, 112);"> </span><u>De gepubliceerde persoonsgegevens van
                            zorgverstrekkers en werknemers en/of contactpersonen in zorgvoorzieningen</u></li>
                </ul>
                <p>We verwerken deze gepubliceerde persoonsgegevens om een overzicht te
                    bieden van het huidige zorgaanbod in Vlaanderen.</p>
                <ul>
                    <li><span style="color: rgb(103, 109, 112);"> </span><u>De niet-gepubliceerde persoonsgegevens
                            van zorgverstrekkers en werknemers en/of contactpersonen in zorgvoorzieningen</u></li>
                </ul>
                <p>Deze contactgegevens worden opgevraagd ter kwaliteitscontrole en
                    actualisatie van de gegevens en ter ondersteuning van de werking van verantwoordelijke
                    beheerders en de Vlaamse overheid.</p>
                <ul>
                    <li><u>De gegevens die we via eHealth ontvangen</u></li>
                </ul>
                <p>Deze gegevens worden verwerkt om de zorgverlener correct te identificeren.</p>
                <h1><strong>Wat zijn de grondslagen voor de verwerking
                        van uw persoonsgegevens?</strong></h1>
                <ul>
                    <li><u>De profielgegevens</u></li>
                </ul>
                <p>Bij het aanmaken van een profiel dient de gebruiker uitdrukkelijk kennis
                    te nemen van deze werkwijze en hiermee akkoord te gaan (</span>'informed
                    consent').</p>
                <ul>
                    <li><span style="color: rgb(103, 109, 112);"> </span><u>De (niet)-gepubliceerde
                            persoonsgegevens van zorgverstrekkers en werknemers en/of contactpersonen in
                            zorgvoorzieningen</u></li>
                </ul>
                <p>Als overheidsdienst verwerken wij deze (niet)-gepubliceerde
                    persoonsgegevens in het kader van onze taak van algemeen belang zoals omschreven in
                    het</span><span style="color: rgb(103, 109, 112);"> </span><a
                        href="https://codex.vlaanderen.be/Zoeken/Document.aspx?DID=1031834&amp;param=inhoud"
                        rel="noopener noreferrer" target="_blank">Decreet houdende de Sociale Kaart van 3 mei
                        2019</a><span style="color: rgb(103, 109, 112);"> </span>(B.S.
                    26/06/2019).</p>
                <ul>
                    <li><u>De gegevens die we uit CoBRHA via het eHealth-platform ontvangen</u></li>
                </ul>
                <p>Als overheidsdienst verwerken wij deze (niet)-gepubliceerde persoonsgegevens in het
                    kader van onze taak van algemeen belang zoals omschreven in het<span style="color: rgb(103, 109, 112);">
                    </span><a href="https://codex.vlaanderen.be/Zoeken/Document.aspx?DID=1031834&amp;param=inhoud"
                        rel="noopener noreferrer" target="_blank">Decreet houdende de Sociale Kaart van 3 mei
                        2019</a><span style="color: rgb(103, 109, 112);"> </span>(B.S. 26/06/2019) en het <span
                        style="color: rgb(51, 50, 50);">CoBRHA-samenwerkingsakkoord tussen de federale overheid en
                        de gemeenschappen.</p>
                <h1><strong>Hoelang bewaren we uw
                        persoonsgegevens?</strong></h1>
                <p>In overeenstemming met het<span style="background-color: rgb(245, 246, 246); color: rgb(103, 109, 112);">
                    </span><a href="https://codex.vlaanderen.be/Zoeken/Document.aspx?DID=1031834&amp;param=inhoud"
                        rel="noopener noreferrer" target="_blank">Decreet houdende de Sociale Kaart van 3 mei
                        2019</a><span style="color: rgb(103, 109, 112);"> </span>(B.S. 26/06/2019) bewaren we de
                    persoonsgegevens van de zorgaanbieder tot de zorgaanbieder definitief ophoudt zorg aan te bieden. De
                    persoonsgegevens van de contactpersonen worden bewaard tot de contactpersonen niet meer actief zijn.
                </p>
                <h1><strong>Worden uw persoonsgegevens
                        doorgegeven?</strong></h1>
                <p>Op basis van het<span style="background-color: rgb(245, 246, 246); color: rgb(103, 109, 112);">
                    </span><a href="https://codex.vlaanderen.be/Zoeken/Document.aspx?DID=1031834&amp;param=inhoud"
                        rel="noopener noreferrer" target="_blank">Decreet houdende de Sociale Kaart van 3 mei
                        2019</a><span style="color: rgb(103, 109, 112);"> </span>(B.S. 26/06/2019) wordt de
                    Sociale Kaart voor het publiek opengesteld. De publieke inhoud van de Sociale Kaart kan door derden
                    hergebruikt worden (o.a. via API). De Sociale Kaart wordt onderhouden door een team van regionale
                    beheerders, in samenwerking met de zorgraden. De beheerder bevoegd voor een bepaalde regio kan de
                    gegevens inkijken waarvan de professionele hulpverlener heeft gevraagd om ze publiek te maken.</p>
                <p>Voor elke geregistreerde gebruiker geldt dat uw persoonlijke gegevens van uw profiel
                    (rijksregisternummer, e-mailadres, favorietenlijst, opgeslagen zoekopdrachten, persoonlijke
                    commentaar bij een fiche) geëncrypteerd worden opgeslagen en enkel voor u leesbaar zijn. Deze
                    gegevens kunnen niet aan derden doorgegeven worden.</p>
                <p>We geven uw gegevens niet door aan landen buiten de Europese Unie
                    of internationale organisaties. Dat zijn landen of organisaties die gevestigd zijn buiten het
                    grondgebied van de Europese Economische Ruimte (Europese Unie + IJsland, Noorwegen en
                    Liechtenstein).</p>
                <p>We doen beroep op Google Analytics, hierbij worden analysecookies verzameld. U kan deze
                    niet-essentiële cookies ook weigeren. In elk geval nemen we alle mogelijke maatregelen om ervoor te
                    zorgen dat uw gegevens worden beschermd zoals bepaald in de Europese, federale en Vlaamse
                    regelgeving.</p>
                <h1><strong>Wat zijn uw rechten?</strong></h1>
                <p>Als we uw persoonsgegevens verwerken in het kader van het algemeen
                    belang, kunt u zich op elk moment verzetten tegen de verwerking van uw gegevens. We wegen dan af
                    of uw individuele belangen zwaarder doorwegen dan het algemeen belang dat we nastreven met
                    de verwerking.</p>
                <p>U kunt de gegevens die we over u verwerken, altijd inkijken en,
                    indien nodig, laten verbeteren. Je kunt daarvoor contact opnemen via </span><a
                        href="https://dpo.zorg@vlaanderen.be" rel="noopener noreferrer"
                        target="_blank">dpo.zorg@vlaanderen.be</a>. We vragen dan een
                    bewijs van uw identiteit zodat uw gegevens niet worden meegedeeld aan iemand die er geen recht
                    op heeft. U kunt hier ook terecht als u vindt dat uw gegevens niet langer
                    relevant zijn en dus verwijderd moeten worden.</p>
                <p>Geregistreerde gebruikers kunnen zelf online hun gegevens beheren en
                    zelf hun profiel verwijderen</span><span style="color: rgb(103, 109, 112);">.</p>
                <p>Als u het niet eens bent met de manier waarop we uw gegevens
                    verwerken, kan u contact opnemen met onze FG via </span><a href="https://dpo.zorg@vlaanderen.be"
                        rel="noopener noreferrer" target="_blank">dpo.zorg@vlaanderen.be</a>. Daarnaast kan u een klacht
                    indienen bij de
                    bevoegde toezichthoudende autoriteit.</p>
                <h1><strong>Hoe worden uw persoonsgegevens
                        beveiligd?</strong></h1>
                <p>We zorgen ervoor dat de persoonsgegevens goed beveiligd, bewaard en
                    getransporteerd worden, en enkel ingekeken kunnen worden door de medewerkers die ze nodig
                    hebben.</p>
                <p>Informatie die u in uw profiel voor eigen gebruik bewaart
                    (zoekopdrachten, favorietenlijsten…), zijn enkel voor uzelf toegankelijk. Alle noodzakelijke
                    technische beveiligingsvereisten zijn gevolgd om ervoor te zorgen dat deze gevoelige informatie
                    geëncrypteerd opgeslagen wordt.</p>
                <h1><strong>Algemene informatie</strong></h1>
                <p>We hebben het recht om het beleid te wijzigen en aan te passen.
                    Wijzigingen en aanpassingen melden we altijd via de website.</p>
                <p>Deze site bevat hyperlinks en verwijzingen naar websites van andere
                    overheden, instanties en organisaties, en naar informatiebronnen die door derden worden beheerd.
                    De Vlaamse overheid heeft over deze websites geen enkele vorm van controle en kan daarom geen
                    enkele garantie bieden over de volledigheid of juistheid van de inhoud en over de
                    beschikbaarheid van de websites en informatiebronnen. De Vlaamse overheid kan ook niet
                    garanderen dat deze websites een privacy-beleid naleven overeenkomstig de AVG. We raden
                    gebruikers dus aan dit zelf na te gaan door de privacy-clausules te raadplegen die op iedere
                    website moeten voorkomen.</p>
                <p>Het departement Zorg onderneemt actief acties met het oog op de
                    kwaliteitsborging en volledigheid van de gegevens in de databank Sociale Kaart. Echter,
                    aangezien het departement in grote mate afhankelijk is van de input en medewerking van de
                    zorgaanbieder (zorgvoorziening en zorgverlener) zelf kunnen we niet garanderen dat de gegevens
                    100% volledig en actueel zijn.</p>
                <p>Bij het digitaal inloggen (eID, itsme…) wordt gebruik gemaakt van het
                    eHealth-aanmeldsysteem. Bij een eerste aanmelding verschijnt een scherm waarbij standaardtekst
                    gepubliceerd wordt over ‘toestemming tot gebruik van uw gezondheidsgegevens’. Deze tekst wordt
                    standaard getoond aan alle applicaties die gebruik maken van het eHealth-aanmeldsysteem. De
                    applicatie Sociale Kaart heeft nooit toegang tot uw gezondheidsgegevens, maar gebruikt dit
                    aanmeldsysteem enkel om personen uniek te identificeren.</p>
                <p>Bij het inloggen dient iedereen een rol te kiezen. Wie een zorgverlener
                    is met een RIZIV-nummer zal kunnen kiezen om in te loggen als ‘burger’ of als ‘zorgverlener’.
                    Alle andere personen krijgen enkel de optie ‘burger’ te zien. Hierdoor wordt een burger uniek
                    geïdentificeerd en gekoppeld aan de account in de applicatie Sociale Kaart. Inloggen als
                    ‘zorgvoorziening’ is niet mogelijk.</p>
                <p>De Vlaamse overheid kan niet aansprakelijk worden gesteld voor
                    rechtstreekse of onrechtstreekse schade die ontstaat door het gebruik van de website of van de
                    op of via de website ter beschikking gestelde informatie. Dat geldt zonder enige beperking ook
                    voor alle verliezen, werkonderbrekingen, beschadigingen van uw apparatuur, programma's of andere
                    gegevens op jouw computersysteem. De Vlaamse overheid kan niet garanderen dat de
                    site </span><a href="{{route('home')}}" rel="noopener noreferrer"
                        target="_blank">www.desocialekaart.be</a> volledig vrij van onderbreking is en niet door
                    andere technische problemen wordt getroffen.</p>
            </div>
        </section>

        <section id="toegankelijkheid">
            <div class="title-container">
                <div class="icon-container">
                    <i class="fa-solid fa-person"></i>
                </div>
                <h2>toegankelijkheid</h2>
            </div>

            <div class="recap-card">
                <h4>In het kort</h4>
                <p>
                    We willen dat deze website voor iedereen toegankelijk en makkelijk te gebruiken is, ook voor mensen met
                    een beperking. Daarom houden we rekening met leesbaarheid, navigatie en gebruiksgemak. We blijven de
                    site verbeteren, maar sommige onderdelen (zoals bepaalde PDF’s of video’s) zijn mogelijk nog niet
                    volledig toegankelijk. Ervaart u problemen? Laat het ons weten, dan zoeken we samen naar een oplossing.
                </p>
            </div>

            <button id="toegankelijkheid-extra-info-btn" class="extra-info-btn">
                <p>Volledige informatie
                <p>
                    <span class="arrow"><i class="fa-solid fa-angle-down"></i></span>
            </button>

            <div id="toegankelijkheid-extra-info" class="extra-info">
                <h1>Wat is webtoegankelijkheid?</h1>
                <p>Webtoegankelijkheid betekent dat websites en -toepassingen toegankelijk en bruikbaar zijn voor
                    iedereen. De toegankelijkheid van aangeboden informatie en diensten is namelijk niet voor iedereen
                    vanzelfsprekend. Denk aan slechtzienden en blinden, ouderen met een verminderd gezichtsvermogen,
                    maar ook mensen met beperkte handfunctie of een beperkte leesvaardigheid.</p>
                <p>Webtoegankelijkheid steunt op de volgende 4 principes:</p>
                <ul>
                    <li><strong>Waarneembaar</strong>: informatie en componenten van de gebruikersinterface moeten
                        toonbaar zijn aan gebruikers op voor hen waarneembare wijze.</li>
                    <li><strong>Bedienbaar</strong>: componenten van de gebruikersinterface en navigatie moeten
                        bedienbaar zijn.</li>
                    <li><strong>Begrijpelijk</strong>: Informatie en de bediening van de gebruikersinterface moeten
                        begrijpelijk zijn.</li>
                    <li><strong>Robuust</strong>: informatie moet voldoende robuust zijn om betrouwbaar geïnterpreteerd
                        te kunnen worden door een breed scala van gebruikers.</li>
                </ul>
                <p>Elke overheidswebsite moet verplicht een toegankelijkheidsverklaring hebben. Deze legt gedetailleerd
                    en duidelijk uit in welke mate de website voldoet aan de vooropgestelde normen en wordt regelmatig
                    bijgewerkt.</p>
                <h1>Toegankelijkheidsverklaring desocialekaart.be</h1>
                <p>Het departement Zorg, verantwoordelijke voor de Sociale Kaart, streeft ernaar deze website
                    toegankelijk te maken voor alle bezoekers, ook voor bezoekers met een motorische, visuele of
                    auditieve beperking. De site beantwoordt al aan vele normen en voorschriften voor toegankelijke
                    websites. Enkele voorbeelden:</p>
                <ul>
                    <li>De pagina's op deze website zijn gestructureerd en betekenisvol opgebouwd.</li>
                    <li>Als u de grootte van de tekst aanpast met de browserfuncties, dan past de lay-out van de
                        pagina's zich automatisch aan.</li>
                    <li>Menu's en formulieren zijn zo ontworpen dat ze eenvoudig bruikbaar zijn voor wie de muis niet
                        kan gebruiken.</li>
                    <li>Alle pagina's bevatten een broodkruimelnavigatie.</li>
                    <li>Content, zoals bijvoorbeeld handleidingen, wordt zoveel mogelijk via webpagina’s en zo weinig
                        mogelijk via PDF-bestanden op de Sociale Kaart</li>
                    <li>Videobestanden zullen we indien mogelijk van ondertiteling voorzien.</li>
                </ul>
                <h1>Niet-toegankelijke inhoud</h1>
                <p>Desocialekaart.be wordt verder getest op toegankelijkheid. Het is mogelijk dat de onderstaande inhoud
                    nog niet toegankelijk is:</p>
                <ul>
                    <li>Documenten die enkel in PDF-formaat gepubliceerd kunnen worden. Wie problemen ondervindt met de
                        toegankelijkheid ervan, kan een alternatief formaat vragen via <a
                            href="https://mailto:desocialekaart@vlaanderen.be" rel="noopener noreferrer"
                            target="_blank">desocialekaart@vlaanderen.be</a>.</li>
                    <li>Niet alle video’s zijn van ondertiteling voorzien.</li>
                </ul>
                <h1><strong>Contact</strong></h1>
                <p>Ondervindt u een probleem met de toegankelijkheid van deze site, neem dan contact op via <a
                        href="https://mailto:desocialekaart@vlaanderen.be/" rel="noopener noreferrer"
                        target="_blank">desocialekaart@vlaanderen.be</a>. We onderzoeken iedere vraag.</p>
                <p>Hebt u contact opgenomen maar bent u niet tevreden met het antwoord? Stuur dan uw klacht naar de <a
                        href="https://www.vlaanderen.be/departement-zorg/klachten" rel="noopener noreferrer"
                        target="_blank">klachtenbehandelaar van het Departement Zorg</a>.</p>
            </div>
        </section>
    </div>
    @endif
@endsection
