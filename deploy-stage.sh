#!/usr/bin/env bash
set -euo pipefail

BASE="/var/www/niipi-stage"
REPO="$BASE/repo.git"
RELEASES="$BASE/releases"
SHARED="$BASE/shared"
BRANCH="staging"
KEEP_RELEASES=5

PHP_FPM_SERVICE="php8.4-fpm"

RELEASE="$(date +%Y%m%d%H%M%S)"
RELEASE_DIR="$RELEASES/$RELEASE"

mkdir -p "$RELEASE_DIR"

git --git-dir="$REPO" fetch --prune origin
git --git-dir="$REPO" --work-tree="$RELEASE_DIR" checkout -f "origin/$BRANCH"

ln -sfn "$SHARED/.env" "$RELEASE_DIR/.env"
rm -rf "$RELEASE_DIR/storage"
ln -sfn "$SHARED/storage" "$RELEASE_DIR/storage"

cd "$RELEASE_DIR"

composer install --no-dev --optimize-autoloader --no-interaction

php artisan storage:link || true
php artisan migrate --force

php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

php artisan filament:optimize || true
php artisan filament:cache-components || true

php artisan queue:restart || true

ln -sfn "$RELEASE_DIR" "$BASE/current"

sudo systemctl reload "$PHP_FPM_SERVICE"

cd "$RELEASES"
ls -1dt */ 2>/dev/null | tail -n +$((KEEP_RELEASES+1)) | xargs -r rm -rf