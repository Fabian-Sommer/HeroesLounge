<?php
namespace Rikki\Heroeslounge\Classes\Helpers;

class URLHelper
{
    public static function makeTwitchURL($userstring)
    {
        if ($userstring == "") {
            return "";
        } elseif (URLHelper::startsWith($userstring, "http")) {
            return $userstring;
        } elseif (URLHelper::startsWith($userstring, "www.twitch.tv")) {
            return "https://" . $userstring;
        } elseif (URLHelper::startsWith($userstring, "twitch.tv")) {
            return "https://www." . $userstring;
        } elseif (URLHelper::startsWith($userstring, "/")) {
            return "https://www.twitch.tv" . $userstring;
        }
        return "https://www.twitch.tv/" . $userstring;
    }

    public static function makeTwitterURL($userstring)
    {
        if ($userstring == "") {
            return "";
        } elseif (URLHelper::startsWith($userstring, "http")) {
            return $userstring;
        } elseif (URLHelper::startsWith($userstring, "www.twitter.com")) {
            return "https://" . $userstring;
        } elseif (URLHelper::startsWith($userstring, "twitter.com")) {
            return "https://" . $userstring;
        } elseif (URLHelper::startsWith($userstring, "/")) {
            return "https://twitter.com" . $userstring;
        }
        return "https://twitter.com/" . $userstring;
    }

    public static function makeFacebookURL($userstring)
    {
        if ($userstring == "") {
            return "";
        } elseif (URLHelper::startsWith($userstring, "http")) {
            return $userstring;
        } elseif (URLHelper::startsWith($userstring, "www.facebook.com")) {
            return "https://" . $userstring;
        } elseif (URLHelper::startsWith($userstring, "facebook.com")) {
            return "https://www." . $userstring;
        } elseif (URLHelper::startsWith($userstring, "/")) {
            return "https://www.facebook.com" . $userstring;
        }
        return "https://www.facebook.com/" . $userstring;
    }

    public static function makeYoutubeURL($userstring)
    {
        if ($userstring == "") {
            return "";
        } elseif (URLHelper::startsWith($userstring, "http")) {
            return $userstring;
        } elseif (URLHelper::startsWith($userstring, "www.youtube.com")) {
            return "https://" . $userstring;
        } elseif (URLHelper::startsWith($userstring, "youtube.com")) {
            return "https://www." . $userstring;
        } elseif (URLHelper::startsWith($userstring, "/channel/")) {
            return "https://www.youtube.com" . $userstring;
        } elseif (URLHelper::startsWith($userstring, "/user/")) {
            return "https://www.youtube.com" . $userstring;
        } elseif (URLHelper::startsWith($userstring, "/c/")) {
            return "https://www.youtube.com" . $userstring;
        } elseif (URLHelper::startsWith($userstring, "/")) {
            return "https://www.youtube.com/channel" . $userstring;
        }
        return "https://www.youtube.com/channel/" . $userstring;
    }

    public static function makeWebsiteURL($userstring)
    {
        if ($userstring == "") {
            return "";
        } elseif (URLHelper::startsWith($userstring, "http")) {
            return $userstring;
        } elseif (URLHelper::startsWith($userstring, "www")) {
            return "http://" . $userstring;
        }
        return $userstring;
    }

    public static function startsWith($haystack, $needle)
    {
        $length = strlen($needle);
        return (substr($haystack, 0, $length) === $needle);
    }
}
