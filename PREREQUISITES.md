# Setup And Maintenance

This is the installation and operations guide for Jongerenkaart. For website behaviour, see [README.md](README.md). For architecture, see [DESIGN.md](DESIGN.md).

## Requirements

Install Docker Desktop or Docker Engine with Docker Compose support.

Verify the installation:

```bash
docker --version
docker compose version
```

If the machine only has the older Compose binary, replace `docker compose` in this document with `docker-compose`.

The repository contains the Laravel application in `src/`. Run Docker commands from the repository root.

## Scaleway Ubuntu Server And Docker Setup

Use this section for a new Scaleway production server. If the Instance already exists, verify its image, Flexible IP, and Security Group before continuing with the SSH commands.

### 1. Create The Scaleway Instance

In the Scaleway console:

1. Upload the administrator's public SSH key under the project's SSH keys.
2. Open **Compute > CPU & GPU Instances** and create an Instance.
3. Select **Ubuntu 24.04 LTS (Noble)**.
4. For the default workload described in this guide, choose a production-suitable Instance with at least 4 vCPU, 8 GB RAM, and a 40 GB persistent SSD/block volume.
5. Keep or attach a public **Flexible IPv4**. Scaleway normally creates one with a new Instance. Retain this address so DNS does not need to change when the Instance is stopped or replaced.
6. Record the public IPv4 address shown in the console.

The Scaleway image installs the uploaded SSH key for the initial `root` login. Their official documentation covers [creating an Instance](https://www.scaleway.com/en/docs/instances/how-to/create-an-instance/), [SSH keys](https://www.scaleway.com/en/docs/organizations-and-projects/how-to/create-ssh-key/), and [Flexible IPs](https://www.scaleway.com/en/docs/instances/how-to/use-flexips/).

### 2. Configure The Scaleway Security Group

Create a dedicated **stateful** Security Group in the same Availability Zone and attach the Instance to it. Do not rely on the automatically created first Security Group without checking it: Scaleway documents that this group can be stateless.

Configure:

- default inbound policy: `Drop`;
- default outbound policy: `Accept`;
- stateful rules: enabled;
- inbound `Accept`, TCP `22`, source `YOUR_PUBLIC_IP/32`;
- inbound `Accept`, TCP `80`, source `0.0.0.0/0`;
- inbound `Accept`, TCP `443`, source `0.0.0.0/0`.

Use the public IP of the computer or office that administers the server for `YOUR_PUBLIC_IP`. Add IPv6 rules for `::/0` on ports `80` and `443` only when a working Flexible IPv6 is attached and published in DNS. Add an IPv6 SSH source only for the administrator's own IPv6 range.

Do **not** create public rules for:

```text
2375/2376  Docker daemon
27017      MongoDB
7700       Meilisearch
8000       Artisan development server
8080       Internal nginx endpoint behind Caddy
9000       PHP-FPM
```

Scaleway Security Groups filter public Instance traffic. Keep the application database and search engine on the private Docker network as a second boundary. See Scaleway's [Security Group guide](https://www.scaleway.com/en/docs/instances/how-to/use-security-groups/).

### 3. Create A Deployment User

Connect from the local computer using the Flexible IPv4:

```bash
ssh root@SCW_PUBLIC_IP
```

On Windows PowerShell, the same `ssh` command works when the OpenSSH client is installed.

Create a named deployment user and copy the Scaleway-provided SSH authorisation:

```bash
adduser --disabled-password --gecos "" deploy
usermod -aG sudo deploy

install -d -m 700 -o deploy -g deploy /home/deploy/.ssh
cp /root/.ssh/authorized_keys /home/deploy/.ssh/authorized_keys
chown deploy:deploy /home/deploy/.ssh/authorized_keys
chmod 600 /home/deploy/.ssh/authorized_keys

printf 'deploy ALL=(ALL) NOPASSWD:ALL\n' > /etc/sudoers.d/deploy
chmod 440 /etc/sudoers.d/deploy
visudo -cf /etc/sudoers.d/deploy
```

Keep the first root session open and test the new account from a second terminal:

```bash
ssh deploy@SCW_PUBLIC_IP
sudo whoami
```

`sudo whoami` must print `root`. Only close the first session after this succeeds. The `deploy` account and later Docker-group membership both provide administrative control and must only be used by trusted maintainers.

### 4. Configure The Ubuntu UFW Firewall

Use UFW on the Instance as the host firewall, in addition to the Scaleway Security Group. Keep the working root SSH session open while configuring and testing it so a mistake does not immediately lock out all administrative access.

From the tested `deploy` session, install UFW:

```bash
sudo apt-get update
sudo apt-get install -y ufw
```

Set restrictive defaults:

```bash
sudo ufw default deny incoming
sudo ufw default allow outgoing
```

Allow SSH from the administrator's fixed public IPv4 address. Replace the example CIDR before running the command:

```bash
ADMIN_CIDR="203.0.113.25/32"
sudo ufw allow from "$ADMIN_CIDR" to any port 22 proto tcp
```

When the administrator does not have a stable public IP, use rate-limited public SSH instead of the preceding source-restricted rule:

```bash
sudo ufw limit 22/tcp
```

Use only one of these SSH approaches. Restricting SSH to a known `/32` address is preferred.

Allow the public web ports used by Caddy:

```bash
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
```

Review the rules before enabling the firewall:

```bash
sudo ufw show added
```

The output must contain an SSH rule that matches the current administrator connection. Then enable UFW:

```bash
sudo ufw enable
sudo ufw status verbose
sudo ufw status numbered
```

Open a third terminal and verify that a new connection still works:

```bash
ssh deploy@SCW_PUBLIC_IP
```

Only close the earlier sessions after this succeeds. If an incorrect UFW rule blocks SSH, use Scaleway's web console or rescue access to repair the rules.

UFW should expose only:

```text
22/tcp   SSH, preferably from the administrator IP only
80/tcp   HTTP redirect and certificate validation
443/tcp  HTTPS
```

Do not add UFW rules for MongoDB, Meilisearch, PHP-FPM, Artisan, Docker, or the loopback nginx port.

### 5. Install Docker Engine And Compose

Run the following commands while connected as `deploy`. These use Docker's current official Ubuntu APT repository instead of Ubuntu's `docker.io` package or the convenience installation script.

Remove conflicting packages. On a clean Scaleway Instance, it is normal for none of them to be installed:

```bash
for package in docker.io docker-compose docker-compose-v2 docker-doc podman-docker containerd runc; do
    sudo apt-get remove -y "$package"
done
```

Install repository prerequisites and Docker's signing key:

```bash
sudo apt-get update
sudo apt-get upgrade -y
sudo apt-get install -y ca-certificates curl

sudo install -m 0755 -d /etc/apt/keyrings
sudo curl -fsSL https://download.docker.com/linux/ubuntu/gpg \
    -o /etc/apt/keyrings/docker.asc
sudo chmod a+r /etc/apt/keyrings/docker.asc
```

Add Docker's stable repository:

```bash
sudo tee /etc/apt/sources.list.d/docker.sources > /dev/null <<EOF
Types: deb
URIs: https://download.docker.com/linux/ubuntu
Suites: $(. /etc/os-release && echo "${UBUNTU_CODENAME:-$VERSION_CODENAME}")
Components: stable
Architectures: $(dpkg --print-architecture)
Signed-By: /etc/apt/keyrings/docker.asc
EOF

sudo apt-get update
```

Install Docker Engine, Buildx, and the modern `docker compose` plugin:

```bash
sudo apt-get install -y \
    docker-ce \
    docker-ce-cli \
    containerd.io \
    docker-buildx-plugin \
    docker-compose-plugin
```

Enable both services at boot:

```bash
sudo systemctl enable --now docker.service
sudo systemctl enable --now containerd.service
sudo systemctl --no-pager --full status docker.service
```

The installation follows Docker's official [Ubuntu installation guide](https://docs.docker.com/engine/install/ubuntu/).

### 6. Configure Docker Access And Log Rotation

Add only the trusted deployment user to the Docker group:

```bash
sudo usermod -aG docker deploy
```

Configure Docker's `local` logging driver before creating the production containers. It rotates and compresses container logs, which helps protect the 40 GB server disk:

```bash
sudo install -d -m 0755 /etc/docker
sudo tee /etc/docker/daemon.json > /dev/null <<'EOF'
{
  "log-driver": "local",
  "log-opts": {
    "max-size": "20m",
    "max-file": "5"
  }
}
EOF

sudo systemctl restart docker
```

Disconnect and reconnect so the Docker group membership becomes active:

```bash
exit
ssh deploy@SCW_PUBLIC_IP
```

Verify the complete installation without `sudo`:

```bash
docker version
docker compose version
docker buildx version
docker run --rm hello-world
docker info --format 'Logging driver: {{.LoggingDriver}}'
```

The last line must show `Logging driver: local`. Membership of the `docker` group is effectively root-level access; never expose `/var/run/docker.sock` or a Docker TCP daemon publicly. Docker's [post-installation guide](https://docs.docker.com/engine/install/linux-postinstall/) explains this access model and log rotation.

Check whether the earlier Ubuntu upgrade requires a reboot:

```bash
if [ -f /var/run/reboot-required ]; then
    cat /var/run/reboot-required
else
    echo "No reboot required"
fi
```

When a reboot is required, perform it before deployment and reconnect using the same Flexible IP:

```bash
sudo reboot
```

```bash
ssh deploy@SCW_PUBLIC_IP
docker version
```

### 7. Prepare The Application Paths

Create the paths used by the deployment instructions:

```bash
sudo mkdir -p /opt/jongerenkaart
sudo mkdir -p /etc/jongerenkaart
sudo mkdir -p /var/backups/jongerenkaart
sudo chown -R deploy:deploy /opt/jongerenkaart
sudo chown deploy:deploy /var/backups/jongerenkaart
```

Upload or clone the project into `/opt/jongerenkaart`. Store the protected production environment at `/etc/jongerenkaart/production.env` and MongoDB backups under `/var/backups/jongerenkaart`, not inside the Git repository.

For production, run only `compose.prod.yaml`. The development Compose file publishes MongoDB, Meilisearch, Artisan, and nginx testing ports and must not be started on the public Scaleway Instance.

Docker-published ports can bypass UFW rules on Ubuntu. The Scaleway Security Group is therefore an important public perimeter. Production publishes only Caddy on public ports `80` and `443`; Portainer binds to host loopback on `127.0.0.1:9443`. nginx, PHP-FPM, MongoDB and Meilisearch have no host port. Do not disable Docker's firewall rules; Docker documents that doing so can break container networking. See Docker's [firewall guidance](https://docs.docker.com/engine/network/packet-filtering-firewalls/).

After starting the production stack, inspect every published Docker port:

```bash
docker ps --format 'table {{.Names}}\t{{.Ports}}'
sudo ufw status verbose
```

Caddy may show public bindings for `80/tcp` and `443/tcp`. Portainer may show `127.0.0.1:9443->9443/tcp`; it must never show `0.0.0.0:9443` or `[::]:9443`. nginx, MongoDB, Meilisearch, PHP-FPM, Artisan and the Portainer tunnel port `8000` must not show public bindings. UFW does not reliably filter ports that Docker publishes on all host interfaces, so correct Compose bindings and the Scaleway Security Group remain required even though UFW is enabled.

## Docker Services

The development stack in `compose.yaml` contains:

| Service | Purpose | Host access |
| --- | --- | --- |
| `app` | PHP 8.4 FPM, Composer, npm, and Artisan | Port `8000` for optional `artisan serve` |
| `nginx` | Normal development web entry point | `http://localhost:8080` |
| `mongodb` | MongoDB 8 source database | `mongodb://localhost:27018` |
| `meilisearch` | Meilisearch 1.14 index | `http://localhost:7700` |

MongoDB uses host port `27018` so it does not conflict with MongoDB on Windows at `localhost:27017`.

Persistent development data is stored in bind-mounted folders:

```text
data/mongodb
data/meilisearch/db
data/public-assets
```

The source code is mounted from `src/` into the development PHP and nginx containers. Composer's download cache uses the named volume `composer-cache`.

## Clean Development Setup

Use these steps after a fresh clone.

1. Create the Laravel environment file.

Linux or macOS:

```bash
cp .env.docker.example src/.env
```

Windows PowerShell:

```powershell
Copy-Item .env.docker.example src/.env
```

2. Build and start the containers.

```bash
docker compose up -d --build
```

3. Install PHP dependencies.

```bash
docker compose exec app composer install
```

4. Generate the Laravel application key.

```bash
docker compose exec app php artisan key:generate
```

5. Import the supplied MongoDB organisation backup.

```bash
docker compose exec mongodb sh /scripts/import-organisaties.sh
```

The script imports `inventaris_sociale_kaart.organisaties.json` into `jongerenkaart.organisaties`. It uses `--drop` by default and therefore replaces the existing `organisaties` collection.

6. Clear configuration left from another environment.

```bash
docker compose exec app php artisan config:clear
```

7. Seed the initial admin user and default medewerkers.

```bash
docker compose exec app php artisan db:seed
```

The initial admin login is:

```text
email: admin@jongerenkaart.be
password: Wachtwoord
```

Change this password after the first login.

8. Open `http://localhost:8080/login`, sign in as the admin, open `Instellingen`, and run `Alles controleren en vernieuwen`.
9. Open `Statistieken` and click `Dashboard bijwerken` to create the first local traffic snapshot. This is optional during development and does not affect public features.

This final action builds the dropdown-option collection and cache, applies the configured Meilisearch settings, recreates the search index, and imports all organisations. Use this project action instead of `scout:sync-index-settings`; that Artisan command is not available in the installed Scout version.

## Daily Development

Start the stack:

```bash
docker compose up -d
```

Open the normal nginx-served application at `http://localhost:8080`.

Stop the stack without deleting local data:

```bash
docker compose down
```

Rebuild after changing a Dockerfile or container dependency:

```bash
docker compose up -d --build
```

Laravel's development server is optional. The `app` service exposes port `8000`, but Artisan must listen on all container interfaces:

```bash
docker compose exec app php artisan serve --host=0.0.0.0 --port=8000
```

Then open `http://localhost:8000`. Normal development can continue through nginx on port `8080` without this command.

## Common Commands

Laravel and tests:

```bash
docker compose exec app php artisan about
docker compose exec app php artisan route:list
docker compose exec app php artisan test
docker compose exec app php artisan config:clear
docker compose exec app php artisan view:clear
```

Composer:

```bash
docker compose exec app composer install
docker compose exec app composer require vendor/package
```

Frontend dependencies:

```bash
docker compose exec app npm install
docker compose exec app npm run dev
```

Container shell:

```bash
docker compose exec app sh
```

Logs:

```bash
docker compose logs -f
docker compose logs -f app
docker compose logs -f nginx
docker compose logs -f mongodb
docker compose logs -f meilisearch
```

## Environment Configuration

The development `.env` uses Docker service names, not host ports:

```env
APP_URL=http://localhost:8080
DB_CONNECTION=mongodb
MONGODB_URI=mongodb://mongodb:27017/jongerenkaart
MONGODB_DATABASE=jongerenkaart
SCOUT_DRIVER=meilisearch
MEILISEARCH_HOST=http://meilisearch:7700
MEILISEARCH_KEY=local-development-key
```

From the host machine, use port `27018` for MongoDB and port `7700` for Meilisearch. Containers use `mongodb:27017` and `meilisearch:7700` on the private Docker network.

## Importing MongoDB Data

Re-import the standard backup:

```bash
docker compose exec mongodb sh /scripts/import-organisaties.sh
```

Import into another database or collection:

```bash
docker compose exec mongodb sh -c "MONGO_DATABASE=other_db MONGO_COLLECTION=other_collection /scripts/import-organisaties.sh"
```

To import without dropping the target collection, override the drop flag:

```bash
docker compose exec mongodb sh -c "MONGO_IMPORT_DROP='' /scripts/import-organisaties.sh"
```

After any full import, log in as an admin and run `Alles controleren en vernieuwen` from `/beheer/instellingen`. A raw MongoDB import bypasses Laravel, so it cannot incrementally update filter counts or Meilisearch.

## Normal Organisation Maintenance

Creating, editing, or deleting an organisation through the application updates only that organisation in MongoDB and Meilisearch. It also increments and decrements only the affected filter-option counts. No full rebuild is required after normal application edits.

The same write records only one daily organisation-change counter for local statistics. The statistics dashboard is recalculated only when an admin presses `Dashboard bijwerken`; opening it simply reads the last saved snapshot. Daily traffic aggregates are retained for 400 days and inactive visitor records for 90 days. Review the public privacy/cookie text for the first-party visitor identifier before production deployment.

Use the admin settings maintenance actions as follows:

- `Database controleren`: read-only health and count check.
- `Dropdown opties opnieuw opbouwen`: full MongoDB scan to repair the option-count collection and cache.
- `Zoekindex vernieuwen`: recreate Meilisearch settings and documents from MongoDB.
- `Alles controleren en vernieuwen`: run the full repair path after an import or when several derived stores may be stale.
- `Backup downloaden`: stream all organisation documents as one JSON backup.

The full rebuild actions can take time on a large dataset. They are maintenance tools, not steps required after each edit.

## Backup And Restore

Admins can export the complete `organisaties` collection from `/beheer/instellingen`. Keep downloaded backups outside the repository data folders.

The included import script expects a JSON array. Before restoring, confirm that the intended file is mounted as `/backups/inventaris_sociale_kaart.organisaties.json`. The default restore drops the existing organisation collection.

MongoDB files in `data/mongodb` are database storage files, not a portable JSON backup. Use the admin JSON export or MongoDB's official backup tools for portable backups.

Also back up `data/caddy/data` and `data/portainer`. The first contains TLS private keys and ACME account state; the second contains Portainer users and configuration. Treat both as secrets. `data/caddy/config` can be preserved as well, but the version-controlled Caddyfile remains the authoritative configuration.

## Meilisearch Maintenance

Meilisearch is derived data. MongoDB remains the source of truth.

Use `Zoekindex vernieuwen` in the admin settings after:

- changing searchable or filterable fields;
- changing `src/config/scout.php`;
- restoring MongoDB outside Laravel;
- seeing search results that no longer match MongoDB.

If Meilisearch cannot read data created by another Meilisearch version, stop the stack, preserve anything needed from `data/meilisearch/db`, clear that directory, start the stack, and rebuild the index from admin settings. Do not remove `data/mongodb`; the search index can be recreated, but MongoDB is the source data.

## Choosing Production Server Resources

These are starting estimates for the current single-server design, not guarantees. Benchmark the production image before launch and resize from measured p95 latency, CPU, memory, swap, disk latency, and request queues.

The current repository contains approximately:

- 2,240 organisation documents;
- 5.9 MB of source JSON;
- 219 MB of MongoDB storage files;
- 40 MB of Meilisearch storage files.

Because the number of organisations is not expected to grow significantly, concurrent requests and index rebuilds determine the server size more than raw database capacity.

### Sizing Profiles

| Case | Expected use | CPU | RAM | Disk | Network |
| --- | --- | ---: | ---: | ---: | --- |
| Small/pilot | Up to 25 concurrent visitors and a few edits | 2 vCPU | 4 GB | 25 GB NVMe/SSD | 50 Mbps, at least 250 GB monthly transfer |
| Default | Up to 100 concurrent visitors, 20 concurrent organisation updates, and 3 active logins | **4 vCPU** | **8 GB** | **40 GB NVMe/SSD** | **100 Mbps**, preferably 500 GB to 1 TB monthly transfer |
| Busy | Roughly 250-500 concurrent visitors or frequent reactive/map searches | 8 vCPU | 16 GB | 80 GB NVMe/SSD | 250 Mbps or 1 Gbps, at least 2 TB monthly transfer |
| High availability | More than 500 concurrent visitors, strict uptime, or sustained heavy writes | At least two app nodes with 4 vCPU each, plus dedicated data/search resources | At least 8 GB per app node; size MongoDB and Meilisearch separately | 100+ GB managed SSD, preferably split by service, with snapshots | Load balancer and redundant 1 Gbps-capable networking |

Do not choose a one-vCPU production server. MongoDB recommends access to at least two real cores, and Meilisearch cannot use multi-threaded indexing on a single core. Avoid the cheapest CPU-credit-limited VM when traffic is sustained; depleted credits can create severe latency even when average CPU appears modest.

### Default Recommendation: 4 vCPU, 8 GB RAM, 40 GB NVMe

For the stated default workload, start with:

```text
4 modern vCPU
8 GB RAM
40 GB persistent NVMe/SSD
100 Mbps network port
500 GB to 1 TB monthly transfer
64-bit Linux with XFS or EXT4
```

Why this is the default:

- Four cores leave room for PHP requests while MongoDB or Meilisearch is active.
- Eight GB gives MongoDB, Meilisearch, PHP-FPM, nginx, Docker, and Linux enough shared headroom for this small dataset.
- NVMe/SSD keeps MongoDB journals/checkpoints, Docker image extraction, logs, and Meilisearch rebuilds responsive.
- Forty GB is still much larger than the live data, but leaves practical room for the operating system, Docker images and build layers, logs, temporary search rebuilds, and short-term backup staging.
- A 100 Mbps port is ample for dynamic HTML/JSON at this scale. External CDNs currently serve several frontend libraries and map tiles, so they do not all consume this server's bandwidth.

One hundred concurrent users means connected or active visitors, not necessarily 100 requests per second. If all visitors continuously trigger reactive search, CPU and request concurrency become the first likely limit. Disable reactive search from admin settings while investigating sustained load.

### RAM Planning

MongoDB WiredTiger uses both its internal cache and the operating-system filesystem cache. Its default internal cache is based on available memory. Meilisearch indexing is also memory intensive and can use a large share of detected RAM. On one shared host, leaving every container unlimited can allow one maintenance action to pressure all services.

For an 8 GB host, a reasonable initial memory budget is:

| Service | Starting budget |
| --- | ---: |
| `mongodb` | 2.5-3 GB |
| `meilisearch` | 1.5-2 GB |
| `app` / PHP-FPM | 1.5-2 GB |
| `nginx` | 128-256 MB |
| Linux, Docker, filesystem cache, and safety headroom | At least 1 GB |

Treat these as monitoring targets rather than blindly applying hard limits. Docker warns that an exhausted host may invoke the OOM killer. If limits are introduced, test full Meilisearch rebuilds and 20 concurrent edits before production. Keep swap small as emergency headroom, but do not rely on regular swapping for MongoDB performance.

PHP-FPM worker count must fit inside the app memory budget. Measure the real resident memory of a busy PHP worker, reserve framework/opcache overhead, and calculate `pm.max_children` from the remaining app memory. Start around 12-16 workers on this default host only after measurement; too many workers increase memory pressure and database/search concurrency without increasing useful throughput.

### CPU Planning

CPU demand comes mainly from PHP request handling, map distance calculations, MongoDB work, and Meilisearch indexing/search. Four vCPU should handle the default dataset and visitor target, but a full index rebuild can temporarily compete with public searches.

Schedule full rebuilds outside peak use. If public searches become slow during maintenance, limit Meilisearch indexing threads/memory or move Meilisearch to a separate host. Add CPU when PHP-FPM queues grow while RAM and disk remain healthy.

### Disk Planning

Use persistent NVMe or SSD, not ephemeral instance storage. MongoDB recommends XFS for data-bearing production nodes and supports EXT4. Keep database storage separate from disposable container layers where the hosting platform allows it.

Meilisearch recommends starting with disk capacity of at least ten times the raw indexed dataset. That minimum is tiny for the current data, but production disk must also hold:

- MongoDB data and journal;
- Meilisearch data and temporary rebuild space;
- Docker images and old layers during deployment;
- application and container logs;
- public assets;
- local backup staging.

The live MongoDB and Meilisearch data currently use well below 1 GB. On a 40 GB server, a realistic rough allocation is 8-12 GB for Linux and system packages, 5-10 GB for Docker images/build cache, less than 2 GB for current application data and rebuild space, and at least 10 GB of free operational headroom.

Alert at 70% disk use and investigate before 80%. Store long-term or multiple-generation backups off-server. If many backups, verbose logs, monitoring data, or old Docker images must remain on the same disk, choose 80 GB instead of 40 GB.

### Network Planning

Use a provider with stable low-latency networking and at least a 100 Mbps port for the default case. Prefer a plan with 500 GB to 1 TB monthly transfer so normal traffic, image pulls, backups, and monitoring have comfortable headroom.

Only nginx should receive public application traffic. Keep MongoDB and Meilisearch on the private Docker network. When using external MongoDB, Meilisearch, backups, or a load balancer, prefer private networking in the same region to reduce latency and transfer cost.

The website currently depends on external CDNs and OpenStreetMap tile servers. For stricter reliability or high traffic, host/version static assets appropriately and use a tile provider whose usage policy fits production traffic.

### When To Resize Or Split Services

Move from the default profile when any of these persist during normal traffic:

- CPU above 70% for several minutes;
- available RAM below 15% or any sustained swap activity;
- PHP-FPM queueing or p95 dynamic-page latency above the chosen service target;
- MongoDB queued operations or elevated disk latency;
- Meilisearch searches slowing significantly during updates;
- disk above 70%;
- network transfer or port utilisation approaching the provider limit.

The first upgrade is usually 8 vCPU and 16 GB RAM. If uptime matters more than raw capacity, separate MongoDB and Meilisearch or use managed services before simply enlarging one server. Multiple app nodes require a shared session/cache store because the current file-based session and cache drivers are local to one app container.

Official background for these recommendations:

- [MongoDB production notes](https://www.mongodb.com/docs/manual/administration/production-notes/)
- [MongoDB WiredTiger](https://www.mongodb.com/docs/manual/core/wiredtiger/)
- [Meilisearch hardware FAQ](https://www.meilisearch.com/docs/resources/help/faq)
- [Meilisearch RAM and multithreading](https://www.meilisearch.com/docs/resources/self_hosting/performance/ram_multithreading)
- [Docker resource constraints](https://docs.docker.com/engine/containers/resource_constraints/)

## Production Deployment

`compose.prod.yaml` builds Composer dependencies and frontend assets into a production PHP image. The Caddy container is the only public web entry point. It reaches nginx through the `edge` network; nginx reaches PHP through the `backend` network. MongoDB and Meilisearch remain on `backend` and are not published. Portainer is isolated on `management` and its UI binds only to host loopback.

### 1. Prepare The Server

Prepare a Linux server with:

- Docker Engine and Docker Compose;
- enough persistent disk space for MongoDB, backups, and Meilisearch;
- DNS pointing the intended domain to the server;
- inbound HTTP/HTTPS access through the firewall;
- a TLS-capable reverse proxy or load balancer for HTTPS.

Clone or securely copy the repository to a dedicated application directory. Run all following commands from that directory.

Create the persistent directories before the first start:

```bash
mkdir -p \
  data/mongodb \
  data/meilisearch/db \
  data/public-assets \
  data/caddy/data \
  data/caddy/config \
  data/portainer
```

Do not put database or secret files in Git.

### 2. Configure Production Secrets

Generate a unique 32-byte application key and store it in Laravel's
`base64:...` format. On the production server, this command creates a complete
valid value without inserting a newline into the key:

```bash
APP_KEY_VALUE="base64:$(openssl rand -base64 32 | tr -d '\n')"
```

Store production values in the host's secret manager or in a root-readable environment file outside the repository, for example `/etc/jongerenkaart/production.env`:

```env
APP_KEY=base64:replace-with-the-generated-value
APP_URL=https://jongerenkaart.example
SESSION_LIFETIME=60
SESSION_ABSOLUTE_LIFETIME=60
MEILISEARCH_KEY=replace-with-a-long-random-key
```

When creating the file from a shell, substitute the generated value rather
than copying the literal placeholder:

```bash
sed -i "s|^APP_KEY=.*|APP_KEY=${APP_KEY_VALUE}|" /etc/jongerenkaart/production.env
chmod 600 /etc/jongerenkaart/production.env
```

Never use `change-me-in-production`, `local-development-key`, or the initial seeded password on a public server.

The production Compose file injects `APP_KEY` and `APP_URL` from this external
environment file. It also configures the internal MongoDB and Meilisearch
connections and uses file-backed Laravel sessions and caches. The
`--env-file` option only supplies values for Compose interpolation; it does not
inject arbitrary variables into a container unless `compose.prod.yaml`
explicitly lists them.

`src/.env` is excluded from the Docker image. Creating only `src/.env` on the deployment machine therefore does not configure the production container.

When using the example external environment file, include it in each production Compose command:

```bash
docker compose --env-file /etc/jongerenkaart/production.env -f compose.prod.yaml config --quiet
```

This validates interpolation without printing the resolved configuration and secrets.

### 3. Provide Initial Organisation Data

For a clean installation, securely place the organisation JSON backup at:

```text
inventaris_sociale_kaart.organisaties.json
```

The production Compose file mounts this file read-only into the MongoDB container. Skip the later import step when restoring MongoDB through another approved backup process.

### 4. Build And Start

Confirm that both dependency lockfiles were included in the uploaded project before building:

```bash
cd /opt/jongerenkaart
test -f src/composer.lock && echo "composer.lock present"
test -f src/package-lock.json && echo "package-lock.json present"
```

Stop if either confirmation is missing. The production Dockerfile uses `composer install` and `npm ci`; these lockfiles make the server build reproducible. Do not replace `npm ci` with an unlocked `npm install` in the Dockerfile.

Build and start all production services:

```bash
docker compose --env-file /etc/jongerenkaart/production.env -f compose.prod.yaml up -d --build
```

Check container state and startup logs:

```bash
docker compose --env-file /etc/jongerenkaart/production.env -f compose.prod.yaml ps
docker compose --env-file /etc/jongerenkaart/production.env -f compose.prod.yaml logs --tail=100 app nginx mongodb meilisearch
```

The first `app` startup copies the built public files into the empty `data/public-assets` directory. nginx serves that same directory.

Every production service, including Caddy and Portainer, uses Docker's rotating
`local` logging driver. Each container keeps at most three log files of 10 MB.
Laravel uses `LOG_CHANNEL=stderr`, so it does not create a second unbounded log
tree inside the app container. With six services, configured container-log
payload is therefore bounded to roughly 180 MB in total, plus small Docker
metadata overhead. The `local` driver may compress rotated files.

The fresh-server script also configures the same `local` driver as the Docker
daemon default with a 20 MB by five-file limit. That protects additional
containers started outside this Compose project. Per-service Compose limits
remain stricter and take precedence.

Log limits do not bound MongoDB growth, Caddy certificates, Portainer state,
new Docker images or build cache. The current dataset is small and the setup
script prunes build cache after installation, but still monitor `df -h /` and
`docker system df`; no finite disk can be guaranteed to run indefinitely
without monitoring.

### Limited-Disk Deployment

A 10 GB server disk is sufficient for the running application only while the
database remains small, but it does not provide a safe margin for building the
PHP production image on that same server. Compiling PHP extensions and
retaining image layers can temporarily require several additional gigabytes.
Use at least a 20 GB root or attached Block Storage volume for a server that
builds its own images.

Before and after every deployment, inspect available and Docker-managed space:

```bash
df -h /
df -i /
docker system df
```

Keep at least 3 GB free before an on-server build. After a successful
deployment, remove obsolete build cache and unused images:

```bash
docker builder prune -af
docker image prune -af
```

Never add `--volumes` to a prune command. Persistent MongoDB, Meilisearch, and
public data must not be treated as disposable cleanup targets.

For a 10 GB host, prefer building `jongerenkaart-app:prod` in CI or on a
separate build machine, publishing it to a private container registry, and
pulling that immutable image during deployment. This avoids the large
temporary build footprint on the production server.

### 5. Initialize A Clean Database

Only on a clean installation, import the organisation backup. The default command drops and replaces the existing `organisaties` collection, so do not run it as a routine deployment step:

```bash
docker compose --env-file /etc/jongerenkaart/production.env -f compose.prod.yaml exec mongodb sh /scripts/import-organisaties.sh
```

Seed the initial admin and default medewerkers:

```bash
docker compose --env-file /etc/jongerenkaart/production.env -f compose.prod.yaml exec app php artisan db:seed --force
```

The seeded admin starts as `admin@jongerenkaart.be` with password `Wachtwoord`. Log in immediately and change that password from `Mijn account`.

If the seeder reports that `SQLiteConnection::getCollection` does not exist,
the app container is still using an older Compose configuration or cached
Laravel configuration. Upload the current `compose.prod.yaml`, recreate the app
container, clear its configuration cache, and run the seeder again:

```bash
docker compose --env-file /etc/jongerenkaart/production.env -f compose.prod.yaml up -d --force-recreate app
docker compose --env-file /etc/jongerenkaart/production.env -f compose.prod.yaml exec app php artisan config:clear
docker compose --env-file /etc/jongerenkaart/production.env -f compose.prod.yaml exec app php artisan db:seed --force
```

### 6. Build Derived Search Data

Log in as the admin, open `/beheer/instellingen`, and run `Alles controleren en vernieuwen`. This creates the filter-option counts and cache, applies Meilisearch settings, recreates the search index, and imports all MongoDB organisations.

Do this after a clean import or full MongoDB restore. Normal organisation edits update derived data incrementally and do not need this full action.

### 7. Build Laravel Caches

Cache production configuration and Blade views:

```bash
docker compose --env-file /etc/jongerenkaart/production.env -f compose.prod.yaml exec app php artisan config:cache
docker compose --env-file /etc/jongerenkaart/production.env -f compose.prod.yaml exec app php artisan view:cache
```

Do not add `route:cache` yet. The project currently contains a closure-based `/tests` route; remove or convert that route before introducing route caching into deployment automation.

### 8. Configure HTTPS

The production stack runs the pinned official **Caddy container**. Caddy owns public ports `80` and `443`, manages certificates in `data/caddy/data`, and forwards traffic to `nginx:80` through the Compose `edge` network. nginx then reaches PHP-FPM through `backend`. Do not run Certbot or a second host-level Caddy service for the same domain.

The fresh-server script creates the persistent directories, starts Caddy with the rest of the stack, validates its configuration, and checks its connection to nginx. With DNS already configured, use an HTTPS application URL:

```bash
bash /root/setup-production-server.sh \
  --repo-url https://github.com/EIGENAAR/REPOSITORY.git \
  --branch main \
  --app-url https://jongerenkaart.example \
  --www-domain www.jongerenkaart.example
```

The script does not install Caddy on Ubuntu. It uses `caddy:2.11.4-alpine` from `compose.prod.yaml`, sets secure session cookies, and stores certificate state under `data/caddy`. It does not create DNS records or alter the cloud provider's security group.

Omit `--www-domain` when no `www` DNS record is configured. When supplied, Caddy also obtains a certificate for that hostname and redirects it to the canonical `--app-url`.

An ACME or SSL contact email is optional for this setup. The Caddyfile intentionally has no global `email` setting; automatic HTTPS still works without one. Add an address later only when an appropriate operational mailbox exists.

#### 8.1 Migrating From Host Caddy

If Caddy was previously installed directly on Ubuntu, stop that service before
starting the production stack. Otherwise, the Caddy container cannot bind ports
`80` and `443`:

```bash
sudo systemctl disable --now caddy
sudo ss -lntp | grep -E ':(80|443)\s' || true
```

Keep the Ubuntu package installed until the container setup has been verified. It
can then optionally be removed with `sudo apt remove caddy`. The fresh-server
script deliberately stops with an error when it detects an active host Caddy
service.

#### 8.2 Register A Domain With Infomaniak

Infomaniak can be used as the domain registrar and DNS provider even when this application runs on another company's server. A domain name is the registered name, such as `jongerenkaart.be`; `https://jongerenkaart.be` is the resulting website URL.

1. Create an Infomaniak account and organisation.
2. In **Manager**, open the shop and search for the domain name.
3. Complete the domain registration. Separate Infomaniak web hosting is not required when the Docker application is hosted on this server.
4. Open the domain's **DNS Zone** in Manager.
5. Add an `A` record for the root domain (`@`, or the empty host depending on the form) pointing to the server's static public IPv4 address.
6. Add either a `CNAME` record for `www` pointing to the root domain or another `A` record for `www` using the same server address.
7. Add an `AAAA` record only when the server has working public IPv6 and ports `80` and `443` are also reachable over IPv6.

Do not delete existing `MX`, SPF, DKIM, or DMARC records when email for the domain is already configured. DNS changes can take time to propagate; Infomaniak notes that changes may take up to 48 hours. Verify both names before requesting certificates:

```bash
dig +short jongerenkaart.example A
dig +short www.jongerenkaart.example A
```

Replace the example names throughout this section with the registered domain. See Infomaniak's official guides for [ordering a domain](https://www.infomaniak.com/en/support/faq/1987/commander-un-nom-de-domaine-aupres-dinfomaniak) and [managing domain DNS](https://www.infomaniak.com/en/support/faq/2003/getting-started-guide-domain-name).

#### 8.3 Optional: Monitor A Dynamic IP With Infomaniak DynDNS

Skip this section when the Instance already uses a Scaleway Flexible IPv4.
Flexible IP is simpler and does not need a watcher. Use this procedure only when
the Instance genuinely has a dynamic public IPv4 address.

Infomaniak includes DynDNS when it manages the domain's DNS zone. The server can
read its own address from the Scaleway Metadata API, compare it with the current
DNS answer, and call Infomaniak only when the addresses differ. Laravel, Caddy
and Compose continue to use `https://jongerenkaart.example`; no application
configuration file is rewritten.

First create the dynamic record:

1. Open the domain in **Infomaniak Manager**.
2. Select **Dynamic DNS**.
3. Choose **Add a Dynamic DNS**.
4. Leave the name empty to update the root domain, or enter the intended
   subdomain.
5. Enter the current server IPv4 address.
6. Create a dedicated DynDNS username and password. Do not reuse an Infomaniak
   account password.

When the root domain is dynamic, `www` can remain a `CNAME` to the root domain;
it does not require a second updater. Do not keep a separate static `A` record
for the same hostname as the dynamic record.

On the Ubuntu server, verify that Scaleway exposes the expected metadata field:

```bash
curl --noproxy '*' --fail --silent --show-error --max-time 10 \
  http://169.254.42.42/conf | grep '^PUBLIC_IP_ADDRESS='
```

Create a root-only file containing the hostname:

```bash
sudo install -d -m 0700 /etc/jongerenkaart
sudo nano /etc/jongerenkaart/infomaniak-ddns.env
```

```env
DDNS_HOSTNAME=jongerenkaart.example
```

Store the Infomaniak DynDNS credentials in a separate curl netrc file. This
keeps the password out of the script and process-list arguments:

```bash
sudo nano /etc/jongerenkaart/infomaniak-ddns.netrc
```

```text
machine infomaniak.com
login REPLACE_WITH_DYNDNS_USERNAME
password REPLACE_WITH_DYNDNS_PASSWORD
```

Protect both files:

```bash
sudo chown root:root \
  /etc/jongerenkaart/infomaniak-ddns.env \
  /etc/jongerenkaart/infomaniak-ddns.netrc
sudo chmod 600 \
  /etc/jongerenkaart/infomaniak-ddns.env \
  /etc/jongerenkaart/infomaniak-ddns.netrc
```

Create the updater:

```bash
sudo nano /usr/local/sbin/jongerenkaart-infomaniak-ddns
```

```bash
#!/usr/bin/env bash

set -Eeuo pipefail

source /etc/jongerenkaart/infomaniak-ddns.env

: "${DDNS_HOSTNAME:?DDNS_HOSTNAME is required}"

metadata="$(
  curl --noproxy '*' --fail --silent --show-error --max-time 10 \
    http://169.254.42.42/conf
)"

current_ip="$(
  printf '%s\n' "$metadata" |
    awk -F= '$1 == "PUBLIC_IP_ADDRESS" {
      gsub(/\047|"/, "", $2)
      print $2
      exit
    }'
)"

if [[ ! "$current_ip" =~ ^([0-9]{1,3}\.){3}[0-9]{1,3}$ ]]; then
  printf 'No valid Scaleway public IPv4 address was found.\n' >&2
  exit 1
fi

if getent ahostsv4 "$DDNS_HOSTNAME" |
  awk '{ print $1 }' |
  grep --fixed-strings --line-regexp --quiet "$current_ip"; then
  exit 0
fi

response="$(
  curl --fail --silent --show-error \
    --request POST \
    --netrc-file /etc/jongerenkaart/infomaniak-ddns.netrc \
    --data-urlencode "hostname=${DDNS_HOSTNAME}" \
    --data-urlencode "myip=${current_ip}" \
    https://infomaniak.com/nic/update
)"

case "$response" in
  good*|nochg*)
    printf 'Updated %s to %s: %s\n' "$DDNS_HOSTNAME" "$current_ip" "$response"
    ;;
  *)
    printf 'Infomaniak returned an unexpected response: %s\n' "$response" >&2
    exit 1
    ;;
esac
```

Make it root-owned and executable:

```bash
sudo chown root:root /usr/local/sbin/jongerenkaart-infomaniak-ddns
sudo chmod 750 /usr/local/sbin/jongerenkaart-infomaniak-ddns
```

Create `/etc/systemd/system/jongerenkaart-ddns.service`:

```ini
[Unit]
Description=Update Jongerenkaart Infomaniak DynDNS when the Scaleway IP changes
Wants=network-online.target
After=network-online.target
ConditionPathExists=/etc/jongerenkaart/infomaniak-ddns.env
ConditionPathExists=/etc/jongerenkaart/infomaniak-ddns.netrc

[Service]
Type=oneshot
ExecStart=/usr/local/sbin/jongerenkaart-infomaniak-ddns
User=root
SyslogIdentifier=jongerenkaart-ddns
StandardOutput=journal
StandardError=journal
LogRateLimitIntervalSec=1h
LogRateLimitBurst=5
NoNewPrivileges=true
PrivateTmp=true
ProtectHome=true
ProtectSystem=strict
ProtectKernelTunables=true
ProtectKernelModules=true
ProtectControlGroups=true
```

Create `/etc/systemd/system/jongerenkaart-ddns.timer`:

```ini
[Unit]
Description=Check the Jongerenkaart public IP every five minutes

[Timer]
OnBootSec=2min
OnUnitActiveSec=5min
RandomizedDelaySec=30s
Persistent=true
Unit=jongerenkaart-ddns.service

[Install]
WantedBy=timers.target
```

Test one execution before enabling the timer:

```bash
sudo systemctl daemon-reload
sudo systemctl start jongerenkaart-ddns.service
sudo systemctl status jongerenkaart-ddns.service --no-pager
sudo journalctl -u jongerenkaart-ddns.service --since "10 minutes ago"
```

The service exits silently when DNS is already correct. A successful update or
an error is written to journald; it does not create a separate growing log file.
Enable and inspect the timer:

```bash
sudo systemctl enable --now jongerenkaart-ddns.timer
systemctl list-timers jongerenkaart-ddns.timer
```

The updater creates no cache, history or dedicated log file. It produces no
output when the address is unchanged, and systemd accepts at most five messages
from this service per hour. Also place a hard limit on the complete Ubuntu
system journal so repeated operating-system or service errors cannot fill the
disk:

```bash
sudo install -d -m 0755 /etc/systemd/journald.conf.d
sudo nano /etc/systemd/journald.conf.d/10-jongerenkaart-storage.conf
```

```ini
[Journal]
SystemMaxUse=200M
RuntimeMaxUse=50M
SystemKeepFree=1G
MaxRetentionSec=30day
Compress=yes
```

Apply the journal limit and immediately remove older excess data:

```bash
sudo systemctl restart systemd-journald
sudo journalctl --vacuum-size=200M
sudo journalctl --disk-usage
```

This limit applies to all local systemd services, not only the DynDNS watcher.
It complements the separate Docker log limits in `compose.prod.yaml`. On a
20 GB server, the persistent journal is capped at 200 MB and journald also aims
to keep at least 1 GB free.

Manual monitoring:

```bash
curl --noproxy '*' --fail --silent --show-error --max-time 10 \
  http://169.254.42.42/conf | grep '^PUBLIC_IP_ADDRESS='
getent ahostsv4 jongerenkaart.example
systemctl status jongerenkaart-ddns.timer --no-pager
journalctl -u jongerenkaart-ddns.service --since today
```

After an IP change, DNS caches can still use the old value until its TTL expires.
Caddy requires no restart: it continues using the hostname and serves HTTPS when
requests reach the new address. Infomaniak documents both the
[DynDNS setup](https://www.infomaniak.com/en/support/faq/2357/manage-a-dynamic-ip-with-infomaniak-dyndns)
and its [HTTPS update endpoint](https://www.infomaniak.com/fr/support/faq/2376/utiliser-dyndns-infomaniak-par-api-infomaniak).

To stop using dynamic DNS:

```bash
sudo systemctl disable --now jongerenkaart-ddns.timer
```

Then replace the Infomaniak dynamic record with a normal `A` record before
removing the credentials or updater.

#### 8.4 Compose Networks And Laravel Proxy Handling

Production uses three networks:

| Network | Connected services | Purpose |
| --- | --- | --- |
| `edge` | Caddy and nginx | Public proxy to internal web server |
| `backend` | nginx, app, MongoDB and Meilisearch | Application and data traffic |
| `management` | Portainer | Separate management-service network |

Caddy connects to `nginx:80` by Compose service name. nginx has no `ports` mapping, so it cannot be reached directly from the host or internet. MongoDB and Meilisearch must remain unexposed on `backend`.

`docker/nginx/prod.conf` already passes the original host, client chain, and protocol to PHP. `src/bootstrap/app.php` trusts those proxy headers only when `TRUSTED_PROXIES` is configured; production Compose sets this because PHP-FPM is not publicly exposed. This allows Laravel to generate HTTPS URLs and record the forwarded visitor IP rather than the nginx container address.

For a manual installation, set:

```env
APP_URL=https://jongerenkaart.example
SESSION_SECURE_COOKIE=true
```

The repository passes these values into the app container:

```yaml
services:
  app:
    environment:
      APP_URL: ${APP_URL:?APP_URL is required}
      SESSION_SECURE_COOKIE: ${SESSION_SECURE_COOKIE:-false}
      TRUSTED_PROXIES: "*"
```

#### 8.5 Caddy Container Configuration

The version-controlled `docker/caddy/Caddyfile` contains:

```caddyfile
{$APP_URL} {
    encode zstd gzip
    reverse_proxy nginx:80
}

import sites/*
```

The complete `docker/caddy` directory is mounted read-only at `/etc/caddy`, as recommended by the official container image. The optional `--www-domain` flag creates `docker/caddy/sites/www.caddy` during fresh installation. An empty import glob is valid, so no file is needed when `www` is unused.

Persist both Caddy storage locations:

```text
data/caddy/data    certificates, private keys and ACME state
data/caddy/config  Caddy runtime configuration
```

The data directory is not a disposable cache. Include it in protected server backups and never remove it with `docker compose down -v`. Caddy enables automatic HTTPS when `APP_URL` contains a public hostname. DNS must point to this server, and inbound TCP ports `80` and `443` must be allowed by UFW and the cloud firewall. Port `80` remains open for redirects and certificate validation.

Validate the running container:

```bash
docker compose --env-file /etc/jongerenkaart/production.env -f compose.prod.yaml \
  exec caddy caddy validate --config /etc/caddy/Caddyfile
```

#### 8.6 Recreate And Verify

Apply the repository and environment changes:

```bash
docker compose --env-file /etc/jongerenkaart/production.env -f compose.prod.yaml up -d --build
docker compose --env-file /etc/jongerenkaart/production.env -f compose.prod.yaml exec app php artisan config:clear
docker compose --env-file /etc/jongerenkaart/production.env -f compose.prod.yaml exec app php artisan config:cache
```

Confirm that only Caddy owns the public web ports and Portainer is loopback-only:

```bash
docker compose --env-file /etc/jongerenkaart/production.env -f compose.prod.yaml ps
docker ps --format 'table {{.Names}}\t{{.Ports}}'
```

Check certificate issuance and redirects:

```bash
curl -I http://jongerenkaart.example
curl -I https://jongerenkaart.example
curl -I https://www.jongerenkaart.example
docker compose --env-file /etc/jongerenkaart/production.env -f compose.prod.yaml logs --tail=100 caddy nginx
```

The HTTP request should redirect to HTTPS, the root HTTPS request should return the application response, and `www` should redirect when configured. Also confirm that login cookies are marked `Secure`, browser certificate warnings are absent, and generated links use `https://`. Caddy renews managed certificates automatically; monitor the Caddy container logs rather than creating a separate renewal cron job.

When TLS terminates at an external load balancer instead, disable the Caddy service and publish nginx only to the trusted load-balancer boundary. Configure equivalent forwarded-protocol handling and use the provider's managed certificate and redirect facilities.

### 9. Access Portainer Safely

Production includes the pinned `portainer/portainer-ce:2.39.5-alpine` image. Portainer can inspect and control the local Docker engine because `/var/run/docker.sock` is mounted into it. Possession of a Portainer administrator account is therefore effectively equivalent to root access on the server.

For that reason:

- Portainer binds only to `127.0.0.1:9443`;
- it is not connected to Caddy and has no public hostname;
- port `9443` must not be opened in UFW or the cloud security group;
- Edge Agent tunnel port `8000` is not published;
- use a unique password of at least 12 characters;
- keep `data/portainer` in protected backups.

From the local computer, create an SSH tunnel and keep that terminal open:

```bash
ssh -L 9443:127.0.0.1:9443 root@51.15.121.139
```

Then open:

```text
https://localhost:9443
```

Portainer uses a self-signed certificate on this loopback-only interface, so the browser may display a certificate warning. Confirm that the address is exactly `localhost:9443` before proceeding. Create the initial administrator within five minutes and select the automatically detected local Docker environment.

If the initial setup times out, restart only Portainer and reconnect immediately:

```bash
docker compose --env-file /etc/jongerenkaart/production.env -f compose.prod.yaml restart portainer
```

Verify that it remains private:

```bash
docker compose --env-file /etc/jongerenkaart/production.env -f compose.prod.yaml port portainer 9443
ss -lntp | grep 9443
```

The expected binding starts with `127.0.0.1:9443`. Never change it to `"9443:9443"` or place Portainer behind the public Caddy site merely for convenience.

### 10. Verify The Deployment

Check at least:

- the home, search, map, login, and contact pages load over HTTPS;
- an organisation search returns results;
- map tiles and organisation pins load;
- admin login works and the seeded password has been changed;
- `Database controleren` reports MongoDB and Meilisearch as reachable;
- the contact page opens the expected configured recipient;
- MongoDB and Meilisearch ports are not publicly exposed;
- backups are stored outside the application data directories.

Follow live logs during verification:

```bash
docker compose --env-file /etc/jongerenkaart/production.env -f compose.prod.yaml logs -f app nginx
```

### Deploying Later Updates

Before every release, make a MongoDB backup. The admin JSON export covers organisations; use MongoDB's official backup tooling for a complete database backup that also includes users, site settings, content pages, medewerkers, and filter-option documents.

Then:

1. Pull or copy the new tested source revision.
2. Build the new images.
3. Stop the production stack without deleting `data/mongodb`.
4. Verify the exact `data/public-assets` path and clear only its contents so the new app image can repopulate it on startup.
5. Start the new stack.
6. Rebuild Laravel configuration and view caches.
7. Run the admin search rebuild only when searchable fields, Scout settings, or imported MongoDB data changed.
8. Repeat the deployment verification checks.

Build the new images:

```bash
docker compose --env-file /etc/jongerenkaart/production.env -f compose.prod.yaml build
```

Stop and start while retaining database files:

```bash
docker compose --env-file /etc/jongerenkaart/production.env -f compose.prod.yaml down
docker compose --env-file /etc/jongerenkaart/production.env -f compose.prod.yaml up -d
```

`down` does not delete the bind-mounted data folders. Never use `down -v` as part of the deployment procedure. Do not clear `data/mongodb` or `data/meilisearch/db` when refreshing public assets.

The public-assets directory currently copies files only when empty. Clearing that verified directory between image versions prevents nginx from continuing to serve assets from the previous release.

### Rollback

Keep the previous tested source revision or image available until verification succeeds. To roll back application code, stop the stack, restore the previous revision/image, clear only the verified `data/public-assets` contents, and start again. Restore MongoDB only when the release changed data incompatibly and only from a tested backup.

View production logs or stop the stack with:

```bash
docker compose --env-file /etc/jongerenkaart/production.env -f compose.prod.yaml logs -f
docker compose --env-file /etc/jongerenkaart/production.env -f compose.prod.yaml down
```

## Resetting Local Data

Stop the development stack first:

```bash
docker compose down
```

Only after confirming the repository path, clear the specific local data folder that needs resetting:

- `data/mongodb`: deletes the source database and all users/settings stored there.
- `data/meilisearch/db`: deletes only the rebuildable search index.
- `data/public-assets`: deletes copied production public assets.

Start again with `docker compose up -d`. If MongoDB was cleared, repeat the clean setup import, seed, and admin full-rebuild steps. If only Meilisearch was cleared, run the admin search-index rebuild.

## Troubleshooting

If `php artisan serve` reports `127.0.0.1:8000` and the host cannot connect, restart it with `--host=0.0.0.0`.

If MongoDB Compass or Atlas shows the Windows MongoDB server instead of Docker MongoDB, connect to `mongodb://localhost:27018`.

If an environment change appears ignored:

```bash
docker compose exec app php artisan config:clear
```

If a container exits, inspect its logs:

```bash
docker compose logs app
docker compose logs mongodb
docker compose logs meilisearch
```

## References

- [Laravel documentation](https://laravel.com/docs)
- [Laravel Scout](https://laravel.com/docs/scout)
- [MongoDB Laravel integration](https://www.mongodb.com/docs/drivers/php/laravel-mongodb/)
- [Meilisearch documentation](https://www.meilisearch.com/docs)
