# WikiMoDex (pokevoid.wiki)

WikiMoDex is the Laravel app behind [pokevoid.wiki](https://pokevoid.wiki) — a wiki/gallery site for the PokeVoid Pokémon ROM hack, with Discord OAuth login, a wiki editor, a glitch/creation gallery, trainer cards, and a small admin panel.

This README covers standing up your own copy of the app on new hosting.

## Requirements

- **PHP 8.3+** (8.4 recommended) with the `pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `ctype`, `fileinfo`, and `bcmath` extensions (standard on most PHP hosts — check with `php -m`)
- **MySQL 5.7+ / 8.0+**
- **Composer**
- **Node.js 18+ and npm** (for building frontend assets)
- **Git**

## 1. Clone the repo

The `pokevoid/` directory is a git submodule pinned to a specific commit in this repo (it holds sprite/image assets used at runtime). Clone with submodules so it's fetched automatically:

```bash
git clone --recurse-submodules https://github.com/Scoooom/WikiMoDex-V2.git
cd WikiMoDex-V2
```

If you already cloned without that flag:

```bash
git submodule update --init --recursive
```

## 2. Install dependencies

```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run build
```

## 3. Configure the environment

```bash
cp .env.example .env
```

Then edit `.env`:

- **Database** — fill in `DB_HOST`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` for the database you create in step 4.
- **Secrets** — the previous site operator will give you a separate set of values for `APP_KEY`, `DISCORD_CLIENT_ID`, `DISCORD_CLIENT_SECRET`, `DISCORD_REDIRECT_URI`, `DISCORD_PUBLIC_KEY`, and `PRSV_ENCRYPTION_KEY`. Copy those in as given, unchanged — the domain (`pokevoid.wiki`) isn't changing, so there's nothing to update in the Discord Developer Portal.
- **Cloudflare** — leave `CLOUDFLARE_API_TOKEN` and `CLOUDFLARE_ZONE_ID` blank. This deployment doesn't sit behind Cloudflare; the app already checks for these before making any API call and silently skips the cache-purge step when they're unset, so nothing breaks.

## 4. Set up the database

Create a database and user on the new host, then import the provided dump:

```bash
mysql -u YOUR_USER -p YOUR_DATABASE < wikimodex-dump.sql
# or, if compressed:
gunzip -c wikimodex-dump.sql.gz | mysql -u YOUR_USER -p YOUR_DATABASE
```

**This dump contains real production data** — actual Discord user accounts, wiki content, uploaded creations, etc. It's provided to you outside of this git repo (not committed here) — treat it as sensitive and don't re-publish it anywhere public.

## 5. Finish setup

```bash
php artisan storage:link
```

Make sure `storage/` and `bootstrap/cache/` are writable by whatever user your web server runs as. Point your web server's document root at the `public/` directory.

## 6. Scheduled tasks & queue

The app runs a weekly sync job (`pokevoid:sync`) via Laravel's scheduler. Add this to cron:

```
* * * * * cd /path/to/app && php artisan schedule:run >> /dev/null 2>&1
```

Queued jobs use the `database` queue driver. Either run a persistent worker:

```bash
php artisan queue:work
```

(under a process manager like supervisor or systemd, so it restarts if it dies), or, on hosting without background-process support, set `QUEUE_CONNECTION=sync` in `.env` — queued work then just runs inline on the request that triggered it.

## Granting admin access

Admin/editor status is a plain flag on the `users` table. The target user must log in via Discord **at least once first** (that's what creates their row), then:

```sql
UPDATE users SET is_admin = 1, is_wiki_editor = 1 WHERE user_id = '<their discord user id>';
```

`database/seeders/AdminSeeder.php` shows this same pattern hardcoded to one specific Discord ID — you can edit that ID and run `php artisan db:seed --class=AdminSeeder`, or just run the `UPDATE` above directly.

## Granting super-admin access

"Super admin" is a step above regular admin and is **not** a database flag — it's a hardcoded allowlist of Discord user IDs in `app/Models/User.php`:

```php
public function isSuperAdmin(): bool
{
    return (bool) in_array($this->user_id, ["356260100064673814", "1339032924170879118"]);
}
```

To add or remove a super admin, edit that array, commit the change, and redeploy (run `php artisan optimize:clear` or restart PHP-FPM if config/opcache caching is enabled, so the change takes effect).

## Two-factor requirement for the admin panel

Access to `/admin` additionally requires `mfa_enabled` on the user's row. This isn't something you configure in the app — it's copied automatically from Discord's own `mfa_enabled` field every time the user logs in via OAuth. In other words: the admin panel is only reachable by users who have two-factor authentication turned on for their **Discord account**.

## Security note

Don't upload debug/diagnostic scripts (e.g. `phpinfo()` dumps) into `public/` — anything placed there is served directly to the internet.
