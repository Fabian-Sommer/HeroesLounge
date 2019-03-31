<?php namespace Rikki\Heroeslounge\Classes\Helpers;

use Auth;
use Config;
use Session;
use Redirect;
use Log;

class TimezoneHelper
{
    private const DEFAULT_TIMEZONE = 'UTC';
    private const TIMEZONE_KEY = 'timezone';

    private static $defaultTimezone;

    public static function defaultTimezone()
    {
        if (!self::$defaultTimezone) {
            self::$defaultTimezone =
                Config::get('app.timezone', self::DEFAULT_TIMEZONE);
        }
        return self::$defaultTimezone;
    }

    public static function getTimezone()
    {
        return Session::get(self::TIMEZONE_KEY, self::defaultTimezone());
    }

    public static function setTimezone()
    {
        if (isset($_POST[self::TIMEZONE_KEY])) {
            $timezoneName = $_POST[self::TIMEZONE_KEY];
        } else {
            $timezoneName = self::defaultTimezone();
        }
        if (!in_array($timezoneName, timezone_identifiers_list())) {
            $timezoneName = self::defaultTimezone();
        }
        Session::put(self::TIMEZONE_KEY, $timezoneName);
        return Redirect::refresh();
    }

    public static function hasTimezone()
    {
        return Session::has(self::TIMEZONE_KEY);
    }
}
