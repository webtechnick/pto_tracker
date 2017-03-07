<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Session;

class RedirectToGoogleLoginIfNotGoogleAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $domain = null)
    {
        if (Session::has('GoogleToken')) {
            //$user = Socialite::driver('google')->userFromToken(Session::get('GoogleToken'));

            return $next($request);
        }
        return redirect('/login/google');
    }
}
