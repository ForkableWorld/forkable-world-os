<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Http\Middleware;
use App\Slim\View\Request\QMRequest;
use App\Utils\AppMode;
use App\Utils\EnvOverride;
use Closure;
use Illuminate\Http\Request;
use Tests\TestGenerators\ApiTestFile;
class PHPUnitTestMiddleware {
	/**
	 * @param Request $request
	 * @param Closure $next
	 * @return mixed
	 */
	public function handle(Request $request, Closure $next){
		if(EnvOverride::isLocal() && !AppMode::isTestingOrStaging()){
			$createTest = QMRequest::getParam([
				QMRequest::PARAM_GENERATE_PHPUNIT,
				'phpunit',
			]);
			if($createTest){
				$url = ApiTestFile::saveLocallyAndNotify();
				return redirect($url);
			}
		}
		return $next($request);
	}
}
