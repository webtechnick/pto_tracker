<?php

namespace App\Http;

use Illuminate\Http\Request;
/**
 * Base for any HTTP/Ajax Model Search
 */
abstract class ModelSearch
{
    /**
     * Incoming request
     * @var [type]
     */
    protected $request;

    /**
     * The Query to chain modifiers on
     * @var [type]
     */
    protected $query;

    /**
     * Available Query Modifiers Allowed
     * @var [type]
     */
    protected $params = [
        'tags',
        'keyword',
        'q',
        // 'owned',
    ];

    /**
     * Available sort modifiers allowed
     * @var [type]
     */
    protected $sorts = [
        'created',
        'updated',
        // 'rating',
        // 'random',
    ];

    /**
     * Setup the request
     * @param Request $request [description]
     */
    public function __construct(Request $request = null)
    {
        $this->request = $request ?: request();
        $this->startQuery();
        $this->buildQuery();
        $this->setOrder();
    }

    /**
     * Start the query define this in child
     * @return [type] [description]
     */
    abstract function startQuery();

    /**
     * Overwrite the query specific to ajax search results
     * if you want it different than the default start query
     *
     * @return [type] [description]
     */
    public function resultsQuery()
    {
        $this->startQuery();
    }

    /**
     * Get all the stories based on search
     * @return [type] [description]
     */
    public function get($limit = null)
    {
        if ($limit) {
            $this->query->limit($limit);
        }
        return $this->query->get();
    }

    /**
     * Return results object
     *
     * @param  [type] $limit [description]
     * @return [type]        [description]
     */
    public function results($limit = null)
    {
        $this->resultsQuery(); // Setup Query
        $this->buildQuery();

        return $this->get($limit)->map->toResult();
    }

    /**
     * Get the first object
     * @return [type] [description]
     */
    public function first()
    {
        return $this->query->first();
    }

    /**
     * Get all the request data for old
     * @return [type] [description]
     */
    public function old()
    {
        return $this->request->only(array_merge($this->params, ['sort']));
    }

    /**
     * Get an approved field from the old data
     * @param  [type] $field [description]
     * @return [type]        [description]
     */
    public function field($field)
    {
        $old = $this->old();
        if (isset($old[$field])) {
            return $old[$field];
        }

        return '';
    }

    /**
     * Paginate the stories based on search
     * @param  [type] $limit [description]
     * @return [type]        [description]
     */
    public function paginate($limit = null)
    {
        return $this->query->paginate($limit);
    }

    /**
     * Build the query based on input params.
     */
    public function buildQuery()
    {
        $empty_request = true;
        foreach ($this->params as $name) {
            if ($this->request->has($name)) {
                $empty_request = false;
                $method = '_' . $name;
                $this->{$method}($this->request->get($name));
            }
        }

        if ($empty_request) {
            $this->_emptyRequest();
        }
    }

    /**
     * Decide the sort based on incoming request
     */
    public function setOrder()
    {
        $empty_sort = true;
        if ($this->request->has('sort')) {
            $value = $this->request->get('sort');
            if (in_array($value, $this->sorts)) {
                $empty_sort = false;
                $method = '_sort_' . $value;
                $this->{$method}($this->request->get('direction'));
            }
        }

        if ($empty_sort) {
            $this->_emptySort();
        }
    }

    /**
     * Default order for sort
     * @return [type] [description]
     */
    public function _emptySort()
    {
        // Overwritable in child. Do nothing by default
    }

    /**
     * Empty request
     * @return [type] [description]
     */
    public function _emptyRequest()
    {
        // Overwritable in child. Do nothing by default
    }

    /**
     * Sort by created
     * @param  string $way [description]
     * @return [type]      [description]
     */
    public function _sort_created($way = 'desc')
    {
        $this->clearOrderBy();
        $this->query->orderBy('created_at', 'desc');
    }

    /**
     * Sort by Updated
     * @param  string $way [description]
     * @return [type]      [description]
     */
    public function _sort_updated($way = 'desc')
    {
        $this->clearOrderBy();
        $this->query->orderBy('updated_at', 'desc');
    }

    /**
     * Sory by rating
     * @return [type] [description]
     */
    public function _sort_rating()
    {
        $this->clearOrderBy();
        $this->query->sortByRating();
    }

    /**
     * Sory by rating
     * @return [type] [description]
     */
    public function _sort_random()
    {
        $this->clearOrderBy();
        $this->query->inRandomOrder();
    }

    /**
     * Default Tags Query Modifier
     * @param  [type] $input [description]
     * @return [type]        [description]
     */
    public function _tags($input)
    {
        $this->query->byInputTags($input);
    }

    /**
     * Default Keyword Query Modifier
     * @param  [type] $input [description]
     * @return [type]        [description]
     */
    public function _keyword($input)
    {
        $this->query->filter($input);
    }

    /**
     * Default Owned Query Modifier
     * @param  [type] $input [description]
     * @return [type]        [description]
     */
    public function _owned($input)
    {
        if ($this->isEmptyInput($input)) {
            return;
        }
        $this->query->mine();
    }

    /**
     * Default Q query (tags and keyword) modifier
     * @param  [type] $input [description]
     * @return [type]        [description]
     */
    public function _q($input)
    {
        $this->query->where(function ($query) use($input) {
            $query->orWhere(function ($q) use($input) {
                $q->byInputTags($input);
            });
            $query->orWhere(function ($q) use($input) {
                $q->filter($input);
            });
        });
    }

    /**
     * Clear the orderBy
     * @return [type] [description]
     */
    protected function clearOrderBy()
    {
        $this->query->getQuery()->orders = null;
    }

    /**
     * Verify input is truly empty
     * @param  [type]  $input [description]
     * @return boolean        [description]
     */
    protected function isEmptyInput($input)
    {
        if (is_array($input)) {
            return empty($input[0]);
        }
        return empty($input);
    }
}
