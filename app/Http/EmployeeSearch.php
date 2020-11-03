<?php

namespace App\Http;

use App\Http\ModelSearch;
use App\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
/**
 * Employee Search Query
 * Takes in a request and based on input from request, run corresponding query modifiers.
 */
class EmployeeSearch extends ModelSearch
{
    protected $params = [
        'team',
        'q',
    ];

    /**
     * Start the query
     * @return [type] [description]
     */
    public function startQuery()
    {
        $this->query = Employee::orderBy('name', 'ASC');
    }

    /**
     * Default empty query
     * @return [type] [description]
     */
    public function _emptyRequest()
    {

    }

    /**
     * Team tag search
     * @param  [type] $input [description]
     * @return [type]        [description]
     */
    public function _team($input)
    {
        $this->query->byInputTags($input);
    }

    public function _q($input)
    {
        $this->query->filter($input);
    }
}
