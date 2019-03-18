<?php

namespace App\Http\Middleware;

use Closure;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\App;
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
        if (App::environment('local')) {
            return $next($request);
        }
        if (Session::has('GoogleToken')) {
            if (!Session::has('GoogleUser')) {
                try {
                    $user = Socialite::driver('google')->userFromToken(Session::get('GoogleToken'));
                    Session::put('GoogleUser', $user);
                } catch (RequestException $e) {
                    return redirect('/login/google'); // re-signin
                }
            }
            $user = Session::get('GoogleUser');
            view()->share(compact('user'));

            return $next($request);
        }
        return redirect('/login/google');
    }
}
