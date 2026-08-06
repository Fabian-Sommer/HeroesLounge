<?php namespace Rikki\Heroeslounge\Classes\Helpers;

use Auth;
use Config;
use Session;
use Redirect;
use Log;
use DateTime;
use DateTimeZone;
use Exception;

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
        $timezoneName = isset($_POST[self::TIMEZONE_KEY]) ? $_POST[self::TIMEZONE_KEY] : null;

        if (!is_string($timezoneName) || !self::isValidTimezone($timezoneName)) {
            // Leave the session alone so hasTimezone() stays false and detection
            // is retried on the next page load, instead of pinning the visitor
            // to the default timezone for the rest of the session.
            return;
        }

        Session::put(self::TIMEZONE_KEY, $timezoneName);
        return Redirect::refresh();
    }

    private static function isValidTimezone($timezoneName)
    {
        // timezone_identifiers_list() only returns the canonical identifiers, so it
        // rejects the backwards compatible aliases browsers still report, such as
        // Europe/Kiev, Asia/Calcutta and Etc/GMT+5. All of them are valid here.
        try {
            new DateTimeZone($timezoneName);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public static function hasTimezone()
    {
        return Session::has(self::TIMEZONE_KEY);
    }
}
