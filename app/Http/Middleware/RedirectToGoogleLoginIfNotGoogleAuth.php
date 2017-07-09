<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Session;
use Laravel\Socialite\Facades\Socialite;

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
            try {
                $user = Socialite::driver('google')->userFromToken(Session::get('GoogleToken'));
                view()->share(compact('user'));
            } catch (Exception $e) {
                return redirect('/login/google'); // re-signin
            }

            return $next($request);
        }
        return redirect('/login/google');
    }
}
