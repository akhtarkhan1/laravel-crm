<?php

namespace Webkul\Admin\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

class Bouncer
{
    /**
    * Handle an incoming request.
    *
    * @param  \Illuminate\Http\Request  $request
    * @param  \Closure  $next
    * @param  string|null  $guard
    * @return mixed
    */
    public function handle($request, Closure $next, $guard = 'user')
    {
        if (! Auth::guard($guard)->check()) {
            return redirect()->route('admin.session.create');
        }

        if (! (bool) auth()->guard($guard)->user()->status) {
            auth()->guard($guard)->logout();

            return redirect()->route('admin.session.create');
        }

        $this->checkIfAuthorized($request);

        return $next($request);
    }

    /**
    * Handle an incoming request.
    *
    * @param  \Illuminate\Http\Request  $request
    * @return mixed
    */
    public function checkIfAuthorized($request)
    {
        if (! $role = auth()->guard('user')->user()->role) {
            abort(401, 'This action is unauthorized.');
        }

        if ($role->permission_type == 'all') {
            return;
        } else {
            $acl = app('acl');

            if ($acl && isset($acl->roles[Route::currentRouteName()])) {
                bouncer()->allow($acl->roles[Route::currentRouteName()]);
            }
        }
    }
}
