<?php

namespace App\Http;

use App\Http\ModelSearch;
use App\PaidTimeOff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
/**
 * PaidTimeOff Search Query
 * Takes in a request and based on input from request, run corresponding query modifiers.
 */
class PaidTimeOffSearch extends ModelSearch
{
    protected $params = [
        'team',
        'year',
        'q'
    ];

    /**
     * Start the query
     * @return [type] [description]
     */
    public function startQuery()
    {
        $this->query = PaidTimeOff::with(['employee' => function($query) {
            $query->select(['id', 'name', 'color', 'bgcolor']);
        }]);

        // Force a year scope
        // $year = $this->request->input('year') ?: date('Y');
        // $this->query->whereYear('end_time', $year);
    }

    /**
     * Default empty query
     * @return [type] [description]
     */
    public function _emptyRequest()
    {

    }

    /**
     * Get year search
     * @param  [type] $input [description]
     * @return [type]        [description]
     */
    public function _year($input)
    {
        $this->query->whereYear('end_time', $input);
    }

    /**
     * Default Q query (tags and keyword) modifier
     * @param  [type] $input [description]
     * @return [type]        [description]
     */
    public function _team($input)
    {
        $this->query->whereHas('employee', function($q) use ($input) {
            $q->byInputTags($input);
        });
    }

    public function _q($input)
    {
        $this->query->filter($input);
    }
}
