<?php

namespace App\Database;

use Illuminate\Database\SQLiteConnection;

/**
 * SQLite connection that returns column values the way pdo_sqlite did on
 * PHP < 8.1: every non-null value as SQLite's text ("15", "4.0", "0.5").
 *
 * PHP 8.1 switched to native ints/floats, which changed the JSON served by
 * /api/time-off and /get/ptos (dora-metrics consumes it) and the text in the
 * views and emails ("3 day(s)" instead of "3.0 day(s)"). PDO's
 * ATTR_STRINGIFY_FETCHES is not equivalent: it renders REAL 4.0 as "4".
 */
class LegacySqliteConnection extends SQLiteConnection
{
    public function select($query, $bindings = [], $useReadPdo = true, array $fetchUsing = [])
    {
        return array_map(
            fn ($row) => $this->stringifyRow($row),
            parent::select($query, $bindings, $useReadPdo, $fetchUsing)
        );
    }

    public function cursor($query, $bindings = [], $useReadPdo = true, array $fetchUsing = [])
    {
        foreach (parent::cursor($query, $bindings, $useReadPdo, $fetchUsing) as $row) {
            yield $this->stringifyRow($row);
        }
    }

    protected function stringifyRow($row)
    {
        if (is_object($row)) {
            foreach (get_object_vars($row) as $column => $value) {
                $row->{$column} = static::sqliteText($value);
            }

            return $row;
        }

        if (is_array($row)) {
            return array_map(fn ($value) => static::sqliteText($value), $row);
        }

        return static::sqliteText($row);
    }

    /**
     * sqlite3_column_text() formatting: integers as digits, REALs with
     * "%!.15g" (15 significant digits, always with a decimal point).
     */
    public static function sqliteText($value)
    {
        if (is_int($value)) {
            return (string) $value;
        }

        if (is_float($value)) {
            if (is_nan($value) || is_infinite($value)) {
                return is_nan($value) ? null : ($value > 0 ? 'Inf' : '-Inf');
            }

            $text = sprintf('%.15G', $value);
            if (str_contains($text, 'E')) {
                [$mantissa, $exponent] = explode('E', $text);
                $mantissa = str_contains($mantissa, '.') ? $mantissa : $mantissa.'.0';

                return $mantissa.'e'.($exponent[0] === '-' ? '-' : '+').str_pad(ltrim($exponent, '+-'), 2, '0', STR_PAD_LEFT);
            }

            return str_contains($text, '.') ? $text : $text.'.0';
        }

        return $value;
    }
}
