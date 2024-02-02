<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
class MetaMiddleware {
	/**
	 * @param Request $request
	 * @param \Closure $next
	 * @return mixed
	 */
	public function handle(Request $request, Closure $next): mixed{
		// We should set this stuff later by the object or in controllers
		//MetaTag::set('title', MetaHtml::generateTitle());
		//MetaTag::set('image', MetaHtml::generateImage());
		//MetaTag::set('description', MetaHtml::generateDescription());
		//MetaTag::set('keywords', MetaHtml::getKeywordString());
		return $next($request);
	}
}
