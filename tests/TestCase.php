<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
    /**
     * The base URL to use while testing the application.
     *
     * @var string
     */
    protected $baseUrl = 'http://localhost';

    /**
     * Make a factory model
     * @param  [type]  $model    [description]
     * @param  array   $defaults [description]
     * @param  integer $count    [description]
     * @return [type]            [description]
     */
    public function make($model, $defaults = [], $count = 1)
    {
        if ($count > 1) {
            return factory($model, $count)->make($defaults);
        }
        return factory($model)->make($defaults);
    }

    /**
     * Create a factory model
     * @param  [type]  $model    [description]
     * @param  array   $defaults [description]
     * @param  integer $count    [description]
     * @return [type]            [description]
     */
    public function create($model, $defaults = [], $count = 1)
    {
        if ($count > 1) {
            return factory($model, $count)->create($defaults);
        }
        return factory($model)->create($defaults);
    }

    /**
     * Sign in a user.
     * @param  boolean $is_admin [description]
     * @return [type]            [description]
     */
    public function signIn($user = null)
    {
        $user = $user ?: $this->create('App\User');
        $this->actingAs($user);
        return $user;
    }

    /**
     * Sign in an employee user
     * @param  [type] $employee [description]
     * @return [type]           [description]
     */
    public function signInEmployee($employee = null, $data = [])
    {
        $employee = $employee ?: $this->create('App\Employee');
        $user = $this->create('App\User', array_merge($data, ['employee_id' => $employee->id, 'role' => 'user']));

        return $this->signIn($user);
    }

    /**
     * Sign in an admin user.
     * @return [type] [description]
     */
    public function signInAdmin($data = [])
    {
        return $this->signIn($this->create('App\User', array_merge($data, ['role' => 'admin'])));
    }

    /**
     * Sign in a planner user.
     * @return [type] [description]
     */
    public function signInPlanner($data = [])
    {
        return $this->signIn($this->create('App\User', array_merge($data, ['role' => 'planner'])));
    }

    /**
     * Sign in a manager user.
     * @return [type] [description]
     */
    public function signInManager($data = [])
    {
        return $this->signIn($this->create('App\User', array_merge($data, ['role' => 'manager'])));
    }
}
