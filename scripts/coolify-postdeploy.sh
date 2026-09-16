#!/usr/bin/env sh
set -eu

php artisan storage:link --force
php artisan migrate --force
mkdir -p storage/app/public/branding public/uploads/branding
chmod -R ug+rwx storage/app/public public/uploads || true