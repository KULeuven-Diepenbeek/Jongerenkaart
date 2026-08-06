#!/bin/sh
set -e

DATABASE="${MONGO_DATABASE:-jongerenkaart}"
COLLECTION="${MONGO_COLLECTION:-organisaties}"
BACKUP_FILE="${MONGO_BACKUP_FILE:-/backups/inventaris_sociale_kaart.organisaties.json}"
URI="${MONGO_URI:-mongodb://localhost:27017}"
DROP_FLAG="${MONGO_IMPORT_DROP:---drop}"

if ! command -v mongoimport >/dev/null 2>&1; then
    echo "mongoimport was not found in this MongoDB container."
    echo "Use a MongoDB image that includes the MongoDB Database Tools."
    exit 1
fi

if [ ! -f "$BACKUP_FILE" ]; then
    echo "Backup file not found: $BACKUP_FILE"
    exit 1
fi

echo "Importing $BACKUP_FILE into $DATABASE.$COLLECTION"

mongoimport \
    --uri "$URI" \
    --db "$DATABASE" \
    --collection "$COLLECTION" \
    --file "$BACKUP_FILE" \
    --jsonArray \
    $DROP_FLAG

echo "Import complete."
