<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Http\Middleware;
use App\Models\UserVariable;
use App\Models\Variable;
use App\Slim\Middleware\QMAuth;
use App\Utils\UrlHelper;
use Illuminate\Http\Request;
use App\Http\Middleware\Authorize;
use App\Astral;
class AstralAuthorize extends Authorize
{
	/**
	 * Handle an incoming request.
	 * @param \Illuminate\Http\Request $request
	 * @param \Closure $next
	 * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Http\Response|\Illuminate\Routing\Redirector
	 */
	public function handle(Request $request, \Closure $next){
		$authorized = Astral::check($request);
		if(!$authorized){
			if($uv = $this->getUserVariable()){
				return redirect($uv->getAstralUpdateUrl());
			}
		}
		return parent::handle($request, $next);
	}
	/**
	 * @return UserVariable|null
	 */
	protected function getUserVariable(): ?UserVariable{
		$variableId = UrlHelper::getBetween('/variables/', '/edit');
		if(!$variableId){
			return null;
		}
		$v = Variable::findInMemoryOrDB($variableId);
		if($v->getIsPublic()){
			return $v->getOrCreateUserVariable(QMAuth::getUserId());
		} else{
			return UserVariable::findByVariableId($variableId);
		}
	}
}
