<?php

namespace App\Http\Middleware;

use Closure;
class NiceArtisanAuth
{
	/**
	 * Handle an incoming request.
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure  $next
	 * @return mixed
	 */
	public function handle(\Illuminate\Http\Request $request, Closure $next): mixed{
		$user = $request->user();

		if ($user && $user->isAdmin()) {
			return $next($request);
		}

		return redirect('/');
	}
}
