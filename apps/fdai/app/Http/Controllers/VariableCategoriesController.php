<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Http\Controllers;
use App\Models\VariableCategory;
use App\Properties\VariableCategory\VariableCategoryIdProperty;
class VariableCategoriesController extends Controller {
	public function index(): string{
		if($id = VariableCategoryIdProperty::fromRequest()){
			return $this->show($id);
		}
		//VariableCategory::saveIndexJSNecessary();
		return VariableCategory::getIndexPageHtml();
	}
	/**
	 * @param $query
	 * @return string
	 */
	public function show($query): string{
		return VariableCategory::generateShowPage($query ?? 'ketamine');
	}
}
