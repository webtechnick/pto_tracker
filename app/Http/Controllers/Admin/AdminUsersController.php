<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\NewUserRequest;
use App\Http\Requests\UserRequest;
use App\Traits\Flashes;
use App\User;
use Illuminate\Http\Request;

class AdminUsersController extends Controller
{
    use Flashes;

    /**
     * Show list of users
     *
     * @return view
     */
    public function index()
    {
        $users = User::orderBy('name', 'ASC')->get();

        return view('users.index', compact('users'));
    }

    /**
     * Show form to create an user
     *
     * @return view
     */
    public function create()
    {
        return view('users.create');
    }

    /**
     * Show a form to edit an user
     *
     * @param  user $user [description]
     * @return view
     */
    public function edit(User $user)
    {
        return view('users.edit', compact('user'));
    }

    /**
     * Delete an user and all their PTO
     *
     * @param  user $user [description]
     * @return redirect
     */
    public function delete(User $user)
    {
        $user->delete();

        $this->goodFlash('user removed.');

        return redirect()->route('admin.users');
    }

    /**
     * Store a new user
     *
     * @param  userRequest $request [description]
     * @return redirect
     */
    public function store(NewUserRequest $request)
    {
        $user = User::createFromRequest($request->all());

        $this->goodFlash($user->name . ' Created.');

        return redirect()->route('admin.users');
    }

    /**
     * Update an user
     *
     * @param  Request  $request  [description]
     * @param  user $user [description]
     * @return redirect
     */
    public function update(UserRequest $request, User $user)
    {
        $user->updateFromRequest($request->all());

        $this->goodFlash($user->name . ' Updated.');

        return redirect()->route('admin.users');
    }
}
