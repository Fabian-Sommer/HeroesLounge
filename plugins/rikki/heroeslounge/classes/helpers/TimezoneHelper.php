<?php namespace Rikki\Heroeslounge\Classes\Helpers;

use Auth;
use Config;
use Session;
use Redirect;
use Log;
use DateTime;
use DateTimeZone;

class TimezoneHelper
{
    public const DEFAULT_TIMEZONE = 'UTC';
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

    public static function getTimezoneOffset()
    {
        return (new DateTime('now', new DateTimeZone(self::getTimezone())))->format('P');
    }

    public static function getTimeFormatString()
    {
        $tz = self::getTimezone();
        if (substr($tz, 0, 8) === "America/") {
            return 'g:i a';
        } else {
            return 'H:i';
        }
    }

    public static function getDateTimeFormatString()
    {
        return 'd M Y ' . self::getTimeFormatString();
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
