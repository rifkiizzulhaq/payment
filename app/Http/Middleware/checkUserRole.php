<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class checkUserRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, ...$roles)
    {
        // // return $next($request);
        // $user = Auth::user();

        // // Periksa apakah pengguna telah login
        // if ($user) {
        //     // Periksa apakah peran pengguna ada dalam daftar yang diberikan
        //     if (in_array($user->role, $roles)) {
        //         return $next($request);
        //     }
        // }

        // if ($user && $user->role === 'Admin') {
        //     return redirect('/tampilan')->route('tampilan');
        // }

        // abort(403, 'Unauthorized action.');
    }
}
