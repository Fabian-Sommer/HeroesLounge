# Upgrading from October CMS 1.0 to Winter CMS 1.2

These files upgrade an existing October CMS 1.0 (build 471) installation and database to Winter CMS 1.2:

- `winter-1.2-pre-upgrade.sql`: database preparation, run once before the first `php artisan winter:up`
- `local-plugins.patch`: Winter 1.2 / PHP 8 fixes for third-party plugins that are not part of this repository

## Requirements

- PHP 8.1 or newer (tested with 8.2) with the extensions gd, intl, zip, pdo_mysql, mbstring, bcmath, exif and curl
- Composer 2
- MySQL 5.7+ or MariaDB 10.2+
- `plugins/rikki/heroeslounge/updates/version.yaml` must list the Rikki.Heroeslounge version that is installed in
  the database (`SELECT version FROM system_plugin_versions WHERE code = 'Rikki.Heroeslounge'`). If that version is
  missing from the file, Winter re-runs the plugin's whole migration history and fails.

## Third-party plugins

These plugins have no Winter release and are not part of the repository. Copy them from the current server
(`plugins/<author>/<name>`), then apply the fixes:

| Plugin | Patched |
| --- | --- |
| Indikator.Content | `models/slideshowstyle/fields.yaml` (YAML syntax) |
| KurtJensen.Passage | `updates/version.yaml` (YAML syntax) |
| Martin.SSButtons | `models/Settings.php` (model constructor signature) |
| Mohsin.Rest | `console/CreateRestController.php` (return type) |
| OFFLINE.SiteSearch | unchanged |
| Xeor.ContentType | unchanged |

Run this from the project root (it also works outside of a git checkout):

    git apply --check upgrade/local-plugins.patch
    git apply upgrade/local-plugins.patch

If `git apply --check` fails, check whether the plugins are already patched: `git apply --reverse --check upgrade/local-plugins.patch`
succeeds in that case.

Do not copy the old `plugins/rainlab/` folder: the RainLab plugins are replaced by their Winter versions, which Composer installs.
The following plugins are no longer used and don't need to be copied either: October.Demo, Bedard.AnalyticsExtension,
JD.GhostUser, Rebel59.Countdown, ToughDeveloper.ImageResizer, Indikator.Backend, Indikator.DevTools, Indikator.Plugins,
JanVince.SmallExtensions, Frozendo.PagePreview, Esroyo.UserProfile, VojtaSvoboda.UserAccessLog, OFFLINE.Speedy, OFFLINE.CORS.
Their database tables are left untouched.

## Upgrading production

1. Back up the database and `storage/app/uploads` and `storage/app/media`.
2. Note these values from the old installation's `config/` files. They go into the new `.env` (see `.env.example`):
   - `app.key` → `APP_KEY` (must stay the same, it encrypts sessions and stored data)
   - `app.timezone` → `APP_TIMEZONE` (dates are stored in this timezone)
   - the database credentials → `DB_*`
   - the mail settings → `MAIL_*`
3. Deploy the repository into a new directory and run `composer install --no-dev`.
4. Copy over `storage/app/uploads`, `storage/app/media`, the `AuthCode.php` secrets in
   `plugins/rikki/heroeslounge/classes/*/` and the third-party plugins listed above, then apply `local-plugins.patch`.
   Do not copy the old `modules/` or `vendor/` folders.
5. Prepare and upgrade the database:

       mysql -u <user> -p <database> < upgrade/winter-1.2-pre-upgrade.sql
       php artisan winter:up

   The SQL script hands the migration history of the RainLab plugins over to the Winter plugins that replace them.
   Winter only does this automatically when the installed RainLab version matches the Winter plugin's "replaces"
   constraint, and production's RainLab.User (1.5.4) and RainLab.Location (1.1.5) are older than that.
6. Make sure the web server allows `storage/app/resized` (used by the `| resize` Twig filter, see `.htaccess`), and that
   the scheduler runs every minute: `* * * * * php /path/to/artisan schedule:run`.
7. Check the site, the backend and `Settings → Updates & Plugins`.

## Test data dumps

The test data dumps have an empty `migrations` table, although the October core schema is fully applied (production has
the table filled). On such a database `winter:up` tries to create the existing core tables again and fails. Use a dump
that includes the contents of the `migrations` table.
