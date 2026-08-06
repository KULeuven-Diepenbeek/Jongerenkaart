# Security Assessment

Assessment date: 30 July 2026

Scope:

- Laravel application code, routes, authentication and authorization
- MongoDB and Meilisearch integration
- Development and production Docker Compose configuration
- nginx and PHP-FPM configuration
- Data handling, backups, analytics and administration
- A prospective AWS production deployment

This is a source and configuration review, not a penetration test. It did not
include dynamic scanning of a deployed production environment, an external
network test, a live dependency advisory audit, or a legal GDPR assessment.
The findings should be verified in a staging environment before launch.

## Executive Summary

**Current release decision: do not expose this version to the public internet.**

The application has useful baseline controls:

- Laravel web forms use CSRF protection.
- Authenticated management routes use `auth` middleware.
- Admin-only routes additionally use role middleware.
- Organisation editing performs an object-level permission check.
- All organisation API routes require an authenticated, CSRF-protected web
  session and enforce role/object-level permissions.
- New contact-person accounts require explicit admin activation.
- Sessions are regenerated after login and invalidated after logout.
- Authenticated sessions have an absolute maximum age of 60 minutes.
- Passwords are hashed through Laravel.
- Production disables `APP_DEBUG`.
- MongoDB and Meilisearch do not publish host ports in `compose.prod.yaml`.
- Caddy is the only public web container and nginx is isolated behind an
  `edge` network.
- Portainer binds only to host loopback and requires an SSH tunnel.
- MongoDB remains the source of truth and ordinary changes update derived data
  incrementally.
- Docker log rotation is configured.

Two of the four originally identified application blockers have now been
mitigated. The project owner currently accepts the remaining two as
non-blocking for the planned release, although this assessment still rates
them critical and recommends resolving them before public exposure:

1. A clean install creates a known admin account with the public password
   `Wachtwoord`.
2. The internet-facing nginx 1.27 image is on a branch affected by multiple
   published vulnerabilities fixed only in newer branches.

The current setup is suitable for local development. It requires application
fixes and an AWS security perimeter before production use.

## Data and Trust Boundaries

### Data classes

The system handles:

- public organisation and service information;
- names, email addresses and telephone numbers of contact people;
- account names, email addresses, password hashes and roles;
- organisation-to-user authorization links;
- pseudonymous visitor identifiers and authenticated activity;
- administrative settings and editable public HTML;
- MongoDB exports containing organisation and contact-person data.

No contact-form message is stored by the server because the form opens the
visitor's mail client. The application does not currently collect health
records from visitors. Contact details and account data are still personal
data and need controlled access, retention and backups.

### Trust boundaries

The main boundaries are:

1. Internet to Caddy or an external ALB.
2. Caddy to nginx and nginx to PHP-FPM.
3. Laravel to MongoDB and Meilisearch.
4. Admin/contact-person browser sessions to management routes.
5. Deployment tooling to AWS, the EC2 host and application secrets.
6. Git and deployment artifacts to the imported organisation dataset.

## Detailed Findings

### CRITICAL-01: Public unauthenticated organisation CRUD API

Status: **Remediated in the application on 27 July 2026.**

Implemented controls:

- Every `/api/organisaties` route now uses `web`, `auth` and `session.fresh`.
- API requests use the normal encrypted Laravel session cookie and web CSRF
  protection; anonymous JSON requests receive `401`.
- Create and delete require an admin.
- Reads and updates require an admin or a contactpersoon linked through
  `organisatie_ids`.
- Regression tests verify middleware on every API verb, anonymous rejection
  and rejection of contact-person create attempts.

Residual work:

- Use API Resources/DTOs with an explicit public field allowlist.
- Add route-specific rate limits.
- Add persisted-database tests proving contact people cannot read or update an
  unrelated organisation and admins retain all intended operations.
- Use Sanctum or another token mechanism only if a non-browser API client is
  introduced; the current API deliberately uses browser sessions.

### CRITICAL-02: Contact-person account takeover

Status: **Mitigated through mandatory admin approval on 27 July 2026.**

Implemented controls:

- Registration still requires an email present on an organisation, but stores
  the account with `is_active=false`.
- Registration no longer creates an authenticated session.
- Login explicitly rejects inactive accounts.
- The admin settings page lists pending users first and provides activation
  and deletion controls.
- Only an authenticated admin route can activate an account.
- A feature test covers registration, denied login and admin activation.

Operational requirement and residual work:

- Admins must verify the requester's identity through a trusted out-of-band
  channel before pressing `Activeer`; approval without verification recreates
  the original risk.
- Mailbox verification or signed, expiring invitations would provide stronger
  assurance and can be added later.
- Rate-limit registration and login attempts.
- Review accounts created before this fix. Legacy accounts intentionally remain
  active for deployment compatibility and are not automatically re-approved.

### CRITICAL-03: Known initial admin password

Evidence:

- `DatabaseSeeder` always creates `admin@jongerenkaart.be` with `Wachtwoord`.
- The credential is documented in `PREREQUISITES.md`.
- There is no forced password change or MFA requirement.

Impact:

A new or rebuilt environment can be taken over between deployment and the
manual password change. Admin access permits user management, arbitrary public
HTML, database export and full organisation management.

Required remediation:

- In production, require a strong one-time `ADMIN_INITIAL_PASSWORD` secret or
  generate a random bootstrap password and deliver it through an approved
  secure channel.
- Make the production seeder fail when that secret is absent.
- Mark bootstrap credentials as expired and force a password change.
- Remove the known password from production instructions.
- Require MFA for admins before production launch.

### CRITICAL-04: Vulnerable and obsolete internet-facing images

Evidence:

- Production uses `nginx:1.27-alpine`.
- The official nginx advisory list includes multiple 2025/2026 issues affecting
  versions newer than 1.27 and fixed in 1.30.4 or 1.31.3.
- Production uses `getmeili/meilisearch:v1.14`; the current release line is
  substantially newer and includes explicit security fixes.

Impact:

nginx still processes internet-originating requests forwarded by Caddy. Known
memory-safety and request-processing vulnerabilities in an obsolete branch create
avoidable remote risk. An old Meilisearch release expands the post-compromise
and availability risk.

Required remediation:

- Move nginx to a currently supported, patched stable release, at least one
  containing the fixes identified by the current nginx security advisory.
- Upgrade Meilisearch through its supported migration path. Back up
  `/meili_data`, test index compatibility and be prepared to rebuild the index
  from MongoDB.
- Rebuild PHP from the latest PHP 8.4 patch image.
- Pin tested production images by digest after the update.
- Add automatic ECR/Inspector scanning and a regular patch cadence.

### HIGH-01: HTTPS and proxy boundary

Status: **Mitigated for the documented single-host Compose deployment.**

Implemented controls:

- The pinned Caddy container is the only service publishing ports `80` and
  `443`.
- nginx is reachable only through the `edge` network.
- Laravel enables trusted forwarded headers when `TRUSTED_PROXIES` is set.
- Production Compose injects the secure-cookie setting, and the installer
  requires it for an HTTPS `APP_URL`.
- Caddy handles certificate renewal and HTTP-to-HTTPS redirects.

Residual gaps:

- `TRUSTED_PROXIES=*` is acceptable only while PHP-FPM and nginx remain
  inaccessible from untrusted networks.
- HSTS is absent.
- An AWS ALB deployment needs its own explicit proxy/security-group boundary
  instead of blindly combining ALB and Caddy behavior.

Impact:

An incorrect AWS deployment can expose credentials and sessions over HTTP.
Without trusted proxy handling, Laravel may treat ALB-forwarded HTTPS requests
as HTTP and issue incorrect URLs or non-secure cookies.

Required follow-up:

- Terminate TLS on an AWS Application Load Balancer with an ACM certificate.
- Redirect all port 80 requests to 443 at the ALB.
- Allow the instance security group to receive HTTP only from the ALB security
  group.
- Configure Laravel to trust only the expected proxy boundary and forwarded
  headers.
- Set `APP_URL=https://...`, `SESSION_SECURE_COOKIE=true`,
  `SESSION_HTTP_ONLY=true` and `SESSION_SAME_SITE=lax`.
- Add HSTS only after HTTPS works on all intended hostnames.

### HIGH-02: No login, registration or expensive-endpoint rate limiting

Evidence:

- Login and registration call controllers directly without `throttle`
  middleware or a custom `RateLimiter`.
- Search and map endpoints are public and can be reactive.
- Map requests load and process all organisations with coordinates.
- Registration scans all organisations to find an email.

Impact:

- Password guessing and credential stuffing are unrestricted.
- Account enumeration and registration abuse are easier.
- Search, map and analytics writes can be used for application-level denial of
  service.

Required remediation:

- Add Laravel named limiters for login, registration, search, map and API
  routes.
- Key login limits by normalized email plus client IP.
- Use a shared atomic limiter store such as Redis for more than one app
  instance.
- Add AWS WAF managed rules and rate-based rules in front of the ALB.
- Keep the reactive-search admin switch as an operational fallback, not as the
  primary abuse control.

### HIGH-03: MongoDB has no authentication

Evidence:

- The MongoDB URI is `mongodb://mongodb:27017/jongerenkaart`.
- No Mongo root or application credentials are configured.
- All containers share one Docker network.

Positive control:

- Port 27017 is not published by the production Compose file.

Impact:

Any process that reaches the Docker network can fully read and modify MongoDB.
An nginx, application or neighbouring-container compromise therefore gives
immediate database access.

Required remediation:

- Enable MongoDB access control.
- Create a least-privileged application user for the `jongerenkaart` database
  and separate administrative credentials.
- Store credentials in AWS Secrets Manager, not in Git or a world-readable env
  file.
- Use separate frontend and backend Docker networks so nginx cannot directly
  reach MongoDB or Meilisearch.
- If MongoDB moves off-host, require TLS and restrict its security group to the
  application tier.

### HIGH-04: Meilisearch master-key fallback and over-privileged application key

Evidence:

- `MEILISEARCH_KEY` falls back to the literal `change-me-in-production`.
- Laravel uses the same key as `MEILI_MASTER_KEY`.
- The application needs administrative search access for rebuilds, so a
  compromise exposes the full Meilisearch control plane.

Required remediation:

- Make `MEILISEARCH_KEY` required with Compose `${VAR:?message}` syntax.
- Generate a high-entropy master key and store it in Secrets Manager.
- Use a restricted search key for read-only search operations.
- Isolate index administration into an admin/maintenance path with a separate
  key where practical.
- Keep port 7700 private and firewall-restricted.

### HIGH-05: Stored cross-site scripting through editable content

Evidence:

- Admins can save arbitrary HTML.
- Info, FAQ and privacy pages render it with `{!! $contentPage->html !!}`.
- No HTML sanitizer or Content Security Policy is present.

Impact:

A compromised admin account, malicious insider or unsafe pasted HTML can run
JavaScript for every visitor. If an admin subsequently views the page, the
script runs with that admin's same-origin privileges and can perform
authenticated actions.

Required remediation:

- Sanitize saved HTML with a maintained allowlist-based HTML sanitizer.
- Disallow scripts, event attributes, unsafe URL schemes, iframes and embedded
  forms unless explicitly required.
- Prefer structured content blocks over arbitrary HTML.
- Introduce a restrictive CSP, initially in report-only mode.
- Log content changes with actor, timestamp and before/after information.

### HIGH-06: Organisation access is not reliably revoked

Evidence:

- Registration stores organisation IDs permanently in `user.organisatie_ids`.
- Management authorization accepts either the stored ID or a current matching
  email address.
- Removing an email from an organisation does not remove the stored ID.

Impact:

A former contact person can retain edit access after their contact address is
removed.

Required remediation:

- Introduce an explicit organisation-membership collection with status,
  granted-by, granted-at and revoked-at fields.
- Authorize only active membership records.
- Revoke memberships when contact responsibility changes.
- Add an admin view for reviewing and revoking access.
- Invalidate active sessions after role or membership revocation.

### HIGH-07: The organisation dataset is tracked in Git

Evidence:

- `inventaris_sociale_kaart.organisaties.json` is tracked by Git.
- The dataset contains contact-person names and email addresses.
- `.dockerignore` keeps it out of the image but does not remove it from Git
  history.

Impact:

Repository readers, forks, CI artifacts and historical clones may retain all
dataset revisions. Deleting the current file does not delete Git history.

Required remediation:

- Decide formally whether every field is approved for source-control
  distribution.
- If not, remove the file from current Git and rewrite repository history using
  an approved process.
- Rotate/revoke any sensitive values found in historical versions.
- Store imports in an encrypted, access-logged S3 bucket or another approved
  data-transfer channel.
- Fetch the file temporarily during controlled deployment and delete it after
  import.

### HIGH-08: No complete automated backup and restore process

Evidence:

- The admin export includes organisations only.
- Users, memberships, settings, editable pages and analytics are not included.
- MongoDB and Meilisearch use host bind mounts.
- No automated, encrypted off-host backup is defined in Compose.

Impact:

Disk loss, operator error, ransomware or instance termination can cause
permanent data and authorization loss.

Required remediation:

- Put database data on encrypted EBS storage.
- Use AWS Backup/EBS snapshots and an encrypted backup vault with retention.
- Create logical MongoDB backups of all required collections and encrypt them
  before storing them in a private versioned S3 bucket.
- Rebuild Meilisearch from MongoDB rather than treating it as the only search
  backup.
- Define RPO/RTO targets and test restoration at least quarterly.
- Restrict backup deletion and consider cross-account or vault-lock protection.

### HIGH-09: AWS network and host architecture is not yet defined

Deploying the current Compose stack directly on a public EC2 IP would leave too
much responsibility on one host.

Minimum acceptable single-instance architecture:

```text
Internet
  -> Route 53
  -> AWS WAF
  -> public ALB, HTTPS 443 with ACM
  -> private EC2 security group, HTTP allowed only from ALB
  -> Docker nginx
  -> Laravel
  -> private MongoDB and Meilisearch containers
  -> encrypted EBS data volume
```

Host controls:

- Place EC2 in a private subnet without a public IPv4 address.
- Use Systems Manager Session Manager instead of public SSH.
- If SSH is temporarily unavoidable, restrict port 22 to fixed administrator
  addresses and require keys.
- Require IMDSv2.
- Attach a least-privileged EC2 instance role; do not place AWS access keys on
  disk.
- Patch Ubuntu and Docker automatically within an agreed maintenance window.
- Keep UFW as host-level defence in depth, while treating security groups as
  the primary AWS boundary.

For higher availability, move toward stateless app instances across two
Availability Zones behind the ALB. This requires shared sessions/cache in
ElastiCache Redis, immutable public assets in the image or S3/CloudFront, and a
separately operated MongoDB/Meilisearch persistence strategy. Amazon DocumentDB
must not be treated as a drop-in replacement without application compatibility
testing; MongoDB Atlas on AWS or a dedicated private MongoDB deployment may be
more predictable.

### HIGH-10: Portainer controls the Docker host

Evidence:

- Portainer mounts `/var/run/docker.sock`.
- A Portainer administrator can create privileged containers, mount host paths
  and otherwise gain effective root control over the Docker host.

Implemented controls:

- Portainer binds only to `127.0.0.1:9443`.
- Port `8000` is not published.
- Portainer is not routed through Caddy and is attached to a separate
  management network.
- Documentation requires an SSH tunnel and a strong, unique administrator
  password.

Operational requirements:

- Never publish Portainer on `0.0.0.0`, add it to Caddy, or open `9443` in UFW
  or the cloud security group.
- Patch the pinned Portainer image deliberately and review release notes.
- Back up and protect `data/portainer`.
- Treat Portainer credentials and sessions as server-root credentials.
- Prefer AWS Systems Manager Session Manager over an internet-accessible SSH
  tunnel when the production deployment moves to AWS.

### MEDIUM-01: Public API responses expose excessive fields

The API list and detail methods serialize raw model attributes, including
contact people and administrative/source fields that are not needed by normal
search results.

Remediation:

- Define separate public and administrative API Resources.
- Expose only fields with a documented public purpose.
- Paginate and rate-limit all collection endpoints.
- Perform a privacy review of `sociale_kaart_id`, `login_email`,
  `administratieveGegevens` and contact-person fields.

### MEDIUM-02: Weak password policy and no MFA

Passwords only require eight characters. Compromised admin credentials provide
full control.

Remediation:

- Use Laravel's stronger password rule, including a longer minimum and
  compromised-password check where operationally possible.
- Require MFA for admins and strongly encourage it for contact people.
- Require recent password confirmation for exports, admin creation, password
  resets, content HTML changes and destructive maintenance.
- Provide secure password-reset/invitation flows instead of admins sharing
  passwords.

### MEDIUM-03: No immutable application audit log

Organisation changes record only aggregate counts. There is no durable record
of actor, organisation, action and changed fields. User and settings changes
are also not fully audited.

Remediation:

- Add an append-only audit event for authentication, authorization changes,
  organisation CRUD, exports, content edits, settings, rebuilds and user
  administration.
- Record actor ID, action, target ID, timestamp, outcome and a safe change
  summary.
- Do not log passwords, tokens, full session IDs or unnecessary personal data.
- Ship security logs off-host to CloudWatch Logs with alarms and retention.

### MEDIUM-04: Container hardening is limited

Evidence:

- The app container starts as root.
- The entrypoint applies `chmod -R 777` to writable directories.
- Containers retain default Linux capabilities and writable root filesystems.
- nginx forwards every `*.php` file under the public bind mount to PHP-FPM.
- `client_max_body_size` is 64 MB although no public upload feature requires it.

Remediation:

- Run application processes as an unprivileged user and use narrow `750/770`
  permissions.
- Remove unnecessary capabilities, enable `no-new-privileges`, and use
  read-only filesystems with explicit writable mounts where feasible.
- Configure nginx to execute only `/index.php`; reject every other PHP path.
- Reduce request body limits to the smallest value required.
- Add health checks and resource limits.

### MEDIUM-05: Security headers and third-party browser assets

Positive controls:

- nginx sends `X-Frame-Options` and `X-Content-Type-Options`.

Gaps:

- Headers do not use nginx's `always` parameter.
- No CSP, Referrer-Policy or Permissions-Policy is set.
- Bootstrap and Font Awesome are loaded from CDNs without Subresource
  Integrity.
- Leaflet is also fetched at runtime from a third-party CDN.
- Extensive inline CSS and JavaScript make a strong CSP harder.

Remediation:

- Self-host pinned frontend dependencies through the build.
- Alternatively add valid SRI hashes and `crossorigin` attributes.
- Add headers on all responses, then introduce CSP in report-only mode and
  migrate inline code to nonce/hash-compatible assets.
- Use `Referrer-Policy: strict-origin-when-cross-origin` and a minimal
  Permissions-Policy.

### MEDIUM-06: Analytics retention is undefined

The visitor cookie lasts one year. Hashed visitor and authenticated activity
records have no TTL or documented deletion schedule. Hashing makes the
identifier pseudonymous rather than automatically anonymous.

Remediation:

- Define and document retention periods.
- Add TTL cleanup for visitor-level records and retain only aggregated
  statistics longer term.
- Provide a way to respect applicable consent and deletion requirements.
- Verify that the public privacy text accurately describes the custom tracker.

### MEDIUM-07: File sessions and cache prevent safe horizontal scaling

Production uses local file sessions and cache. With multiple AWS app instances,
users can lose sessions and rate limits/caches become inconsistent.

Remediation:

- Use a private, encrypted ElastiCache Redis deployment for sessions, cache and
  rate limiting before adding multiple app instances.
- Use TLS and authentication for Redis.
- Do not rely on ALB stickiness as the long-term security or consistency model.

### MEDIUM-08: Missing database uniqueness and membership integrity controls

Application validation checks that user emails are unique, but no reviewed
MongoDB unique-index migration is present. Concurrent requests may bypass
application-only uniqueness checks.

Remediation:

- Create and test a unique normalized index on `users.email`.
- Add indexes and constraints for membership and content/settings keys.
- Normalize emails consistently before both validation and persistence.

### LOW-01: Test endpoints are deployed publicly

`/tests`, `/tests/search` and `/tests/filter-options` are present in production
routes. They provide unnecessary reconnaissance and additional code paths.

Remediation:

- Register them only in local/testing environments or remove them.
- Once the closure route is removed, enable route caching in production.

### LOW-02: Error and operational information can be improved

Admin maintenance pages display raw exception messages. They are admin-only,
but can expose internal hostnames and implementation details to a compromised
or low-trust admin.

Remediation:

- Show a correlation ID and safe message in the UI.
- Send detailed exceptions to protected logs.

## AWS Deployment Security Baseline

Before launch, define the infrastructure as code using Terraform, AWS CDK or
CloudFormation. At minimum:

### Account and IAM

- Protect the AWS root user with hardware-backed MFA and do not use it for
  routine work.
- Use IAM Identity Center for human access.
- Use least-privileged roles for deployment and EC2/ECS.
- Use temporary role credentials rather than long-lived access keys.
- Require IMDSv2 on EC2.
- Separate production from development in different AWS accounts where
  practical.

### Network

- Public subnets contain only the ALB.
- Application and data services use private subnets/security groups.
- ALB accepts 443 from the internet and redirects 80 to 443.
- EC2 accepts application traffic only from the ALB security group.
- MongoDB and Meilisearch accept traffic only from the application security
  group or remain on a private local Docker network.
- Administrative access uses Session Manager.

### Secrets and encryption

- Store `APP_KEY`, Meilisearch keys, database credentials and backup keys in
  AWS Secrets Manager.
- Encrypt EBS, backup vaults, snapshots, S3 buckets and log buckets with KMS.
- Block S3 public access.
- Use TLS for every cross-host service connection.
- Establish a tested key-rotation procedure. Rotating `APP_KEY` invalidates
  sessions and can affect encrypted application data, so it requires a planned
  runbook.

### Detection and operations

- Enable a multi-Region CloudTrail trail with log-file validation.
- Enable GuardDuty and Security Hub.
- Enable Amazon Inspector for EC2 and ECR continuous scanning.
- Send ALB/nginx/Laravel/system logs to CloudWatch with alarms for:
  - elevated 4xx/5xx rates;
  - repeated failed logins;
  - admin-account and membership changes;
  - MongoDB/Meilisearch restarts;
  - disk, memory and CPU pressure;
  - backup failures;
  - WAF blocks and GuardDuty findings.
- Configure time synchronization and a documented incident-response process.

### Availability and recovery

- Define RPO and RTO.
- Automate encrypted backups with retention and off-host copies.
- Test a complete restore, including users and settings, before launch.
- Monitor disk growth for MongoDB, Meilisearch, Laravel logs and Docker images.
- Rebuild Meilisearch from MongoDB after recovery and verify document counts.

## Verification Plan

Security fixes should be backed by automated checks:

- Feature tests for anonymous API denial.
- Authorization tests for admin, assigned contact person, revoked contact
  person and unrelated contact person.
- Registration tests proving unverified addresses receive no access.
- Login and registration throttling tests.
- HTML sanitizer tests with script tags, event handlers, unsafe URLs and
  malformed markup.
- Header tests for HTTPS, secure cookies, HSTS, CSP and framing.
- Backup restoration test in an isolated environment.
- Container image scan and dependency audit in CI.
- External staging scan through the same ALB/WAF path as production.

`composer validate --strict` passed during this review. A live
`composer audit`/`npm audit` advisory lookup could not be completed in this
environment, so CI must run both against the lockfiles and fail on applicable
high/critical findings. Container scans must cover both OS and language
packages.

## Reference Baseline

- [AWS Well-Architected: store and use secrets securely](https://docs.aws.amazon.com/wellarchitected/latest/security-pillar/sec_identities_secrets.html)
- [AWS ALB infrastructure security](https://docs.aws.amazon.com/elasticloadbalancing/latest/userguide/infrastructure-security.html)
- [AWS ALB HTTPS listeners](https://docs.aws.amazon.com/elasticloadbalancing/latest/application/create-https-listener.html)
- [AWS WAF rate-based rules](https://docs.aws.amazon.com/waf/latest/developerguide/waf-rule-statement-type-rate-based.html)
- [AWS Systems Manager Session Manager](https://docs.aws.amazon.com/systems-manager/latest/userguide/session-manager.html)
- [AWS EC2 IMDSv2](https://docs.aws.amazon.com/AWSEC2/latest/UserGuide/configuring-instance-metadata-service.html)
- [AWS IAM roles for EC2](https://docs.aws.amazon.com/IAM/latest/UserGuide/id_roles_use_switch-role-ec2.html)
- [AWS EBS encryption](https://docs.aws.amazon.com/ebs/latest/userguide/ebs-encryption.html)
- [AWS Backup restore testing](https://docs.aws.amazon.com/aws-backup/latest/devguide/restore-testing.html)
- [AWS GuardDuty setup](https://docs.aws.amazon.com/guardduty/latest/ug/guardduty_settingup.html)
- [AWS Inspector overview](https://docs.aws.amazon.com/inspector/latest/user/what-is-inspector.html)
- [AWS CloudTrail multi-Region trails](https://docs.aws.amazon.com/awscloudtrail/latest/userguide/cloudtrail-concepts.html)
- [Laravel authentication and login throttling](https://laravel.com/docs/13.x/authentication)
- [Laravel rate limiting](https://laravel.com/docs/13.x/rate-limiting)
- [MongoDB security](https://www.mongodb.com/docs/v8.0/security/)
- [Meilisearch self-hosting security](https://www.meilisearch.com/docs/resources/self_hosting/security/overview)
- [nginx security advisories](https://nginx.org/en/security_advisories.html)
- [PHP supported versions](https://www.php.net/supported-versions.php)

## Final Action Plan Ordered by Threat Level

### Critical: block public deployment

1. Remove the known production admin password, require a random bootstrap
   secret, force a first-login change and enable admin MFA.
2. Upgrade nginx to a currently patched branch before accepting internet
   traffic. Back up and upgrade Meilisearch through its supported migration
   path.

### High: complete before production launch

3. Put AWS WAF and an HTTPS ALB with ACM in front of private EC2/app instances;
   redirect HTTP to HTTPS and configure trusted proxies and secure cookies.
4. Add login, registration, search, map and API rate limits using a shared
   atomic store; add WAF rate-based rules.
5. Enable MongoDB authentication with least-privileged users and require a
   non-default Meilisearch secret stored in AWS Secrets Manager.
6. Sanitize administrator-authored HTML and deploy a CSP migration plan.
7. Replace permanent `organisatie_ids` authorization with revocable,
   auditable membership records and invalidate sessions on revocation.
8. Remove the organisation dataset from Git if it is not explicitly approved
    for source control, clean history, and move deployment imports to encrypted
    restricted storage.
9. Implement encrypted full backups, off-host retention and a successful
    restore test covering users, settings, content and organisations.
10. Deploy EC2 privately with Session Manager, IMDSv2, least-privileged IAM,
    encrypted EBS and security-group isolation.
11. Keep Portainer loopback-only, access it through SSH or Session Manager,
    use a unique strong administrator password, and include its state in
    protected backups.

### Medium: complete immediately after blockers, preferably before launch

12. Minimize API responses and perform a personal-data exposure review.
13. Strengthen password rules, require recent password confirmation for
    sensitive admin actions and provide secure reset flows.
14. Add an off-host application audit log and CloudWatch security alarms.
15. Harden containers: unprivileged users, narrow permissions, separate
    networks, reduced capabilities, only `/index.php` executable, smaller body
    limits and health/resource controls.
16. Self-host browser dependencies or add SRI, and deploy security headers
    including CSP, Referrer-Policy and Permissions-Policy.
17. Define analytics retention and TTL cleanup.
18. Move sessions, cache and rate limits to protected Redis before scaling
    beyond one app instance.
19. Add MongoDB unique indexes for normalized user emails and integrity indexes
    for memberships/settings.
20. Add dependency, secret, SAST and container-image scanning to CI, with
    release failure on applicable high/critical findings.

### Low: planned hardening

21. Remove production `/tests*` routes and enable route caching.
22. Replace raw admin exception text with correlation IDs and protected logs.
23. Review this assessment after every major authentication, deployment,
    database or AWS architecture change, and commission an external penetration
    test before handling materially more sensitive data.
