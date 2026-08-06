# Application Design

This document describes the architecture and data flows of Jongerenkaart. For website behaviour, see [README.md](README.md). For installation and operations, see [PREREQUISITES.md](PREREQUISITES.md).

## Design Goals

The application is designed around these rules:

- MongoDB is the source of truth for organisation data.
- Meilisearch is a disposable, rebuildable search index.
- Normal edits update only changed derived data.
- Full scans are explicit admin maintenance actions.
- Missing organisation attributes are valid.
- Authorisation is enforced on the server.
- The public interface works on smartphone portrait and laptop landscape screens.

## Runtime Architecture

```text
Browser
  |
  v
Caddy
  |
  v
nginx
  |
  v
Laravel / PHP-FPM
  |                 |
  v                 v
MongoDB         Meilisearch
source data     derived index
```

The development `app` container also supplies Composer, npm, and Artisan. In
production, Caddy terminates HTTPS and is the only public container. Caddy
reaches nginx over `edge`; nginx reaches Laravel, MongoDB and Meilisearch over
`backend`. Portainer is isolated on `management` and is available only through
a loopback-bound SSH tunnel. nginx serves public files and forwards PHP
requests to PHP-FPM.

## Capacity And Scaling Analysis

The current measured dataset is small:

| Item | Measured size |
| --- | ---: |
| Organisation documents | 2,240 |
| Source JSON | 5.9 MB |
| Local MongoDB files | approximately 219 MB |
| Local Meilisearch files | approximately 40 MB |

The expected database growth is limited, so storage-engine capacity is not the main scaling risk. Request concurrency, PHP-FPM worker capacity, map requests, search-index rebuilds, and competition for RAM on a single Docker host matter more.

### Strengths At The Expected Scale

- MongoDB document-level concurrency allows different organisations to be updated concurrently.
- Normal writes update one organisation in MongoDB and Meilisearch instead of rebuilding the full dataset.
- Filter options use incremental counts and a small cache rather than scanning all organisations per request.
- Meilisearch ranks a compact derived document rather than every MongoDB field.
- The dataset and index are small enough to fit comfortably in memory on a modest production server.
- Only three simultaneous authenticated users creates negligible session-storage pressure on a single app instance.

### Current Bottlenecks And Limits

- All four services share one host. A Meilisearch rebuild, MongoDB activity, and PHP traffic can compete for the same CPU, RAM, and disk I/O.
- Docker currently has no service resource limits. MongoDB and Meilisearch can infer available memory from the host or container and consume significant memory during caching or indexing.
- PHP-FPM worker count limits how many Laravel requests execute simultaneously. Extra requests wait in nginx/FPM queues; 100 connected users does not mean 100 PHP requests should execute at once.
- Map result generation loads organisations with coordinates and performs distance filtering in Laravel. This is acceptable for roughly 2,240 documents, but repeated simultaneous map requests are heavier than ordinary Meilisearch requests.
- Scout writes and filter-option updates run synchronously. A burst of 20 edits may increase response latency even though it should remain manageable at the current dataset size.
- File-based sessions and cache are suitable for one `app` container. They are not shared across multiple app replicas.
- MongoDB, Meilisearch, and nginx are single instances. The default deployment has no automatic failover; backups provide recovery, not high availability.
- Public CSS, JavaScript libraries, icons, map code, and map tiles partly depend on external CDNs. Their availability and network latency can affect the frontend independently of this server.

### Default Workload Assessment

For up to 100 concurrent visitors, up to 20 simultaneous organisation updates, and up to three active logins, a single 4 vCPU and 8 GB RAM server should be a reasonable starting point when paired with NVMe/SSD storage. This is a capacity estimate, not a measured guarantee.

The practical request rate depends on visitor behaviour. One hundred visitors reading pages is very different from one hundred visitors continuously typing into reactive search. The global reactive-search switch is therefore a useful load-control mechanism. Disable it when Meilisearch or PHP request volume becomes a problem.

Before launch, test the production image with representative search, map, detail, login, and 20-writer traffic. Measure p95 response time, PHP-FPM queue depth, CPU steal/throttling, MongoDB operation queues, Meilisearch task duration, RAM, swap, and disk latency.

### Scaling Path

Scale vertically first while the dataset remains small: add CPU for request/indexing concurrency and RAM to prevent MongoDB, Meilisearch, and PHP from competing. Move to multiple hosts when availability or sustained traffic requires it.

Before adding multiple Laravel app replicas:

1. Move sessions and cache from local files to Redis or another shared store.
2. Put app/nginx replicas behind a load balancer.
3. Move MongoDB to a managed deployment or replica set with tested backups.
4. Run Meilisearch on dedicated resources or use its managed service.
5. Move public assets to immutable object storage/CDN or bake a reliable shared-asset deployment process.
6. Move synchronous search updates to a queue when write bursts become slow.

The current design is appropriate for a modest single-server deployment, but it should not be described as highly available until these shared-state and replica changes are made.

## Laravel Structure

Browser routes are defined in `src/routes/web.php`. JSON organisation CRUD routes are defined in `src/routes/api.php`.

Important HTTP controllers include:

- `HomePageController`: home-page data.
- `SearchPageController`: search-page filters and settings.
- `MapController`: map page, places, radius filtering, and map JSON.
- `OrganisatiePageController`: public organisation details.
- `OrganisatieController`: search JSON and organisation API CRUD.
- `OrganisationManagementController`: authorised organisation create/edit screens.
- Auth and account controllers: login, registration, logout, and passwords.
- `AdminSettingsController`: health checks, rebuilds, exports, and site settings.
- `AdminPeopleController`: medewerkers and user administration.
- Content-page controllers: public and editable Info, Privacy, and FAQ pages.

Important services include:

- `OrganisatieSearchService`: Meilisearch query construction and MongoDB hydration.
- `OrganisatieFilterOptionsService`: filter-option extraction, counts, and cache.
- `OrganisatieLocationService`: coordinates, place options, and distance logic.

## MongoDB Data Model

`App\Models\Organisatie` uses the MongoDB Laravel integration and maps to the `organisaties` collection. Organisation documents are intentionally flexible: the model understands all known fields, but individual documents may omit any optional field or nested level.

Nested arrays and objects remain part of the organisation document. Helpers on the model normalise the different shapes needed by views, filters, addresses, contact details, and search indexing.

Other MongoDB collections include:

- `users`: login identity, password hash, role, and linked organisation IDs.
- `organisatie_filter_options`: one document per filter field/value with a usage count.
- `site_settings`: global settings such as contact email and reactive search.
- `content_pages`: admin-edited HTML for Info, Privacy, and FAQ.
- `medewerkers`: people displayed on the public Info page.
- `traffic_daily_stats`: one aggregated traffic document per day.
- `traffic_visitors`: pseudonymous last-seen records used for five-minute activity counts.
- `traffic_snapshots`: manually generated admin dashboard snapshots.

MongoDB IDs are also used as Meilisearch document IDs and API identifiers. This keeps one stable identity across stores.

## Meilisearch Index

`Organisatie::toSearchableArray()` creates the smaller document copied to Meilisearch. It converts nested and inconsistent MongoDB values into predictable strings, arrays, booleans, and numeric ages.

The searchable attributes configured in `src/config/scout.php` are:

```text
name
category
subcategory
subsubcategory
description
context
```

The filterable attributes are:

```text
category
leeftijdscategorie
ages
rubriek
werkingsgebieden
taalAanbod
betalingswijze
isFree
aanbod
```

The displayed Meilisearch fields are intentionally limited because final display data is loaded from MongoDB.

## Exact And Context Search

Meilisearch performs one full-text query across the configured searchable attributes. Direct terms in an organisation's name, category, subcategory, sub-subcategory, or description can therefore rank as normal textual matches. Meilisearch also provides typo tolerance and its normal textual ranking rules.

The `context` attribute is a normalised concatenation of:

```text
name + category + subcategory + subsubcategory + description
```

This combined field allows a service description entered by a visitor to match words spread across those source fields. It is the current implementation of contextual search.

This project does not currently configure a Meilisearch embedder or submit query vectors. The contextual search is lexical full-text search, not true embedding-based semantic/vector search. Adding vector search later would require an embedder configuration, generated document vectors or Meilisearch-managed embeddings, and hybrid-search request options.

## Search Request Flow

```text
Search text and filters
  -> OrganisatieSearchService
  -> Meilisearch filters candidate documents and ranks text matches
  -> matching MongoDB IDs are returned
  -> Laravel converts IDs to ObjectId values
  -> full organisation documents are loaded from MongoDB
  -> results are restored to Meilisearch ranking order
  -> JSON or Blade UI is rendered
```

MongoDB is always used for final organisation content. Stale or missing Meilisearch documents can affect discoverability, but cannot replace the source document.

Multiple values inside one filter are OR conditions. Different filter fields are AND conditions. Filters limit the Meilisearch candidate set before textual ranking.

Age ranges are normalised into searchable numeric ages. A stored range such as 12 through 18 includes both boundaries. Free detection is derived only from `Kostprijs`, including wording such as `gratis` or `kosteloos`.

The public search endpoint returns at most 100 organisations. The UI initially renders the best five and can reveal all returned results.

## Reactive Search

`SiteSetting::reactiveSearchEnabled()` reads the global setting from MongoDB through Laravel's cache.

When enabled, the search and map text fields debounce input before requesting results. When disabled, typing does not request results and Enter submits the text. Selects, category pills, and other deliberate filter changes remain immediate.

The admin update writes the MongoDB setting and immediately replaces its cache entry, so no restart or global cache clear is required.

## Filters And Cached Options

Dropdown options are not extracted from every organisation on every page request. `organisatie_filter_options` stores:

```text
field
value
count
```

`OrganisatieFilterOptionsService` caches the resulting option lists under `organisatie_filter_options_v3`.

For a normal update:

1. The service extracts old filter values.
2. The organisation is changed.
3. It extracts new filter values.
4. Removed values decrement their count.
5. Added values increment their count.
6. Values with count zero are deleted.
7. The small option collection is read back into cache.

Selecting `Andere` in an edit form creates a normal organisation value. The incremental comparison adds it to the option store so later editors and applicable filters can use it.

The admin rebuild is the exceptional repair path. It scans all organisations and recreates the count collection and cache after a raw import or suspected inconsistency.

## Organisation Write Flow

Normal create, update, and delete operations follow this sequence:

```text
Authorised request
  -> validate known fields and permissions
  -> write MongoDB organisation
  -> searchable() or unsearchable() for that Meilisearch document
  -> update changed filter-option counts
  -> refresh option cache from the count collection
```

The entire organisation database is not re-indexed after one edit. Full Meilisearch and option rebuilds are available only as explicit admin maintenance actions.

Unknown API fields are ignored so writes stay aligned with `Organisatie::KNOWN_FIELDS`. New organisations receive their MongoDB ID before search synchronisation.

## Map And Location Search

`OrganisatieLocationService` reads coordinates from supported nested and legacy address shapes, including GeoJSON-style coordinates, latitude/longitude, x/y, and the existing geocoordinate fields.

Place options are derived from organisation addresses and shared by `/search` and `/kaart`. Labels combine place and postal code. Distance is calculated in Laravel using latitude/longitude and a Haversine-style calculation.

Map text search first uses Meilisearch to identify matching organisation IDs. Laravel then applies coordinate, radius, and category rules to organisations with usable locations. Leaflet renders the map and Leaflet MarkerCluster groups nearby pins per category.

## Authentication And Authorisation

`User` is a MongoDB-compatible Laravel authentication model.

Roles are:

- `admin`: unrestricted organisation management plus settings and user administration.
- `contactpersoon`: management access only to IDs stored in `organisatie_ids`.

Registration does not trust a requested organisation ID. Laravel searches organisation general and contactpersoon email fields for the submitted address and stores the matching IDs on the new contactpersoon user. The new account is stored with `is_active=false`, is not logged in, and remains unusable until an admin activates it in Instellingen. Legacy users without this field remain active so deployment does not unexpectedly lock existing accounts.

The `auth` and `session.fresh` middleware protect account and organisation-management routes. The same stateful middleware, including the web middleware group's CSRF protection, protects every `/api/organisaties` route. API create/delete operations require an admin; reads and updates require an admin or a linked contactpersoon. The `role:admin` middleware protects settings, editable public pages, medewerkers, user management, backup, and bulk-mail actions. UI visibility is helpful but is not the security boundary; route middleware and controller checks enforce access.

Only admins can create or delete an organisation or create another admin account. Contactpersonen can edit only linked organisations. Organisation deletion is centralized in `OrganisatieDeletionService`, which removes the MongoDB document and Meilisearch entry, decrements filter-option counts, refreshes their cache, and records the deletion statistic.

Laravel's ordinary session lifetime is 60 minutes of inactivity. `EnsureSessionIsFresh` additionally records the login time and enforces an absolute 60-minute maximum, so continued activity cannot extend a login indefinitely. Persistent remember-login is disabled.

## Admin-Controlled Content And Settings

Global settings use the `site_settings` collection and individual cache keys. Current settings are:

- `contact_email`, defaulting to `admin@jongerenkaart.be`;
- `reactive_search_enabled`, defaulting to `true`.

The contact form constructs a `mailto:` link in the visitor's browser. Laravel supplies the configured recipient but does not receive or send the completed message.

Info, Privacy, and FAQ have code-based Blade defaults. An admin can save replacement HTML in `content_pages`. Public controllers prefer saved HTML when present. Resetting deletes that custom document and exposes the code default again. Since saved HTML is rendered as trusted raw HTML, only admin routes may modify it.

`Team Sociale Kaart` is not part of the editable Info HTML. It is rendered after either the default or custom content from the current `medewerkers` collection. `InfoPageContentService` removes the old statically rendered team section from legacy saved HTML and from future Info-page updates, preventing duplicate or stale employee cards.

Medewerkers are stored separately so Info-page people can be managed without editing page code. Default medewerkers are seeded when the collection is empty.

The settings page also provides database/search health checks, JSON backup streaming, Meilisearch rebuilding, option rebuilding, bulk BCC email creation, user search, password resets, user deletion, and admin creation.

## Local Traffic Statistics

`RecordTraffic` is a terminating Laravel web middleware. It lets the normal response complete first and then passes a small event to `TrafficRecorder`. Public page views, search result requests, map result requests, broad device groups, response duration, error responses, and detected bot requests are aggregated into one `traffic_daily_stats` document per date.

The tracker deliberately does not persist IP addresses, complete user-agent strings, referrer URLs, or search input. Guests receive a random first-party `jk_visitor_id` cookie; MongoDB receives only an HMAC hash made with `APP_KEY`. Authenticated activity uses a keyed hash of the user ID. Admin and account routes update last-seen activity but do not inflate public page-view totals.

`traffic_visitors` stores first-seen and last-seen timestamps and the minimum data required for activity counts. “Concurrent visitors” and “active logins” mean distinct records seen within the preceding five minutes; they are a practical activity estimate rather than open network connections.

The admin statistics page never scans or recalculates data during a normal GET request. `TrafficSnapshot` returns the last saved dashboard. Pressing `Dashboard bijwerken` explicitly invokes `TrafficDashboardService`, which:

1. reads the last 30 days of daily aggregates;
2. counts visitors active during the last five minutes;
3. reads current organisation and user totals;
4. saves one replaceable `admin_dashboard` snapshot;
5. prunes daily traffic older than 400 days and inactive visitor records older than 90 days.

Organisation create, update, and delete paths increment only that day's corresponding action count. They do not rebuild analytics, filter options, or the full search index. The statistics dashboard therefore has low request-time overhead and a deliberately admin-controlled reporting cost.

This is first-party application telemetry, not a replacement for infrastructure monitoring. nginx/PHP-FPM, container, CPU, memory, disk, and database health should still be monitored at server level. Before production, document the visitor identifier and retention policy in the site's applicable privacy/cookie information.

## Browser-Owned State

Guest favourites, favourite card snapshots, recently viewed organisations, and theme preference use browser `localStorage`. They are device/browser specific and are not attached to an account.

No login is required for these features. Clearing browser site data removes them.

## Frontend Design

Blade components provide the shared header, navigation, footer, category pills, result cards, and repeated management controls. Bootstrap is loaded from a CDN for common layout and controls; project CSS variables and page-specific classes provide the Jongerenkaart visual system.

The primary colour is `#2563eb`. Category colours are centrally configured in `src/config/jongerenkaart.php` so pills, cards, map pins, and clusters share one mapping.

Light and dark themes use CSS custom properties. The header toggle writes the chosen theme to local storage. Form fields, cards, tabs, map panels, badges, and admin controls have theme-specific contrast rules.

Leaflet and Font Awesome are loaded from CDNs on pages that need them.

## Repair Boundaries

Derived state can always be rebuilt from MongoDB:

- Meilisearch can be deleted and recreated from organisations.
- Filter counts and their Laravel cache can be recreated from organisations.
- Code-default public pages return when a custom content document is reset.

MongoDB cannot be reconstructed from Meilisearch because the index intentionally omits fields. Back up MongoDB before destructive imports or data-folder resets.
