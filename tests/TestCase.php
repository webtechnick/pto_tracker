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
     * Sign in an admin user.
     * @return [type] [description]
     */
    public function signInAdmin()
    {
        return $this->signIn($this->create('App\User', ['role' => 'admin']));
    }

    /**
     * Sign in a planner user.
     * @return [type] [description]
     */
    public function signInPlanner()
    {
        return $this->signIn($this->create('App\User', ['role' => 'planner']));
    }

    /**
     * Sign in a manager user.
     * @return [type] [description]
     */
    public function signInManager()
    {
        return $this->signIn($this->create('App\User', ['role' => 'manager']));
    }
}
