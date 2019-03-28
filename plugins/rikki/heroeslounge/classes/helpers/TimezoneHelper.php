<?php namespace Rikki\Heroeslounge\Classes\Helpers;

use Auth;
use Config;
use Session;

class TimezoneHelper
{
    private const TIMEZONE_KEY = 'timezone';

    public static $defaultTimezone;

    private static function defaultTimezone()
    {
        if (!self::$defaultTimezone)
        {
            self::$defaultTimezone = Config::get('app.timezone', 'Europe/Berlin');
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

        $user = Auth::getUser();
        if ($user) {
            if ($user->sloth->timezone == '') {
                $user->sloth->timezone = TimezoneHelper::getTimezone();
                $user->sloth->save();
            }
        }
    }

    public static function hasTimezone()
    {
        return Session::has(self::TIMEZONE_KEY);
    }
}
