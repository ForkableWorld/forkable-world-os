<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Http\Controllers;
use App\Models\UserVariable;
use App\Properties\UserVariable\UserVariableIdProperty;
use App\Variables\QMUserVariable;
class UserVariablesController extends Controller {
	public function index(): string{
		if($id = UserVariableIdProperty::fromRequest()){
			return $this->show($id);
		}
		return QMUserVariable::getIndexPageView();
	}
	/**
	 * @param $query
	 * @return string
	 */
	public function show($query): string{
		return UserVariable::generateShowPage($query);
	}
}
