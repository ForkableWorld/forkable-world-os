<?php namespace App\Http\Middleware;
use App\AppSettings\HostAppSettings;
use App\Models\OAClient;
use App\Properties\Base\BaseClientSecretProperty;
use Closure;
use Illuminate\Http\Request;
class HostAppSettingsMiddleware {
	/**
	 * Handle an incoming request.
	 * @param Request $request
	 * @param Closure $next
	 * @return mixed
	 */
	public function handle(Request $request, Closure $next): mixed{
		$client = OAClient::fromRequest();
		if($client){
			HostAppSettings::setClient($client);
			$clientSecret = BaseClientSecretProperty::fromRequest();
			if($clientSecret){
				if($clientSecret === $client->client_secret){
					$request->attributes->set('client', $client);
				}
			}
		}
		return $next($request);
	}
}
