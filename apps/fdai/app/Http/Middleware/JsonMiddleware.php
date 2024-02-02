<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Http\Middleware;
use Closure;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
class JsonMiddleware {
	/**
	 * The Response Factory our app uses
	 * @var ResponseFactory
	 */
	protected ResponseFactory $factory;
	/**
	 * JsonMiddleware constructor.
	 * @param ResponseFactory $factory
	 */
	public function __construct(ResponseFactory $factory){
		$this->factory = $factory;
	}
	/**
	 * Handle an incoming request.
	 * @param Request $request
	 * @param Closure $next
	 * @return mixed
	 */
	public function handle($request, Closure $next){
		// First, set the header so any other middleware knows we're
		// dealing with a should-be JSON response.
		$request->headers->set('Accept', 'application/json');
		// Get the response
		$response = $next($request);
		// If the response is not strictly a JsonResponse, we make it
		if(!$response instanceof JsonResponse){
			$response = $this->factory->json($response->content(), $response->status(), $response->headers->all());
		}
		return $response;
	}
}
