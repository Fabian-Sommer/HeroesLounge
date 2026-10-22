<?php namespace Rikki\Heroeslounge\Classes\Traits;

use DateTimeInterface;

/**
 * Since Laravel 7, dates in toArray()/toJson() are serialized as ISO-8601 UTC strings
 * (e.g. "2020-10-15T15:46:05.000000Z"). Keep the "Y-m-d H:i:s" format the API has always
 * returned so existing API consumers keep working.
 */
trait LegacyDateSerialization
{
    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
