<?php

namespace App\Traits;

use DateTimeInterface;

/**
 * Serialize dates in arrays/JSON as "Y-m-d H:i:s" (the pre-Laravel 7 format).
 *
 * Laravel 7+ defaults to ISO-8601 UTC strings ("2026-01-05T00:00:00.000000Z").
 * The Vue calendar hands these values to moment(), which would convert them
 * to the browser's timezone and shift PTO onto the previous day in US
 * timezones. The JSON API consumed by dora-metrics also expects this format.
 */
trait SerializesDatesAsDateTimeStrings
{
    protected function serializeDate(DateTimeInterface $date): string
    {
        return $date->format('Y-m-d H:i:s');
    }
}
