<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
class SetCacheControl {
	/**
	 * Add cache related HTTP headers.
	 * @param Request $request
	 * @param \Closure $next
	 * @param string|array $options
	 * @return Response
	 * @throws \InvalidArgumentException
	 * @noinspection PhpMissingParamTypeInspection
	 */
	public function handle($request, Closure $next, $options = []): Response{
		$response = $next($request);
		if(is_string($options)){
			$options = $this->parseOptions($options);
		}
		if(isset($options['etag']) && $options['etag'] === true){
			$options['etag'] = md5($response->getContent());
		}
		$response->setCache($options);
		$response->isNotModified($request);
		return $response;
	}
	/**
	 * Parse the given header options.
	 * @param string $options
	 * @return array
	 */
	protected function parseOptions(string $options): array{
		return collect(explode(';', $options))->mapWithKeys(function($option){
			$data = explode('=', $option, 2);
			return [$data[0] => $data[1] ?? true];
		})->all();
	}
}
