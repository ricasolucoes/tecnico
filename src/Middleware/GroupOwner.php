<?php

namespace Tecnico\Middleware;

use Closure;

class GroupOwner
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (! auth()->user()->isOwnerOfGroup(auth()->user()->currentGroup)) {
            return back();
        }

        return $next($request);
    }
}
