#!/usr/bin/env bash

set -Eeuo pipefail

PROJECT_DIR="/opt/jongerenkaart"
ENV_FILE="/etc/jongerenkaart/production.env"
BRANCH="main"
SSH_PORT="22"
ADMIN_CIDR=""
REPO_URL=""
APP_URL=""
WWW_DOMAIN=""
SKIP_IMPORT="false"

usage() {
    cat <<'EOF'
Installeer Jongerenkaart op een verse Ubuntu-server.

Gebruik:
  sudo bash setup-production-server.sh \
    --repo-url https://github.com/eigenaar/repository.git \
    --app-url https://jongerenkaart.be

Verplichte opties:
  --repo-url URL       Git-URL van de Jongerenkaart-repository
  --app-url URL        Publieke URL, bijvoorbeeld https://jongerenkaart.be

Optionele opties:
  --branch NAAM        Git-branch (standaard: main)
  --project-dir PAD    Installatiemap (standaard: /opt/jongerenkaart)
  --env-file PAD       Productie-envbestand
  --ssh-port POORT     SSH-poort die UFW moet toelaten (standaard: 22)
  --admin-cidr CIDR    Beperk SSH tot dit IP/CIDR, bijvoorbeeld 203.0.113.8/32
  --www-domain NAAM    Optionele www-naam die naar --app-url doorstuurt
  --skip-import        Importeer het meegeleverde MongoDB JSON-bestand niet
  -h, --help           Toon deze hulp

Zonder --admin-cidr gebruikt UFW rate limiting voor SSH vanaf elk IP.
Een bestaande niet-lege projectmap of bestaand envbestand wordt nooit overschreven.
EOF
}

log() {
    printf '\n[%s] %s\n' "$(date '+%H:%M:%S')" "$*"
}

fail() {
    printf 'FOUT: %s\n' "$*" >&2
    exit 1
}

on_error() {
    printf '\nInstallatie gestopt op regel %s.\n' "$1" >&2
    printf 'Los de fout op en voer hetzelfde commando opnieuw uit.\n' >&2
}

trap 'on_error "$LINENO"' ERR

while [[ $# -gt 0 ]]; do
    case "$1" in
        --repo-url)
            [[ $# -ge 2 ]] || fail "--repo-url verwacht een waarde."
            REPO_URL="$2"
            shift 2
            ;;
        --app-url)
            [[ $# -ge 2 ]] || fail "--app-url verwacht een waarde."
            APP_URL="$2"
            shift 2
            ;;
        --branch)
            [[ $# -ge 2 ]] || fail "--branch verwacht een waarde."
            BRANCH="$2"
            shift 2
            ;;
        --project-dir)
            [[ $# -ge 2 ]] || fail "--project-dir verwacht een waarde."
            PROJECT_DIR="$2"
            shift 2
            ;;
        --env-file)
            [[ $# -ge 2 ]] || fail "--env-file verwacht een waarde."
            ENV_FILE="$2"
            shift 2
            ;;
        --ssh-port)
            [[ $# -ge 2 ]] || fail "--ssh-port verwacht een waarde."
            SSH_PORT="$2"
            shift 2
            ;;
        --admin-cidr)
            [[ $# -ge 2 ]] || fail "--admin-cidr verwacht een waarde."
            ADMIN_CIDR="$2"
            shift 2
            ;;
        --www-domain)
            [[ $# -ge 2 ]] || fail "--www-domain verwacht een waarde."
            WWW_DOMAIN="$2"
            shift 2
            ;;
        --skip-import)
            SKIP_IMPORT="true"
            shift
            ;;
        -h|--help)
            usage
            exit 0
            ;;
        *)
            fail "Onbekende optie: $1"
            ;;
    esac
done

[[ "$(id -u)" -eq 0 ]] || fail "Voer dit script uit als root of met sudo."
[[ -n "$REPO_URL" ]] || fail "--repo-url is verplicht."
[[ -n "$APP_URL" ]] || fail "--app-url is verplicht."
[[ "$APP_URL" =~ ^https?://[^[:space:]]+$ ]] || fail "--app-url moet met http:// of https:// beginnen."
APP_URL="${APP_URL%/}"
APP_SCHEME="${APP_URL%%://*}"
APP_HOST="${APP_URL#*://}"
[[ "$APP_HOST" != */* ]] || fail "--app-url mag geen pad bevatten."
[[ "$APP_HOST" != *:* ]] || fail "--app-url gebruikt de standaardpoort en mag geen poort bevatten."
if [[ -n "$WWW_DOMAIN" ]]; then
    [[ "$WWW_DOMAIN" =~ ^[A-Za-z0-9.-]+$ ]] || fail "--www-domain moet alleen een hostnaam bevatten."
    [[ "$WWW_DOMAIN" != "$APP_HOST" ]] || fail "--www-domain moet verschillen van de host in --app-url."
fi
[[ "$SSH_PORT" =~ ^[0-9]+$ ]] || fail "--ssh-port moet numeriek zijn."
(( SSH_PORT >= 1 && SSH_PORT <= 65535 )) || fail "--ssh-port moet tussen 1 en 65535 liggen."
[[ "$PROJECT_DIR" == /* ]] || fail "--project-dir moet een absoluut pad zijn."
[[ "$ENV_FILE" == /* ]] || fail "--env-file moet een absoluut pad zijn."

source /etc/os-release
[[ "${ID:-}" == "ubuntu" ]] || fail "Dit script ondersteunt alleen Ubuntu."

export DEBIAN_FRONTEND=noninteractive

log "Ubuntu-pakketten bijwerken"
apt-get update
apt-get upgrade -y
apt-get install -y ca-certificates curl git gnupg openssl ufw

log "UFW configureren"
ufw default deny incoming
ufw default allow outgoing

if [[ -n "$ADMIN_CIDR" ]]; then
    ufw allow from "$ADMIN_CIDR" to any port "$SSH_PORT" proto tcp
else
    ufw limit "${SSH_PORT}/tcp"
fi

ufw allow 80/tcp
ufw allow 443/tcp
ufw --force enable

install_docker() {
    log "Docker Engine en de Compose-plugin installeren"

    for package in docker.io docker-compose docker-compose-v2 docker-doc podman-docker containerd runc; do
        apt-get remove -y "$package" >/dev/null 2>&1 || true
    done

    install -m 0755 -d /etc/apt/keyrings
    curl -fsSL https://download.docker.com/linux/ubuntu/gpg \
        -o /etc/apt/keyrings/docker.asc
    chmod a+r /etc/apt/keyrings/docker.asc

    cat >/etc/apt/sources.list.d/docker.sources <<EOF
Types: deb
URIs: https://download.docker.com/linux/ubuntu
Suites: ${VERSION_CODENAME}
Components: stable
Architectures: $(dpkg --print-architecture)
Signed-By: /etc/apt/keyrings/docker.asc
EOF

    apt-get update
    apt-get install -y docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin
}

if ! command -v docker >/dev/null 2>&1 || ! docker compose version >/dev/null 2>&1; then
    install_docker
fi

systemctl enable --now docker
systemctl enable --now containerd

if systemctl is-active --quiet caddy 2>/dev/null; then
    fail "De Caddy-service op de host is nog actief. Voer eerst 'systemctl disable --now caddy' uit zodat de Caddy-container poorten 80 en 443 kan gebruiken."
fi

if [[ ! -e /etc/docker/daemon.json ]]; then
    log "Begrensde lokale Docker-logging configureren"
    install -m 0755 -d /etc/docker
    cat >/etc/docker/daemon.json <<'EOF'
{
  "log-driver": "local",
  "log-opts": {
    "max-size": "20m",
    "max-file": "5"
  }
}
EOF
    systemctl restart docker
else
    log "/etc/docker/daemon.json bestaat al en blijft ongewijzigd"
fi

if [[ -d "$PROJECT_DIR" ]] && [[ -n "$(find "$PROJECT_DIR" -mindepth 1 -maxdepth 1 -print -quit)" ]]; then
    fail "$PROJECT_DIR bestaat en is niet leeg. Verwijder of hernoem deze map eerst."
fi

log "Repository klonen uit $REPO_URL"
install -d -m 0755 "$(dirname "$PROJECT_DIR")"
git clone --depth 1 --branch "$BRANCH" "$REPO_URL" "$PROJECT_DIR"

cd "$PROJECT_DIR"

for required_file in \
    compose.prod.yaml \
    docker/caddy/Caddyfile \
    docker/php/Dockerfile.prod \
    src/composer.lock \
    src/package-lock.json \
    inventaris_sociale_kaart.organisaties.json; do
    [[ -f "$required_file" ]] || fail "Verplicht bestand ontbreekt: $required_file"
done

log "Lokale datamappen aanmaken"
install -d -m 0755 \
    data/mongodb \
    data/meilisearch/db \
    data/public-assets \
    data/caddy/data \
    data/caddy/config \
    data/portainer \
    docker/caddy/sites

if [[ -e "$ENV_FILE" ]]; then
    log "$ENV_FILE bestaat al en blijft ongewijzigd"
else
    log "Productiegeheimen genereren in $ENV_FILE"
    install -d -m 0700 "$(dirname "$ENV_FILE")"
    umask 077

    APP_KEY="base64:$(openssl rand -base64 32 | tr -d '\n')"
    MEILISEARCH_KEY="$(openssl rand -hex 32)"
    SESSION_SECURE_COOKIE="false"

    if [[ "$APP_SCHEME" == "https" ]]; then
        SESSION_SECURE_COOKIE="true"
    fi

    cat >"$ENV_FILE" <<EOF
APP_URL=${APP_URL}
APP_KEY=${APP_KEY}
MEILISEARCH_KEY=${MEILISEARCH_KEY}
SESSION_SECURE_COOKIE=${SESSION_SECURE_COOKIE}
EOF
    chmod 0600 "$ENV_FILE"
fi

ENV_APP_URL="$(sed -n 's/^APP_URL=//p' "$ENV_FILE" | tail -n 1)"
[[ "$ENV_APP_URL" == "$APP_URL" ]] || fail \
    "$ENV_FILE bevat APP_URL=$ENV_APP_URL, maar --app-url is $APP_URL."

if [[ "$APP_SCHEME" == "https" ]] && ! grep -qx 'SESSION_SECURE_COOKIE=true' "$ENV_FILE"; then
    fail "Voeg SESSION_SECURE_COOKIE=true toe aan $ENV_FILE voor HTTPS."
fi

if [[ -n "$WWW_DOMAIN" ]]; then
    cat >docker/caddy/sites/www.caddy <<EOF
${APP_SCHEME}://${WWW_DOMAIN} {
	redir ${APP_URL}{uri} permanent
}
EOF
fi

compose() {
    docker compose \
        --env-file "$ENV_FILE" \
        -f "$PROJECT_DIR/compose.prod.yaml" \
        "$@"
}

log "Compose-configuratie controleren"
compose config --quiet

log "Productie-images bouwen en containers starten"
compose up -d --build

log "Interne Caddy-naar-nginx verbinding controleren"
nginx_ready="false"
for _ in {1..60}; do
    if compose exec -T caddy wget -qO- http://nginx/up >/dev/null 2>&1; then
        nginx_ready="true"
        break
    fi
    sleep 2
done
[[ "$nginx_ready" == "true" ]] || fail "Caddy kon nginx niet bereiken via het edge-netwerk."

log "Wachten tot MongoDB klaar is"
mongodb_ready="false"
for _ in {1..60}; do
    if compose exec -T mongodb mongosh --quiet \
        --eval 'quit(db.adminCommand({ping: 1}).ok === 1 ? 0 : 1)' >/dev/null 2>&1; then
        mongodb_ready="true"
        break
    fi
    sleep 2
done
[[ "$mongodb_ready" == "true" ]] || fail "MongoDB werd niet tijdig klaar. Bekijk: docker compose -f compose.prod.yaml logs mongodb"

log "Wachten tot Meilisearch klaar is"
meilisearch_ready="false"
for _ in {1..60}; do
    if compose exec -T app php -r \
        'exit(@file_get_contents("http://meilisearch:7700/health") === false ? 1 : 0);' >/dev/null 2>&1; then
        meilisearch_ready="true"
        break
    fi
    sleep 2
done
[[ "$meilisearch_ready" == "true" ]] || fail "Meilisearch werd niet tijdig klaar. Bekijk de Meilisearch-logs."

if [[ "$SKIP_IMPORT" == "false" ]]; then
    log "Organisaties uit de MongoDB-back-up importeren"
    compose exec -T mongodb sh /scripts/import-organisaties.sh
else
    log "MongoDB-import overgeslagen door --skip-import"
fi

log "Adminaccount en standaardgegevens seeden"
compose exec -T app php artisan db:seed --force

log "Filteropties en Meilisearch-index opbouwen"
compose exec -T app php artisan jongerenkaart:rebuild-derived-data

log "Laravel optimaliseren"
compose exec -T app php artisan config:clear
compose exec -T app php artisan config:cache
compose exec -T app php artisan view:cache

log "Caddy-containerconfiguratie controleren"
compose exec -T caddy caddy validate --config /etc/caddy/Caddyfile

if [[ "$APP_SCHEME" == "https" ]]; then
    log "HTTPS wordt automatisch aangevraagd voor $APP_HOST"
    if ! getent ahostsv4 "$APP_HOST" >/dev/null 2>&1; then
        printf 'WAARSCHUWING: DNS voor %s is nog niet zichtbaar. Caddy blijft het certificaat later opnieuw proberen.\n' "$APP_HOST"
    fi
    if [[ -n "$WWW_DOMAIN" ]] && ! getent ahostsv4 "$WWW_DOMAIN" >/dev/null 2>&1; then
        printf 'WAARSCHUWING: DNS voor %s is nog niet zichtbaar. Caddy blijft het certificaat later opnieuw proberen.\n' "$WWW_DOMAIN"
    fi
fi

log "Portainer opnieuw starten voor de beveiligde eerste configuratie"
compose restart portainer

log "Tijdelijke Docker-buildcache opruimen"
docker builder prune -af

log "Eindcontrole"
compose ps
compose exec -T app php artisan about --only=environment
compose exec -T mongodb mongosh --quiet jongerenkaart \
    --eval 'print("Organisaties: " + db.organisaties.countDocuments({}))'
ufw status
df -h /

cat <<EOF

Installatie voltooid.

Website: ${APP_URL}
Login:   admin@jongerenkaart.be
Paswoord: Wachtwoord

Wijzig het standaardwachtwoord onmiddellijk via "Mijn account".
Caddy is de enige publiek bereikbare container en stuurt via het edge-netwerk
door naar nginx. MongoDB, Meilisearch en PHP blijven op het backend-netwerk.
Bij een https:// APP_URL beheert Caddy het certificaat en de HTTP-naar-HTTPS
redirect automatisch. DNS voor ${APP_HOST} moet naar deze server wijzen en
poort 80 en 443 moeten ook in de firewall van de cloudprovider openstaan.

Portainer is alleen via een SSH-tunnel bereikbaar. Voer nu op je eigen computer
uit en maak binnen vijf minuten het eerste sterke beheerdersaccount aan:
  ssh -L 9443:127.0.0.1:9443 root@${APP_HOST}
Open daarna:
  https://localhost:9443
De certificaatwaarschuwing op deze lokale Portainer-URL is normaal.

Handige controle:
  cd ${PROJECT_DIR}
  docker compose --env-file ${ENV_FILE} -f compose.prod.yaml ps
  docker compose --env-file ${ENV_FILE} -f compose.prod.yaml logs --tail=100
  docker compose --env-file ${ENV_FILE} -f compose.prod.yaml logs caddy portainer
EOF
