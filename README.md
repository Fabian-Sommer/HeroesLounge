# HeroesLounge

Core plugins and theme for https://heroeslounge.gg, running on [Winter CMS](https://wintercms.com) 1.2
(Laravel 9, PHP 8.1+, tested on PHP 8.2). Winter CMS is the community fork of October CMS.

To understand the basic project structure, have a look at the Winter CMS documentation: https://wintercms.com/docs/v1.2/docs/cms/themes

This repository contains one theme and three plugins:

- LoungeViews: Contains (frontend) components that do not process user input
- LoungeStatistics: Contains (frontend) components that render statistics
- Heroeslounge: Contains most features: migrations, models, (backend) controllers, helper classes, (frontend) components that process user input, API endpoints.

Winter CMS itself and its first-party plugins are installed with Composer (see `composer.json`).

## Plugins

| Plugin | Source |
| --- | --- |
| Winter.User, UserPlus, Pages, Translate, Location, Notify, Builder, GoogleAnalytics | Composer (`winter/wn-*-plugin`), replace the former RainLab plugins |
| Rikki.Heroeslounge, LoungeStatistics, LoungeViews | this repository |
| Indikator.Content, Mohsin.Rest, OFFLINE.SiteSearch, KurtJensen.Passage, Xeor.ContentType, Martin.SSButtons | **not in the repository**, no Winter release exists: copy them from the current server and apply `upgrade/local-plugins.patch`, see [upgrade/README.md](upgrade/README.md) |

The production server additionally needs the secret classes `plugins/rikki/heroeslounge/classes/{discord,mmr,mailchimp}/AuthCode.php`, which are never committed.

## Local development

Requirements: PHP 8.1+ (tested with 8.2; extensions gd, intl, zip, pdo_mysql, mbstring, bcmath, exif, curl),
Composer 2 and MySQL 8 (or MariaDB 10.2+).

1. Install the dependencies (this also creates the `storage/` directories):

       composer install

2. Copy the third-party plugins listed above into `plugins/` and patch them, see [upgrade/README.md](upgrade/README.md#third-party-plugins).
3. Copy `.env.example` to `.env`, fill in the database settings and run `php artisan key:generate`.
4. Import the test data dump you received (Discord pins / team) and upgrade it, the dumps are still on the October CMS schema
   (the dump must include the `migrations` table, see [upgrade/README.md](upgrade/README.md#test-data-dumps)):

       mysql -u root hl_main < hl_test_data.sql
       mysql -u root hl_main < upgrade/winter-1.2-pre-upgrade.sql
       php artisan winter:up

5. Start the site with `php artisan serve` (frontend at http://localhost:8000, backend at http://localhost:8000/backend).

Set a password for a backend user with `php artisan winter:passwd <login> <password>`.
Frontend users in the test dumps have anonymised emails; you can set a password for one directly in the `users` table.

You can't register a new user locally because the Discord integration will not work locally to verify registered users as having joined the Discord server.

Verify your own user information like this:

    SELECT * FROM users AS u LEFT JOIN rikki_heroeslounge_sloths AS s ON s.user_id=u.id WHERE u.username='YOUR_USERNAME';

## Upgrading production from October CMS 1.0 (build 471)

See [upgrade/README.md](upgrade/README.md) for the upgrade steps, the plugin patches and the list of plugins that are no longer used.
