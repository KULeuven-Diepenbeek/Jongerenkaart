# Testing and TDD

This document explains the tests currently present in the Jongerenkaart
project, what they verify, how to run them, and how to use the test suite while
working test-first.

## What TDD Means Here

Test-driven development normally follows a short cycle:

1. **Red:** write a small test that describes the required behaviour and watch
   it fail for the expected reason.
2. **Green:** implement the smallest reasonable change that makes the test
   pass.
3. **Refactor:** improve the production and test code without changing the
   behaviour. Keep the tests green.

Not every existing test in this project was necessarily written before its
implementation. Some are regression tests added after a bug or feature. They
are still valuable: they preserve the intended behaviour and allow future work
to follow the TDD cycle.

## Test Stack

The project uses:

- PHPUnit 12 as the test framework;
- Laravel's `php artisan test` command as the normal test runner;
- Laravel testing helpers for HTTP responses and Blade views;
- `src/phpunit.xml` for the testing environment and test-suite definitions.

Tests live under:

```text
src/tests/
|-- TestCase.php
|-- Unit/
|   |-- EnsureSessionIsFreshTest.php
|   |-- EnsureUserRoleTest.php
|   |-- ExampleTest.php
|   |-- InfoPageContentServiceTest.php
|   |-- OrganisatieContactAndLocationTest.php
|   |-- OrganisatieDeletionServiceTest.php
|   |-- OrganisatieMetadataTest.php
|   |-- OrganisatieSearchFilterBuilderTest.php
|   `-- UserAccountStatusTest.php
`-- Feature/
    |-- AccountApprovalTest.php
    |-- AdminStatisticsViewTest.php
    |-- ExampleTest.php
    |-- InfoPageViewTest.php
    |-- OrganisationApiAuthenticationTest.php
    |-- OrganisationManagementDeletionTest.php
    `-- SearchInterfaceViewTest.php
```

Methods whose names start with `test_` are discovered automatically by
PHPUnit.

## Unit and Feature Tests

### Unit tests

Unit tests normally exercise a small method or class without making HTTP
requests or using external services.

They belong in:

```text
src/tests/Unit
```

`Unit/ExampleTest.php` extends PHPUnit's basic `TestCase`, so Laravel is not
booted at all.

`OrganisatieMetadataTest.php` extends the project's Laravel `Tests\TestCase`.
It still behaves mostly like a unit test, but Laravel is booted so configuration
such as `config('jongerenkaart.category_colors')` is available.

The folder name is a useful convention, not a technical guarantee that a test
cannot access Laravel or a database.

### Feature tests

Feature tests exercise a larger application boundary, such as:

- an HTTP route;
- middleware;
- authentication and authorization;
- controller behaviour;
- a complete Blade view.

They belong in:

```text
src/tests/Feature
```

Feature tests extend `Tests\TestCase`, which boots the Laravel application.

## Current Tests

### `Unit/ExampleTest.php`

Test:

```php
test_that_true_is_true
```

This is Laravel's generated example. It only asserts that `true` is true. It
verifies that PHPUnit itself can execute but does not protect project
behaviour. It can be removed once the project has enough meaningful tests.

### `Unit/OrganisatieMetadataTest.php`

This file contains seven project-specific regression tests.

#### Free status uses only `Kostprijs`

```php
test_free_status_only_uses_kostprijs
```

It verifies:

- `Volledig gratis` is treated as free;
- `Kosteloos voor jongeren` is treated as free;
- a value such as `Gratis` in `betalingswijze` does not make an organisation
  free when `Kostprijs` says `Betalend`.

This protects the rule that the `Gratis` badge and filter derive exclusively
from `Kostprijs`.

#### Anonymous status accepts an explanation

```php
test_anonymous_status_accepts_yes_with_an_explanation
```

It verifies:

- `Ja (op verzoek)` is treated as anonymous help;
- the Eloquent-style `is_anoniem` accessor returns the same result;
- `Neen` is not anonymous;
- a missing value is not anonymous.

#### New metadata accessors return the MongoDB values

```php
test_new_metadata_is_available_through_model_accessors
```

It verifies access to:

- telephone availability;
- chat availability;
- `sociale_kaart_id`.

The test does not save anything to MongoDB. It constructs an in-memory model
and checks how its fields are exposed.

#### Meilisearch documents contain derived filters

```php
test_search_document_contains_derived_boolean_filters
```

It calls `toSearchableArray()` and verifies that the generated Meilisearch
document contains:

- `isFree`;
- `isAnonymous`.

It does not contact Meilisearch. It tests only the document that would be sent
to Meilisearch.

#### Age ranges include their boundaries

```php
test_age_range_includes_both_boundaries
```

It verifies that `12 - 18 jaar` produces all ages from 12 through 18, while 11
and 19 are excluded. This protects the inclusive age-filter behaviour.

#### Context contains all searchable descriptive fields

```php
test_context_contains_category_subcategories_and_description
```

It verifies that the contextual-search text contains the organisation name,
category, subcategory, subsubcategory and description.

#### Every known category has a colour

```php
test_all_known_categories_have_a_configured_color
```

It compares the expected categories with the keys in
`config/jongerenkaart.php`. The comparison ignores ordering.

This test fails when a developer adds or removes a known category without
deliberately updating the colour configuration or the test expectation.

### `Unit/OrganisatieContactAndLocationTest.php`

This file contains seven tests for contact and map behaviour:

- the primary address is selected, formatted and encoded in a Google Maps URL;
- primary telephone, email and website values take precedence;
- organisation and contact-person email addresses are normalized and
  deduplicated;
- GeoJSON coordinates are read in `[longitude, latitude]` order;
- invalid latitude and longitude values are rejected;
- radius filtering includes the boundary and sorts nearest to farthest;
- the selectable radius values start with 1, 3, 5 and 10 km and stop at 80 km.

These tests use in-memory `Organisatie` models and do not contact MongoDB or a
map provider.

### `Unit/OrganisatieSearchFilterBuilderTest.php`

This file tests the conversion of HTTP query parameters into Meilisearch filter
expressions. It verifies:

- several categories use an `IN` expression;
- age, free and anonymous aliases produce correctly typed filters;
- invalid ages and boolean values are ignored;
- duplicate values are removed;
- quotes and backslashes are escaped.

The filter builder was separated from `OrganisatieSearchService` so this
security-sensitive string construction can be tested without contacting
Meilisearch. The search service still uses the same builder in production.

### `Unit/EnsureUserRoleTest.php`

This file contains focused middleware tests. They verify that:

- an administrator can pass a route requiring the admin role;
- a contact person receives HTTP `403`;
- a guest receives HTTP `403`.

These tests protect the role check itself. Separate feature tests are still
needed to prove that every sensitive route is actually assigned the correct
middleware and object-level policy.

### `Unit/EnsureSessionIsFreshTest.php`

These tests verify that an authenticated session:

- remains valid at 59 minutes;
- receives HTTP `401` at 60 minutes;
- receives a fresh absolute deadline when an older pre-deployment session has
  no login timestamp yet.

This is separate from Laravel's idle session lifetime. Together they guarantee
that activity cannot extend a login beyond one hour.

### `Unit/UserAccountStatusTest.php`

These tests verify that explicitly pending users are inactive, approved users
are active, and legacy users without the new `is_active` field remain active
during deployment.

### `Unit/InfoPageContentServiceTest.php`

These tests verify that legacy saved Info HTML no longer embeds stale `Team
Sociale Kaart` cards, while unrelated custom HTML remains unchanged.

### `Unit/OrganisatieDeletionServiceTest.php`

This test verifies that one organisation deletion removes the Meilisearch
document and MongoDB model, applies the previous filter snapshot as a removal,
refreshes derived filter data and records the deletion statistic.

### `Feature/ExampleTest.php`

Test:

```php
test_the_application_returns_a_successful_response
```

It performs an internal Laravel request to `/` and expects HTTP status `200`.
No browser or real network connection is used.

This is a broad smoke test. Opening the homepage also resolves its controller,
services, middleware and view. In this project that path accesses MongoDB-backed
filter options and the traffic middleware may record activity. MongoDB must
therefore be reachable, and the test must use a separate test database.

### `Feature/AdminStatisticsViewTest.php`

Test:

```php
test_statistics_view_renders_without_a_snapshot
```

It renders the statistics Blade view directly with `snapshot => null`.

It verifies that the empty state contains:

- `Er is nog geen momentopname.`;
- `Dashboard bijwerken`.

This protects against the previous error where the statistics page crashed
before its first snapshot existed. It does not test the admin route,
authorization or dashboard database queries.

### `Feature/SearchInterfaceViewTest.php`

This file renders the public Blade views directly and verifies that:

- the search page contains the anonymous filter, anonymous result badge and
  clear-filter command;
- the map page contains the anonymous toggle and the 1, 3 and 80 km radius
  choices;
- the home page can render the new category pills.

These are server-rendered interface regression tests. They do not execute the
JavaScript, perform a real search or interact with Leaflet in a browser.

### `Feature/OrganisationApiAuthenticationTest.php`

These tests verify that every organisation API verb has `web`, `auth` and
`session.fresh` middleware, that guests receive `401`, and that a
contactpersoon cannot create an organisation.

### `Feature/AccountApprovalTest.php`

This MongoDB-backed feature test covers the complete approval boundary:

1. an email attached to an organisation can request an account;
2. the new account is inactive and no login session is created;
3. login is rejected while approval is pending;
4. an admin can activate the account;
5. the approval timestamp and active status are persisted.

### `Feature/InfoPageViewTest.php`

This test renders the Info page with custom editable content and verifies that
the current managed medewerker cards still appear in the separate dynamic
`Team Sociale Kaart` section.

### `Feature/OrganisationManagementDeletionTest.php`

These tests verify that the management DELETE route requires an admin role,
contactpersonen receive `403`, admins see the delete action, and
contactpersonen do not see it.

## Current Baseline

At the time this document was updated, the complete suite contains:

```text
46 tests
131 assertions
```

Run the suite after changing models, search filters, authorization, map
behaviour or public Blade views. The count can increase as new behaviour is
covered; a lower count should be investigated.

## Important Database Safety

`src/phpunit.xml` sets:

```xml
<env name="DB_CONNECTION" value="sqlite"/>
<env name="DB_DATABASE" value=":memory:"/>
```

That protects ordinary Laravel SQL models. However, models such as
`Organisatie` explicitly use the `mongodb` connection. They can still use
`MONGODB_URI` from the container environment.

Never run tests that write MongoDB models against the normal `jongerenkaart`
development database. Use a separate database:

```text
jongerenkaart_test
```

The current metadata tests do not write to MongoDB. The homepage feature test
can read and write analytics data, so the separate test database is still the
safe default.

Before adding database feature tests, the project should also gain a reusable
test cleanup strategy or a dedicated test Compose file. Until then, keep all
test fixtures inside `jongerenkaart_test` and drop only that database after a
run.

## Running the Tests with Docker

Run commands from the repository root, where `compose.yaml` is located.

### 1. Start the development services

```bash
docker compose up -d app mongodb meilisearch
```

The nginx container is not required for Laravel's internal feature tests.

### 2. Clear cached configuration

```bash
docker compose exec app php artisan config:clear
```

This matters because cached configuration can otherwise retain a development
database URI.

### 3. Run all tests safely

```bash
docker compose exec \
  -e MONGODB_URI=mongodb://mongodb:27017/jongerenkaart_test \
  -e MONGODB_DATABASE=jongerenkaart_test \
  app php artisan test
```

If your system uses the older binary, replace `docker compose` with
`docker-compose`.

### 4. Remove the test database afterwards

```bash
docker compose exec mongodb \
  mongosh jongerenkaart_test --quiet \
  --eval 'db.dropDatabase()'
```

This command intentionally deletes only `jongerenkaart_test`.

## Running a Subset

Use the same MongoDB environment overrides even when the current test does not
need MongoDB. That keeps the command safe when a test grows later.

### Unit suite

```bash
docker compose exec \
  -e MONGODB_URI=mongodb://mongodb:27017/jongerenkaart_test \
  -e MONGODB_DATABASE=jongerenkaart_test \
  app php artisan test --testsuite=Unit
```

### Feature suite

```bash
docker compose exec \
  -e MONGODB_URI=mongodb://mongodb:27017/jongerenkaart_test \
  -e MONGODB_DATABASE=jongerenkaart_test \
  app php artisan test --testsuite=Feature
```

### One test file

```bash
docker compose exec \
  -e MONGODB_URI=mongodb://mongodb:27017/jongerenkaart_test \
  -e MONGODB_DATABASE=jongerenkaart_test \
  app php artisan test tests/Unit/OrganisatieMetadataTest.php
```

### One class

```bash
docker compose exec \
  -e MONGODB_URI=mongodb://mongodb:27017/jongerenkaart_test \
  -e MONGODB_DATABASE=jongerenkaart_test \
  app php artisan test --filter=OrganisatieMetadataTest
```

### One test method

```bash
docker compose exec \
  -e MONGODB_URI=mongodb://mongodb:27017/jongerenkaart_test \
  -e MONGODB_DATABASE=jongerenkaart_test \
  app php artisan test --filter=test_free_status_only_uses_kostprijs
```

Adding `--stop-on-failure` is useful during TDD:

```bash
docker compose exec \
  -e MONGODB_URI=mongodb://mongodb:27017/jongerenkaart_test \
  -e MONGODB_DATABASE=jongerenkaart_test \
  app php artisan test --stop-on-failure
```

Do not use `--parallel` for MongoDB tests until each worker receives an
isolated database.

## Understanding the Output

A successful run looks broadly like:

```text
PASS  Tests\Unit\OrganisatieMetadataTest
PASS  free status only uses kostprijs
PASS  anonymous status accepts yes with an explanation

Tests: 28 passed (67 assertions)
```

Important terms:

- **Test:** one `test_*` method.
- **Assertion:** one expected fact checked by a test.
- **Failure:** the application ran, but the actual value did not match the
  expectation.
- **Error:** the test could not complete, for example because MongoDB was
  unavailable or an exception was thrown.
- **Skipped:** the runner deliberately did not execute the test.

A failing test is useful during the Red step only when it fails for the reason
you intended. A connection timeout is not a useful Red result for a business
rule test.

## Writing a New Test

Generate a feature test:

```bash
docker compose exec app php artisan make:test OrganisationAuthorizationTest
```

Generate a unit test:

```bash
docker compose exec app php artisan make:test OrganisationSearchDocumentTest --unit
```

Laravel creates the file under `tests/Feature` or `tests/Unit` inside the
container, which maps to `src/tests` on the host.

### Arrange, Act, Assert

A readable test normally has three conceptual parts:

```php
public function test_free_status_uses_kostprijs(): void
{
    // Arrange
    $organisatie = new Organisatie([
        Organisatie::FIELD_KOSTPRIJS => 'Gratis',
    ]);

    // Act
    $isFree = $organisatie->isGratis();

    // Assert
    $this->assertTrue($isFree);
}
```

Comments are not required when the three parts are already obvious. Clear
variable and method names are preferable.

### Good test names

A test name should describe observable behaviour:

```text
test_contact_person_cannot_edit_an_unrelated_organisation
test_unverified_email_does_not_receive_an_authenticated_session
test_anonymous_filter_is_forwarded_to_meilisearch
```

Avoid implementation-focused names such as:

```text
test_method_called
test_controller_works
```

Good tests should survive internal refactoring as long as user-visible
behaviour stays the same.

## A Practical TDD Example

Suppose the requirement is:

> A contact person must not edit an organisation after their membership is
> revoked.

### Red

Write a feature test that:

1. creates a contact user;
2. creates or fixtures two organisations;
3. grants and then revokes membership;
4. logs in as that user;
5. sends `PUT /beheer/organisaties/{id}`;
6. expects `403`;
7. verifies MongoDB still contains the old value.

Run only that test and confirm it fails because access is still granted, not
because the fixture or database setup is broken.

### Green

Implement active membership checks in the authorization layer. Run the same
test until it passes.

### Refactor

Move repeated membership setup into a factory/helper and keep the behaviour
test passing. Then run the complete suite to check for regressions.

## What Is Not Tested Yet

The current suite is small. It does not yet provide meaningful coverage for:

- persisted object-level API checks for unrelated organisations;
- successful admin API create/delete operations;
- proof of mailbox ownership beyond manual admin approval;
- login and registration throttling;
- password changes and admin password resets;
- organisation create, update and delete flows;
- incremental filter-option counts;
- real Meilisearch requests, index settings and failure handling;
- complete search and map request/result flows;
- MongoDB import and backup restoration;
- editable HTML sanitization;
- statistics aggregation;
- dark mode and responsive frontend behaviour;
- browser-side JavaScript, favourites and Leaflet interaction;
- nginx, Docker and AWS configuration.

The highest-priority additions should follow the threat order in
`SECURITY.md`, starting with tests that prove anonymous API users cannot write
data and unverified contact emails cannot obtain an account.

## Testing External Services

Use different test levels deliberately:

- **Unit test:** verify `toSearchableArray()` without contacting Meilisearch.
- **Application feature test:** mock/fake the Meilisearch client and verify the
  filter expression or submitted document.
- **Integration test:** use disposable MongoDB and Meilisearch test containers
  to verify real compatibility.
- **End-to-end test:** use a browser against a staging-like stack to verify the
  complete user workflow.

Most tests should be fast unit or feature tests. Keep a smaller integration
suite for the boundaries that mocks cannot prove, such as MongoDB ObjectIds,
Meilisearch filter syntax and index settings.

Never point integration tests at production services.

## Useful Non-PHPUnit Checks

Compile all Blade templates:

```bash
docker compose exec app php artisan view:cache
```

Clear the compiled views afterwards during development:

```bash
docker compose exec app php artisan view:clear
```

Validate routes and controller registration:

```bash
docker compose exec app php artisan route:list --except-vendor
```

Check PHP formatting:

```bash
docker compose exec app ./vendor/bin/pint --test
```

These checks complement PHPUnit but do not replace behaviour tests.

## Continuous Integration Recommendation

Every proposed change should eventually run:

1. Composer validation and dependency audit.
2. PHP formatting.
3. Unit tests.
4. Feature tests against an isolated test database.
5. Relevant MongoDB/Meilisearch integration tests.
6. Frontend build and dependency audit.
7. Container and security checks described in `SECURITY.md`.

The pipeline should fail when a test fails. Production deployment should only
use the exact commit and container images that passed the pipeline.

Code coverage can help identify untested areas, but it is not enabled by
default because the current PHP image does not include a coverage driver such
as Xdebug or PCOV. Coverage percentage should support test design, not replace
meaningful behavioural assertions.
