<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use WebTechNick\LaravelGlow\Traits\Filterable;
use WebTechNick\LaravelGlow\Traits\ToggleActivatable;

class Tag extends Model
{
    use Filterable,
        ToggleActivatable;

    public $fillable = [
        'name',
        'slug',
        'description',
    ];

    public function getFilters()
    {
        return [
            'name', 'slug'
        ];
    }

    /**
     * Get the routeKey for tag
     * @return [type] [description]
     */
    public function getRouteKeyName()
    {
        return 'slug';
    }

    /**
     * Link to the tag view
     *
     * @return [type] [description]
     */
    public function link()
    {
        return '/?team=' . $this->slug;
        // return route('home', ['query' => ['team' => $this->slug]]);
        // return route('home', ['team' => $this->slug]);
    }

    /**
     * Can we slug the name?
     *
     * @param  [type] $name [description]
     * @return [type]       [description]
     */
    static public function canSlug($name)
    {
        return true;
    }

    /**
     * Find popular by type or in general.
     * @param  [type] $query [description]
     * @param  [type] $type  types of tagables.
     * @return [type]        [description]
     */
    public function scopePopular($query, $type = null)
    {
        if ($type) {
            // Specific type events, users, etc..
            return $query->withCount($type)
                         ->groupBy('id')
                         ->orderBy($type . '_count', 'desc');
        }
        // Default will return popular tags across entire site.
        return $query->select(DB::raw('count(taggables.tag_id) as tag_count, tags.*'))
                     ->join('taggables', 'taggables.tag_id', '=', 'tags.id')
                     ->groupBy('tags.id')
                     ->orderBy('tag_count', 'desc');
    }

    /**
     * Find or create a new self by name
     * @param  [type] $name [description]
     * @return [type]       [description]
     */
    public static function findOrCreateByName($name)
    {
        $retval = self::byName($name)->first();
        if (!$retval) {
            $retval = self::create(['name' => $name, 'slug' => str_slug($name)]);
        }
        return $retval;
    }

    /**
     * Find a self by name or slug
     * @param  [type] $query [description]
     * @param  [type] $name  [description]
     * @return [type]        [description]
     */
    public function scopeByName($query, $name)
    {
        $query->where('name', $name);
        if (Schema::hasColumn($this->getTable(), 'slug')) {
            $query->orWhere('slug', $name);
        }

        return $query;
    }

    /**
     * Get the name by slug
     *
     * @param  [type] $slug [description]
     * @return [type]       [description]
     */
    public static function nameBySlug($slug)
    {
        $tag = self::where('slug', $slug)->pluck('name');

        if (!empty($tag)) {
            return $tag->first();
        }

        return '';
    }
}
