# Mijn snelle serverinstallatie

## Verse Ubuntu-server automatisch installeren

Het installatiescript gebruikt een Git-repository. De repository-URL is bewust
een verplichte flag en staat dus niet vast in het script.

Kopieer het script vanaf mijn Windows-computer naar de server:

```powershell
scp C:\git\jongerenkaart\scripts\setup-production-server.sh root@51.15.121.139:/root/
```

Verbind met de server:

```bash
ssh root@51.15.121.139
```

Voer daarna de volledige installatie uit:

```bash
bash /root/setup-production-server.sh \
  --repo-url https://github.com/EIGENAAR/REPOSITORY.git \
  --branch main \
  --app-url https://jongerenkaart.be \
  --www-domain www.jongerenkaart.be
```

Voor deze HTTPS-opdracht moeten de DNS-records van `jongerenkaart.be` eerst
naar `51.15.121.139` wijzen. Voor een tijdelijke test zonder domein kan
`--app-url http://51.15.121.139` worden gebruikt; Caddy werkt dan als HTTP
reverse proxy zonder publiek TLS-certificaat.

Een SSL-e-mailadres is niet verplicht. De Caddy-container kan de certificaten
automatisch aanvragen en vernieuwen zonder expliciet ACME-contactadres.

Had ik Caddy eerder rechtstreeks op Ubuntu geïnstalleerd, dan stop ik die service
eerst:

```bash
systemctl disable --now caddy
ss -lntp | grep -E ':(80|443)\s' || true
```

De Caddy-container kan die poorten anders niet innemen.

Gebruik bij voorkeur ook mijn huidige publieke IP voor de SSH-firewallregel:

```bash
bash /root/setup-production-server.sh \
  --repo-url https://github.com/EIGENAAR/REPOSITORY.git \
  --branch main \
  --app-url https://jongerenkaart.be \
  --www-domain www.jongerenkaart.be \
  --admin-cidr MIJN_PUBLIEKE_IP/32
```

Het script:

- installeert systeemupdates, Git, UFW en Docker met de Compose-plugin;
- start de Caddy-container en configureert automatisch HTTPS volgens `--app-url`;
- start Portainer alleen op `127.0.0.1:9443`;
- laat alleen SSH, HTTP en HTTPS toe in UFW;
- kloont de opgegeven Git-repository naar `/opt/jongerenkaart`;
- genereert `/etc/jongerenkaart/production.env` met nieuwe geheimen;
- bouwt en start alle containers;
- importeert de meegeleverde organisaties;
- voert de Laravel-seeder uit;
- bouwt filteropties en de Meilisearch-index op;
- bouwt de Laravel-caches op en voert eindcontroles uit.

Bekijk alle flags zonder iets te installeren:

```bash
bash /root/setup-production-server.sh --help
```

Het script weigert een niet-lege `/opt/jongerenkaart` te overschrijven. De
onderstaande handmatige checklist blijft bruikbaar voor een herinstallatie van
een bestaande server.

Dit is mijn korte checklist om Jongerenkaart volledig opnieuw te installeren
met een lege database.

## 1. Project vanaf mijn computer uploaden

Open PowerShell op mijn computer en maak een uploadbestand zonder lokale
Docker-data, dependencies, Git-data of de lokale `.env`:

```powershell
cd C:\git\jongerenkaart

tar.exe -czf "$env:TEMP\jongerenkaart-upload.tar.gz" `
  --exclude=.git `
  --exclude=data `
  --exclude=src/vendor `
  --exclude=src/node_modules `
  --exclude=src/.env `
  .
```

Kopieer het bestand via SSH naar de server:

```powershell
scp "$env:TEMP\jongerenkaart-upload.tar.gz" root@51.15.121.139:/tmp/
```

## 2. Verbinden met de server

```bash
ssh root@51.15.121.139
```

## 3. Oude installatie stoppen

```bash
cd /opt/jongerenkaart

docker compose \
  --env-file /etc/jongerenkaart/production.env \
  -f compose.prod.yaml \
  down
```

Bewaar de oude map tijdelijk zodat ik nog kan terugkeren:

```bash
cd /opt
mv jongerenkaart jongerenkaart-old
```

## 4. Geuploade versie installeren

```bash
cd /opt
mkdir -p jongerenkaart
tar -xzf /tmp/jongerenkaart-upload.tar.gz -C jongerenkaart
rm /tmp/jongerenkaart-upload.tar.gz
cd jongerenkaart
```

Controleer of de belangrijke bestanden aanwezig zijn:

```bash
test -f compose.prod.yaml
test -f src/composer.lock
test -f src/package-lock.json
test -f inventaris_sociale_kaart.organisaties.json
test -f /etc/jongerenkaart/production.env
```

Maak de lege datamappen:

```bash
mkdir -p data/mongodb
mkdir -p data/meilisearch/db
mkdir -p data/public-assets
mkdir -p data/caddy/data
mkdir -p data/caddy/config
mkdir -p data/portainer
```

## 5. Containers bouwen en starten

```bash
docker compose \
  --env-file /etc/jongerenkaart/production.env \
  -f compose.prod.yaml \
  up -d --build
```

Controleer of alles draait:

```bash
docker compose \
  --env-file /etc/jongerenkaart/production.env \
  -f compose.prod.yaml \
  ps

docker compose \
  --env-file /etc/jongerenkaart/production.env \
  -f compose.prod.yaml \
  logs --tail=100 caddy app nginx mongodb meilisearch portainer
```

Portainer is alleen bereikbaar via een SSH-tunnel vanaf mijn computer:

```powershell
ssh -L 9443:127.0.0.1:9443 root@51.15.121.139
```

Daarna `https://localhost:9443` openen en binnen vijf minuten een sterk,
uniek Portainer-beheerderswachtwoord instellen. Bij een timeout:

```bash
docker compose \
  --env-file /etc/jongerenkaart/production.env \
  -f compose.prod.yaml \
  restart portainer
```

## 6. Organisaties importeren

```bash
docker compose \
  --env-file /etc/jongerenkaart/production.env \
  -f compose.prod.yaml \
  exec mongodb \
  sh /scripts/import-organisaties.sh
```

## 7. Admin en standaardmedewerkers aanmaken

```bash
docker compose \
  --env-file /etc/jongerenkaart/production.env \
  -f compose.prod.yaml \
  exec app \
  php artisan db:seed --force
```

Inloggen met:

```text
E-mail: admin@jongerenkaart.be
Wachtwoord: Wachtwoord
```

Daarna onmiddellijk het wachtwoord wijzigen via `Mijn account`.

## 8. Zoekindex en filters opbouwen

1. Inloggen als admin.
2. Naar `Beheer > Instellingen` gaan.
3. Op `Alles controleren en vernieuwen` drukken.
4. Wachten tot MongoDB, filteropties en Meilisearch klaar zijn.

## 9. Laravel-cache opbouwen

```bash
docker compose \
  --env-file /etc/jongerenkaart/production.env \
  -f compose.prod.yaml \
  exec app php artisan config:clear

docker compose \
  --env-file /etc/jongerenkaart/production.env \
  -f compose.prod.yaml \
  exec app php artisan config:cache

docker compose \
  --env-file /etc/jongerenkaart/production.env \
  -f compose.prod.yaml \
  exec app php artisan view:cache
```

## 10. Controleren

```bash
docker compose \
  --env-file /etc/jongerenkaart/production.env \
  -f compose.prod.yaml \
  ps

curl -I https://jongerenkaart.be
```

Zelf controleren:

- homepagina;
- zoeken en kaart;
- detailpagina;
- adminlogin;
- medewerkers op de Info-pagina;
- organisatie toevoegen, aanpassen en verwijderen;
- account aanvragen en activeren;
- `Database controleren` bij Instellingen.

## 11. Schijfruimte opruimen

Alleen doen wanneer de nieuwe website goed werkt:

```bash
docker builder prune -af
docker image prune -af
df -h /
```

De oude map pas verwijderen nadat alles gecontroleerd is:

```bash
ls -ld /opt/jongerenkaart-old
rm -rf /opt/jongerenkaart-old
```

Nooit `docker system prune --volumes` of `docker compose down -v` gebruiken.

Het tijdelijke uploadbestand op mijn computer mag daarna weg:

```powershell
Remove-Item "$env:TEMP\jongerenkaart-upload.tar.gz"
```
