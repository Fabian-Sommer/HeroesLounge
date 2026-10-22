-- Run ONCE against the October CMS 1.0 (build 471) database, BEFORE the first `php artisan winter:up`.
--
-- Winter's first-party plugins only take over a RainLab plugin's migration history automatically if the
-- installed RainLab version matches their "replaces" constraint. Production has RainLab.User 1.5.4 and
-- RainLab.Location 1.1.5, which are older than Winter.User (~1.6) and Winter.Location (^1.1.6) accept, so
-- the takeover would be skipped and Winter would try to create the existing tables from scratch.
--
-- The Winter plugins ship the complete RainLab version history, so renaming the plugin code while keeping
-- the installed version lets `winter:up` apply exactly the migrations that are still missing (including the
-- RainLab 1.x ones and the 2.0.0 table renames rainlab_* -> winter_*).

UPDATE system_plugin_versions SET code = REPLACE(code, 'RainLab.', 'Winter.')
WHERE code IN ('RainLab.User', 'RainLab.UserPlus', 'RainLab.Location', 'RainLab.Notify', 'RainLab.Pages',
               'RainLab.Translate', 'RainLab.Builder', 'RainLab.GoogleAnalytics');

UPDATE system_plugin_history SET code = REPLACE(code, 'RainLab.', 'Winter.')
WHERE code IN ('RainLab.User', 'RainLab.UserPlus', 'RainLab.Location', 'RainLab.Notify', 'RainLab.Pages',
               'RainLab.Translate', 'RainLab.Builder', 'RainLab.GoogleAnalytics');
