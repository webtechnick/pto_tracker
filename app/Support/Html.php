<?php

namespace App\Support;

use Illuminate\Support\Collection;
use Spatie\Html\Html as SpatieHtml;

/**
 * spatie/laravel-html with values escaped so they round-trip exactly.
 *
 * Spatie writes textarea contents as raw HTML (stored "</textarea><script>"
 * breaks out of the field) and encodes attribute values and option labels
 * without double-encoding (a stored "&amp;" is shown, and re-saved, as "&").
 * Pre-escaping with e() fixes both: spatie's later non-double-encoding pass
 * leaves the entities alone.
 */
class Html extends SpatieHtml
{
    public function input($type = null, $name = null, $value = null)
    {
        $input = parent::input($type, $name, $value);
        $current = $input->getAttribute('value');

        return $current === null ? $input : $input->attribute('value', e($current));
    }

    public function textarea($name = null, $value = null)
    {
        return parent::textarea($name, $value)->html(e($this->old($name, $value)));
    }

    public function select($name = null, $options = [], $value = null)
    {
        return parent::select($name, $this->escapeOptionLabels($options), $value);
    }

    protected function escapeOptionLabels($options)
    {
        return Collection::make($options)->map(function ($label) {
            return is_iterable($label) ? $this->escapeOptionLabels($label) : e($label);
        })->all();
    }
}
