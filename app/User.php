<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * User Roles
     *   A User's role defines it's permissions on the site.
     *
     * Admin: All permissions.
     * User: No permissions. (default)
     *
     * @var [type]
     */
    public static $roles = [
        'admin' => 'Admin',
        'user' => 'User', // Default
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'role'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * Check if user is logged in.
     * @return boolean [description]
     */
    public function isAdmin()
    {
        return $this->role == 'admin';
    }

    /**
     * Retrieve a list of user accounts
     * @return [type] [description]
     */
    public static function getAdmins()
    {
        return self::admins()->get();
    }

    /**
     * Scope to get admins
     *
     * @return [type] [description]
     */
    public function scopeAdmins($query)
    {
        return $query->where('role','admin');
    }

    /**
     * Create a User from the incomming admin request
     *
     * @return [type] [description]
     */
    public static function createFromRequest($data)
    {
        return self::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'role' => $data['role'],
            'password' => bcrypt($data['password']),
        ]);
    }

    /**
     * Update a User from an incomming request
     *
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    public function updateFromRequest($data)
    {
        $this->update($data);

        return $this;
    }
}
