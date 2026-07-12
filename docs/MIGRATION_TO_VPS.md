# Migrating portal.ehlom.com from Bunny Docker → VPS (CloudPanel)

Goal: move the app with zero data loss — same `APP_KEY`, same database contents, same
uploaded files — before starting any multi-tenant build work (see
`MULTI_TENANT_BUILD_PLAN.md` Phase 0).

## What actually needs to move (confirmed by reading the code)

| Thing | Where it lives now | Why it matters |
|---|---|---|
| `APP_KEY` | Bunny env var (injected, not in repo — `.env` is gitignored) | Signs sessions, password-reset links, email-verification links, and any future encrypted DB columns (Phase 4 payment keys will use this). **Must be copied exactly, never regenerated**, or old sessions/links break and any future encrypted data becomes unreadable. |
| Database (MySQL/MariaDB) | Bunny's DB sidecar | Contains all clients, projects, invoices, subscriptions, leads, **and mail credentials** (`settings` table — Brevo API key, from-address). Moving the DB brings mail config with it automatically. |
| Uploaded files | `storage/app/public/*` on Bunny's persistent volume | Client logos, any generated files. **Not in git** — `.gitignore` only tracks placeholder `.gitignore` files in those folders, confirmed empty in this local checkout. These exist only on the live Bunny volume. |
| Code | Already in git (`main` branch, up to date) | Just `git clone` on the VPS — nothing special. |
| Other env vars (`DB_*`, `APP_URL`, `MAIL_*` fallback) | Bunny env vars | Copy for reference but most get new values pointing at the VPS's local DB — only `APP_KEY` must stay byte-identical. |

## Step 1 — Collect current secrets from Bunny

Log into the Bunny dashboard for this container, open its environment variables page, and
copy down (paste somewhere safe temporarily, e.g. a password manager note — not a plain
text file left lying around):

- `APP_KEY` (critical — copy exactly)
- `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` (needed to connect and
  dump the database in Step 3 — only needed temporarily during migration)
- Anything else set there (check for `AWS_*` if S3 was ever configured, though the app
  code confirms `FILESYSTEM_DISK` is local storage, so this is unlikely to be in use)

## Step 2 — Set up the app on the VPS (CloudPanel)

1. In CloudPanel, create the site for `portal.ehlom.com` (PHP 8.3+, matching what's in
   `composer.json` — `"php": "^8.3"`).
2. SSH into the VPS, `cd` into the site's root, and clone the repo:
   ```
   git clone <your-repo-url> .
   ```
   (or pull if CloudPanel already initialized an empty git repo there)
3. Install dependencies:
   ```
   composer install --no-dev --optimize-autoloader
   npm ci && npm run build
   ```
4. Create the real `.env` file (CloudPanel apps use a persistent `.env`, unlike Bunny's
   env-var-injection approach — this is a one-time manual file, not gitignored-and-
   regenerated each deploy):
   ```
   cp .env.example .env
   ```
   Then edit it:
   - `APP_KEY=` → paste the **exact** value copied from Bunny in Step 1. Do **not** run
     `php artisan key:generate`.
   - `APP_ENV=production`
   - `APP_DEBUG=false`
   - `APP_URL=https://portal.ehlom.com`
   - `DB_CONNECTION=mysql`
   - `DB_HOST=127.0.0.1` (or whatever CloudPanel's MySQL host is — usually localhost)
   - `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` → create a **new** database + user in
     CloudPanel's MySQL manager for this app, use those new credentials here (you're
     importing data into it in Step 3, not connecting back to Bunny's DB long-term)
   - `FILESYSTEM_DISK=public`
   - `SESSION_DRIVER=database`, `QUEUE_CONNECTION=database` (matches current setup)
   - Leave `MAIL_*` as-is/defaults — real mail config comes from the `settings` table
     once the DB is imported (Step 3), per `MailConfigService`.

## Step 3 — Move the database

From your local machine (or anywhere with network access to both), export from Bunny's
DB using the credentials from Step 1:

```
mysqldump -h <bunny_db_host> -P <bunny_db_port> -u <bunny_db_user> -p<bunny_db_password> \
  <bunny_db_name> > ehlom_export.sql
```

Copy the dump to the VPS:

```
scp ehlom_export.sql your-vps-user@your-vps-ip:/tmp/
```

On the VPS, import it into the new database you created in Step 2:

```
mysql -u <new_db_user> -p<new_db_password> <new_db_name> < /tmp/ehlom_export.sql
```

This brings over every table as-is — including `settings` (mail config), so you should
**not** need to re-enter the Brevo API key manually. Delete `/tmp/ehlom_export.sql` and
your local copy once confirmed imported (it contains real client data).

## Step 4 — Move uploaded files

These only exist on Bunny's live persistent volume, not in git. How you get them depends
on what Bunny exposes:

- **If Bunny gives you shell/exec access to the running container:** tar the folder and
  copy it out:
  ```
  tar -czf storage-backup.tar.gz storage/app/public
  ```
  then `scp`/download that archive, and on the VPS:
  ```
  scp storage-backup.tar.gz your-vps-user@your-vps-ip:/tmp/
  # on the VPS:
  tar -xzf /tmp/storage-backup.tar.gz -C /path/to/site/root/
  ```
- **If Bunny only gives a file browser / no shell access:** download the files manually
  through whatever file management UI Bunny provides for the volume, then upload them to
  the same relative path (`storage/app/public/...`) on the VPS via `scp` or CloudPanel's
  file manager.
- Check first whether there's actually much there — this app is young (started mid-May),
  so it may just be a handful of client logos rather than a large volume.

On the VPS, recreate the public symlink:
```
php artisan storage:link
```

## Step 5 — Finish setup on the VPS

```
php artisan config:cache
php artisan route:cache
php artisan view:cache
chown -R <cloudpanel-app-user>:<cloudpanel-app-user> storage bootstrap/cache
```

Do **not** run `php artisan migrate` if you imported the full dump in Step 3 — the schema
already came with it. Only run `migrate` if you deliberately did a fresh schema + separate
data import instead.

Set up the Laravel scheduler and queue worker as CloudPanel cron jobs / systemd services
(the Bunny setup used `supervisord` for this inside the container — on the VPS use
CloudPanel's cron job UI to run `php artisan schedule:run` every minute, and set up a
systemd service or CloudPanel-managed process for `php artisan queue:work` if the app
needs background jobs processed continuously — check `app/Console/Commands/` for
scheduled jobs like `GenerateRenewalInvoices` and `SendRenewalReminders`).

## Step 6 — Test before touching DNS

Don't switch DNS yet. On your own laptop, temporarily edit `/etc/hosts` to point
`portal.ehlom.com` at the VPS IP:

```
<vps-ip>  portal.ehlom.com
```

Visit `https://portal.ehlom.com` in a browser (you'll need a cert — CloudPanel can issue
one, or just test over plain HTTP with `http://` for this step) and verify:
- You can log in with your existing admin account (proves DB import worked)
- Existing clients/projects/invoices/leads are all present
- Client logos and any uploaded images render correctly (proves file copy worked)
- Try sending a test email from Settings (proves the Brevo config carried over)

Remove the `/etc/hosts` line once satisfied.

## Step 7 — Cutover

1. Lower your DNS TTL for `portal.ehlom.com` a day ahead of time if possible, so the
   eventual switch propagates fast.
2. Right before cutover, put the Bunny instance into maintenance mode so no new data is
   written during the gap:
   ```
   php artisan down
   ```
   (run this against the Bunny container)
3. Re-run Steps 3 and 4 (a quick final DB dump + file sync) to catch anything written
   since your first pass — this is the actual zero-data-loss step.
4. Import that final delta into the VPS as before.
5. Update the DNS A record for `portal.ehlom.com` to the VPS IP.
6. Once propagated (check with `dig portal.ehlom.com`), verify the live site on the VPS
   one more time.
7. Run `php artisan up` — but on the **VPS** this time, not Bunny.

## Step 8 — Don't delete Bunny immediately

Keep the Bunny container around (stopped, not deleted) for a few days as a rollback
option in case something surfaces post-cutover. Decommission it only once you're
confident the VPS is stable — then proceed to `MULTI_TENANT_BUILD_PLAN.md` Phase 0's
remaining steps (wildcard DNS, wildcard SSL) on the now-primary VPS.
