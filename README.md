# Jongerenkaart

Jongerenkaart helps young people find organisations and services in Flanders and Brussels. This document explains how visitors, contactpersonen, and administrators use the website.

For installation and maintenance, see [PREREQUISITES.md](PREREQUISITES.md). For the technical architecture and data flows, see [DESIGN.md](DESIGN.md).

## Public Website

The public website is responsive and designed primarily for smartphone portrait and laptop landscape screens. Visitors can switch between light and dark mode from the header. Their choice is remembered in the browser.

The main pages are:

- `/`: home page with search, category shortcuts, location search, urgent-help information, favourites, and recently viewed organisations.
- `/search`: organisation search with category pills and additional filters.
- `/kaart`: location-based search on an interactive map.
- `/organisaties/{id}`: full organisation details.
- `/info`, `/contact`, `/faq`, and `/privacy`: supporting information.

## Home Page

The home page provides several ways to start:

- Entering search terms opens the search page and runs the search.
- Category pills open the search page with that category selected.
- `Zoek organisaties via locatie` opens the map page.
- Guide questions such as finding friends, help with stress, and study support start a related search.
- The urgent-help section links to the official 112 website and the 112 BE app.

Favourites and recently viewed organisations are stored locally in the visitor's browser. No account is required. The home page shows the five most recently viewed organisations, lets visitors clear that history, and lets them remove favourites.

## Searching

The search page searches organisation names, categories, subcategories, sub-subcategories, descriptions, and their combined search context.

It supports:

- category pills;
- place and distance;
- age as a number, including the boundaries of stored age ranges;
- rubriek;
- werkingsgebied;
- language;
- payment method, including `Gratis` and `kosteloos` based on `Kostprijs`;
- aanbod;
- clearing all filters.

When no category is selected, all categories are included and all category pills appear at full colour. Once one or more categories are selected, unselected categories are dimmed.

The five best results are shown initially. `Toon alle resultaten` reveals the remaining returned results, up to the configured maximum of 100. When more results exist, the page asks the visitor to refine the search.

Result cards contain the organisation name, category colour, address, description, contact email, distance when relevant, and a free badge when applicable. Addresses open the default navigation application, email links open the default mail application, and the whole card links to the organisation detail page. Long descriptions can be expanded with `Lees meer`.

Search is reactive by default. An administrator can disable reactive text search globally. When disabled, visitors press Enter to search; deliberate filter changes still update results immediately.

## Map

The map page uses the same organisation search and category choices as the search page. A visitor selects a supported place and an `Afstand` of 1 km, 3 km, or a 5 km increment up to 80 km.

The page then:

- marks the selected place;
- draws the selected radius;
- shows organisations inside the radius;
- colours pins and clusters by category;
- lists result cards from closest to farthest;
- synchronises selection between a card and its map pin.

Visitors can also place their own location pin on the map. Text search limits both the map pins and the side-panel results. Category clustering keeps crowded areas readable until the visitor zooms in.

## Organisation Details

Each organisation has a tabbed detail page containing the available document data. Missing MongoDB fields are simply omitted.

Depending on the organisation, tabs can show:

- general operation and target audience;
- addresses and a Leaflet map using latitude and longitude;
- general contact details;
- contactpersonen;
- recognitions;
- administrative details;
- additional information;
- services and opening hours.

The page also supports favourites and adds the organisation to the browser's recently viewed list.

## Contact Page

The contact page does not send mail through Laravel. After the visitor completes the form, the website opens the visitor's default email application with the recipient, subject, sender details, and message filled in.

The initial recipient is `admin@jongerenkaart.be`. An administrator can change it in `Instellingen`. This form is for questions about Jongerenkaart itself, not for contacting an external care provider.

## Accounts

There are two account roles:

- `admin`: can manage every organisation and all website settings.
- `contactpersoon`: can manage only the organisations linked to that account.

Visitors log in at `/login`. Password changes are available from `/mijn-account` after login. A login lasts at most one hour, even while the user remains active. There is no persistent `Ingelogd blijven` option.

A new contactpersoon can request an account at `/account-aanmaken` only when the submitted email address already occurs as either a general organisation email or a contactpersoon email. The account is linked to every matching organisation but remains inactive. An admin must activate it in `Instellingen` before the contactpersoon can log in. Admins can also delete pending or active accounts.

After login, protected links are added to the navigation after a divider. On small screens these links appear in the hamburger menu.

## Organisation Management

Logged-in users open `/beheer/organisaties` to see organisations they may edit. Admins see all organisations; contactpersonen see only their linked organisations. The list can be searched by organisation name.

The edit form follows the tab layout of the public detail page. It supports normal inputs, dropdowns based on existing data, repeatable list rows, and an address map for checking coordinates.

Dropdown fields offer `Andere` when a new value is needed. New values become available to later edits and relevant public filters. Saving an organisation immediately updates the database, search result, and dropdown options affected by that one organisation.

Only admins can create or permanently delete organisations. Delete actions are available from the organisation list and edit page and require confirmation. Deleting also removes the organisation from Meilisearch and decrements or removes its cached filter options.

A new organisation requires:

- a name;
- either a general email address or at least one contactpersoon email.

All other organisation fields may be empty.

## Administrator Settings

Only admins can open `/beheer/instellingen`. The page provides:

- the website contact email setting;
- the global reactive-search switch;
- MongoDB and Meilisearch health information;
- a full dropdown-option rebuild;
- a full Meilisearch index rebuild;
- a combined repair action;
- a complete MongoDB organisation JSON export;
- a link that opens a new email with all organisation addresses in BCC;
- editing of the public Info, Privacy, and FAQ HTML;
- creation, editing, and deletion of public Info-page medewerkers;
- user search and role display;
- activation of pending contactpersoon accounts;
- user password resets and deletion;
- creation of additional admin users.

Resetting an editable public page removes its custom MongoDB version and returns to the version supplied by the application code.

The `Team Sociale Kaart` cards on the public Info page always come directly from the medewerkers managed in `Instellingen`. They are intentionally kept outside the editable Info HTML, so changing or deleting a medewerker updates the cards without requiring the Info page itself to be saved again.

## Local Statistics

Admins can open `/beheer/statistieken` from the protected navigation. This dashboard shows locally collected traffic, active visitors and logins, search and map use, popular pages, device groups, technical signals, and organisation changes. It does not use Google Analytics or another external analytics service.

Opening the page does not recalculate it. The displayed numbers are a saved snapshot with a visible update time. An admin uses `Dashboard bijwerken` to calculate and save a new snapshot. “Active” means that the visitor or logged-in user was seen during the five minutes before that calculation.

The local tracker does not save IP addresses, full user-agent strings, or entered search text. It uses a random first-party visitor identifier and saves only its keyed hash. Public traffic totals, bots, broad device groups, response time, and organisation changes are aggregated per day.

## Related Documentation

- [PREREQUISITES.md](PREREQUISITES.md): requirements, Docker, clean installation, development, production, backups, resets, and maintenance commands.
- [DESIGN.md](DESIGN.md): Laravel structure, MongoDB source-of-truth design, Meilisearch ranking and filters, incremental synchronisation, permissions, and admin settings.
