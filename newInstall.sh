#!/bin/bash
# WikiMoDex V2 - Fresh install script
# Requires: submodules initialized (git submodule update --init --recursive)

set -e

echo "==> Migrating and seeding database..."
php artisan migrate:fresh --seed

echo "==> Running AdminSeeder (no-op if user hasn't logged in yet)..."
php artisan db:seed --class=AdminSeeder

echo "==> Syncing PokeVoid data (forms, items, changelog, alt builds)..."
php artisan pokevoid:sync

echo "==> Parsing moves..."
php artisan moves:parse

echo "==> Parsing official Pokémon..."
php artisan pokemon:parse-official

echo "==> Extracting item icons..."
php artisan items:extract-icons

echo "==> Registering Discord slash commands..."
php artisan discord:register-commands

echo ""
echo "Install complete!"
echo "Note: Run 'php artisan db:seed --class=AdminSeeder' again after first Discord login to grant admin access."
