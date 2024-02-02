<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Http\Middleware;
use App\Computers\ThisComputer;
use App\Logging\QMLog;
use Closure;
use Illuminate\Http\Request;
class SetMemoryLimit {
	/**
	 * Handle an incoming request.
	 * @param Request $request
	 * @param \Closure $next
	 * @return mixed
	 */
	public function handle(Request $request, Closure $next){
		ThisComputer::setAPIMemoryLimit();
		$fromServer = ThisComputer::getMemoryLimit();
		$fromServer = (int)str_replace("M", "", $fromServer);
		$expected = ThisComputer::API_MEMORY_LIMIT_MB;
		if($t = \App\Utils\AppMode::getCurrentTest()){
			$expected = $t->getApiMemoryLimit();
		}
		if($expected !== $fromServer){
			QMLog::logicExceptionIfNotProductionApiRequest("Memory limit should be $expected but is $fromServer");
		}
		return $next($request);
	}
}
