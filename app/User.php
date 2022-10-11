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
     * Planner: Can see PTO days remaining.
     * User: No permissions. (default)
     *
     * @var [type]
     */
    public static $roles = [
        'admin' => 'Admin',
        'planner' => 'Planner', // Can see PTO remaining
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
     * Check if user is an admin.
     *
     * @return boolean [description]
     */
    public function isAdmin()
    {
        return $this->role == 'admin';
    }

    /**
     * Check if user is a planner
     *
     * @return boolean [description]
     */
    public function isPlanner()
    {
        return $this->role == 'planner';
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
     * Scope to get admins
     *
     * @return [type] [description]
     */
    public function scopePlanners($query)
    {
        return $query->where('role','planner');
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
